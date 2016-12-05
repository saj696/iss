<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;
use Cake\View\View;

/**
 * TransferItems Controller
 *
 * @property \App\Model\Table\TransferItemsTable $TransferItems
 */
class ReceiveItemsController extends AppController
{
    public $paginate = [
        'limit' => 15,
        'order' => [
            'TransferItems.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = $this->Auth->user();
        $this->loadModel('TransferResources');
        $this->loadModel('TransferEvents');
        $this->loadModel('Warehouses');
        $this->loadModel('Items');
        $this->loadModel('Depots');
        $this->loadModel('Users');

        $events = $this->TransferEvents->find('all', [
            'conditions' => ['TransferEvents.status !=' => 99, 'TransferEvents.recipient_id'=>$user['id'], 'recipient_action'=>array_flip(Configure::read('transfer_event_types'))['receive']],
            'contain' => ['TransferResources'=>['TransferItems']]
        ]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $warehouses = $this->Warehouses->find('list', ['conditions'=>['status'=>1]])->toArray();
        $depots = $this->Depots->find('list', ['conditions'=>['status'=>1]])->toArray();
        $users = $this->Users->find('list', ['conditions'=>['user_group_id !='=>1,'status'=>1]]);


//        $userLevelWarehouses = $this->Warehouses->find('all', ['conditions'=>['status'=>1, 'unit_id'=>$user['administrative_unit_id']], 'fields'=>['id']])->hydrate(false)->toArray();
//        $myLevelWarehouses = [];
//        foreach($userLevelWarehouses as $userLevelWarehouse):
//            $myLevelWarehouses[] = $userLevelWarehouse['id'];
//        endforeach;
//
//        $userDepotInfo = $this->Depots->get($user['depot_id']);
//        $depotWarehouses = json_decode($userDepotInfo['warehouses'], true);

        $events = $this->paginate($events);
        $this->set(compact('itemArray', 'users', 'events', 'warehouses'));
        $this->set('_serialize', ['events']);
    }

    public function distribute($id=null)
    {
        $user = $this->Auth->user();
        $this->loadModel('TransferEvents');
        $this->loadModel('Stocks');
        $this->loadModel('Depots');
        $this->loadModel('ItemUnits');

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($user, $id, &$saveStatus)
            {
                $userDepotInfo = $this->Depots->get($user['depot_id']);
                $depotWarehouses = json_decode($userDepotInfo['warehouses'], true);

                if(sizeof($depotWarehouses)>1) {
                    $this->Flash->error('Handling multiple warehouses in a depot not possible right now! Plz contact with SoftBD Ltd.');
                    return $this->redirect(['action' => 'index']);
                } else {
                    // Event action update
                    $transferEvent = TableRegistry::get('transfer_events');
                    $query = $transferEvent->query();
                    $query->update()->set(['is_action_taken' => 1])->where(['id' => $id])->execute();

                    // Stock Update
                    $event = $this->TransferEvents->get($id, ['contain' => ['TransferResources'=>['TransferItems']]]);

                    if(sizeof($event['transfer_resource']['transfer_items'])>0):
                        foreach($event['transfer_resource']['transfer_items'] as $itemInfo):
                            $existingStock = $this->Stocks->find('all', ['conditions'=>['warehouse_id'=>$depotWarehouses[0], 'item_id'=>$itemInfo['item_id'], 'manufacture_unit_id'=>$itemInfo['manufacture_unit_id']]])->first();
                            if($existingStock){
                                $stocks = TableRegistry::get('stocks');
                                $query = $stocks->query();
                                $query->update()->set(['quantity' => $existingStock['quantity']+$itemInfo['quantity']])->where(['id' => $existingStock['id']])->execute();
                            } else {
                                $stock = $this->Stocks->newEntity();
                                $stockData['warehouse_id'] = $depotWarehouses[0];
                                $stockData['item_id'] = $itemInfo['item_id'];
                                $stockData['manufacture_unit_id'] = $itemInfo['manufacture_unit_id'];
                                $stockData['quantity'] = $itemInfo['quantity'];
                                $stockData['approved_quantity'] = 0;
                                $stockData['created_by'] = $user['id'];
                                $stockData['created_date'] = time();
                                $stock = $this->Stocks->patchEntity($stock, $stockData);
                                $this->Stocks->save($stock);
                            }
                        endforeach;
                    endif;

                    $this->Flash->success('Successfully received. Thank you!');
                    return $this->redirect(['action' => 'index']);
                }
            });
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
            $this->Flash->error('Not Received. Please try again!');
        }

        $this->autoRender = false;
    }

    public function receive($id=null)
    {
        $user = $this->Auth->user();
        $this->loadModel('TransferEvents');
        $this->loadModel('Stocks');

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($user, $id, &$saveStatus)
            {
                // Event action update
                $transferEvent = TableRegistry::get('transfer_events');
                $query = $transferEvent->query();
                $query->update()->set(['is_action_taken' => 1])->where(['id' => $id])->execute();

                // Stock Update
                $event = $this->TransferEvents->get($id, ['contain' => ['TransferResources'=>['TransferItems']]]);

                if(sizeof($event['transfer_resource']['transfer_items'])>0):
                    foreach($event['transfer_resource']['transfer_items'] as $itemInfo):
                        $existingStock = $this->Stocks->find('all', ['conditions'=>['warehouse_id'=>$user['warehouse_id'], 'item_id'=>$itemInfo['item_id'], 'manufacture_unit_id'=>$itemInfo['manufacture_unit_id']]])->first();
                        if($existingStock) {
                            $stocks = TableRegistry::get('stocks');
                            $query = $stocks->query();
                            $query->update()->set(['quantity' => $existingStock['quantity']+$itemInfo['quantity']])->where(['id' => $existingStock['id']])->execute();
                        } else {
                            $stock = $this->Stocks->newEntity();
                            $stockData['warehouse_id'] = $user['warehouse_id'];
                            $stockData['item_id'] = $itemInfo['item_id'];
                            $stockData['manufacture_unit_id'] = $itemInfo['manufacture_unit_id'];
                            $stockData['quantity'] = $itemInfo['quantity'];
                            $stockData['approved_quantity'] = 0;
                            $stockData['created_by'] = $user['id'];
                            $stockData['created_date'] = time();
                            $stock = $this->Stocks->patchEntity($stock, $stockData);
                            $this->Stocks->save($stock);
                        }

                    endforeach;
                endif;
            });
            $this->Flash->success('Successfully received. Thank you!');
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
            $this->Flash->error('Not Received. Please try again!');
        }

        return $this->redirect(['action' => 'index']);
    }
}
