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
class DeliverChalansController extends AppController
{
    public $paginate = [
        'limit' => 15,
        'order' => [
            'TransferEvents.id' => 'desc'
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
            'conditions' => ['TransferEvents.status !=' => 99, 'TransferEvents.recipient_id'=>$user['id'], 'recipient_action'=>array_flip(Configure::read('transfer_event_types'))['deliver']],
            'contain' => ['TransferResources'=>['TransferItems']]
        ]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $warehouses = $this->Warehouses->find('list', ['conditions'=>['status'=>1]])->toArray();
        $depots = $this->Depots->find('list', ['conditions'=>['status'=>1]])->toArray();
        $users = $this->Users->find('list', ['conditions'=>['user_group_id !='=>1,'status'=>1]]);
        $events = $this->paginate($events);

        $userLevelWarehouses = $this->Warehouses->find('all', ['conditions'=>['status'=>1, 'unit_id'=>$user['administrative_unit_id']], 'fields'=>['id']])->hydrate(false)->toArray();
        $myLevelWarehouses = [];
        foreach($userLevelWarehouses as $userLevelWarehouse):
            $myLevelWarehouses[] = $userLevelWarehouse['id'];
        endforeach;

        $forwardingUsers = $this->Users->find('list', ['conditions'=>['user_group_id'=>Configure::read('depot_in_charge_ug'), 'status'=>1]])->orWhere(['user_group_id'=>Configure::read('warehouse_in_charge_ug')]);

        $this->set(compact('itemArray', 'users', 'events', 'warehouses', 'depots', 'myLevelWarehouses', 'forwardingUsers'));
        $this->set('_serialize', ['events']);
    }

    public function deliver($id=null)
    {
        $user = $this->Auth->user();
        $time = time();
        $this->loadModel('TransferItems');
        $this->loadModel('TransferEvents');
        $this->loadModel('TransferResources');
        $this->loadModel('Users');

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($id, $user, $time, &$saveStatus)
            {
                if($user['user_group_id']==Configure::read('depot_in_charge_ug')):
                    $trigger_type = array_flip(Configure::read('serial_trigger_types'))['depot'];
                    $trigger_id = $user['depot_id'];
                elseif($user['user_group_id']==Configure::read('warehouse_in_charge_ug')):
                    $trigger_type = array_flip(Configure::read('serial_trigger_types'))['warehouse'];
                    $trigger_id = $user['warehouse_id'];
                else:
                    $trigger_type = array_flip(Configure::read('serial_trigger_types'))['others'];
                    $trigger_id = $user['administrative_unit_id'];
                endif;

                $chalanEvent = $this->TransferEvents->get($id, ['contain' => ['TransferResources'=>['TransferItems']]]);

                $chalanReferences = json_decode($chalanEvent['chalan_references'], true);

                foreach($chalanReferences as $chalanReference)
                {
                    $chalanReferenceEvent = $this->TransferEvents->get($chalanReference, ['contain' => ['TransferResources'=>['TransferItems']]]);
                    if($chalanReferenceEvent)
                    {
                        // Resource entry
                        $resource = $this->TransferResources->newEntity();
                        $resourceData['reference_resource_id'] = $chalanEvent['transfer_resource_id']; // ??
                        $resourceData['resource_type'] = array_flip(Configure::read('transfer_resource_types'))['receive_delivery'];
                        $resourceData['trigger_type'] = $trigger_type;
                        $resourceData['trigger_id'] = $trigger_id;
                        $resourceData['created_by'] = $user['id'];
                        $resourceData['created_date'] = $time;
                        $resource = $this->TransferResources->patchEntity($resource, $resourceData);
                        $resourceResult = $this->TransferResources->save($resource);

                        // Corresponding event entry
                        $eventTbl = $this->TransferEvents->newEntity();
                        $eventData['transfer_resource_id'] = $resourceResult['id'];
                        $eventData['recipient_id'] = $chalanReferenceEvent['initiated_by'];
                        $eventData['recipient_action'] = array_flip(Configure::read('transfer_event_types'))['receive'];
                        $eventData['created_by'] = $user['id'];
                        $eventData['created_date'] = $time;
                        $eventTbl = $this->TransferEvents->patchEntity($eventTbl, $eventData);
                        $this->TransferEvents->save($eventTbl);

                        // Corresponding item entry
                        if(sizeof($chalanReferenceEvent['transfer_resource']['transfer_items'])>0):
                            foreach($chalanReferenceEvent['transfer_resource']['transfer_items'] as $itemInfo):
                                $item = $this->TransferItems->newEntity();
                                $itemUnitInfo = $this->ItemUnits->get($itemInfo['item_unit_id']);

                                $itemData['transfer_resource_id'] = $resourceResult['id'];
                                $itemData['item_id'] = $itemUnitInfo['item_id'];
                                $itemData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                                $itemData['item_unit_id'] = $itemInfo['item_unit_id'];
                                $itemData['quantity'] = $itemInfo['quantity'];
                                $itemData['warehouse_id'] = $chalanReferenceEvent['transfer_resource']['transfer_items'][0]['warehouse_id'];
                                $itemData['created_by'] = $user['id'];
                                $itemData['created_date'] = $time;
                                $item = $this->TransferItems->patchEntity($item, $itemData);
                                $this->TransferItems->save($item);
                            endforeach;
                        endif;
                    }
                    else
                    {
                        $this->Flash->error('Delivery not possible. Please try again!');
                        throw new \Exception('error');
                    }
                }

                // Event update with is_action_taken = 1
                $transfer_events = TableRegistry::get('transfer_events');
                $query = $transfer_events->query();
                $query->update()->set(['is_action_taken' => 1])->where(['id' => $id])->execute();
            });

            $this->Flash->success('You have successfully delivered. Thank you!');
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
            $this->Flash->error('Delivery not possible. Please try again!');
            return $this->redirect(['action' => 'index']);
        }
    }
}
