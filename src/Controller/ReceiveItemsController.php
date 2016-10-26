<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;

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

        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']];
        }

        $warehouses = $this->Warehouses->find('list', ['conditions'=>['status'=>1]])->toArray();
        $depots = $this->Depots->find('list', ['conditions'=>['status'=>1]])->toArray();
        $users = $this->Users->find('list', ['conditions'=>['user_group_id !='=>1,'status'=>1]]);
        $events = $this->paginate($events);

        $userLevelWarehouses = $this->Warehouses->find('all', ['conditions'=>['status'=>1, 'unit_id'=>$user['administrative_unit_id']], 'fields'=>['id']])->hydrate(false)->toArray();
        $myLevelWarehouses = [];
        foreach($userLevelWarehouses as $userLevelWarehouse):
            $myLevelWarehouses[] = $userLevelWarehouse['id'];
        endforeach;

        $this->set(compact('itemArray', 'users', 'events', 'warehouses', 'depots', 'myLevelWarehouses'));
        $this->set('_serialize', ['events']);
    }

    /**
     * View method
     *
     * @param string|null $id Transfer Item id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $this->loadModel('TransferItems');
        $this->loadModel('TransferEvents');
        $this->loadModel('TransferResources');
        $this->loadModel('Users');
        $this->loadModel('Warehouses');
        $this->loadModel('Depots');
        $this->loadModel('Items');
        $this->loadModel('Stocks');

        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']];
        }

        $event = $this->TransferEvents->get($id);
        $resource = $this->TransferResources->get($event['transfer_resource_id'], ['contain'=>['TransferItems']]);
        $items = $resource['transfer_items'];
        $orderNo = $resource['serial_no'];
        $requestDate = date('d-m-Y', $resource['created_date']);


        $requestUserInfo = $this->Users->get($event['created_by']);
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1]])->toArray();
        $allWarehouses = $warehouses;

        if($resource['trigger_type']==array_flip(Configure::read('trigger_types'))['warehouse']):
            $requestFrom =  $warehouses[$resource['trigger_id']];
        else:
            $depots = $this->Depots->find('list', ['conditions' => ['status' => 1]])->toArray();
            $requestFrom =  $depots[$resource['trigger_id']];
        endif;

        foreach($warehouses as $key=>$warehouse):
            if($requestUserInfo['user_group_id']==Configure::read('depot_in_charge_ug')):
                $requestUserDepotInfo = $this->Depots->get($requestUserInfo['depot_id']);
                $requestUserWarehouseIds = json_decode($requestUserDepotInfo['warehouses'], true);
                foreach($requestUserWarehouseIds as $requestUserWarehouseId):
                    if($key==$requestUserWarehouseId):
                        unset($warehouses[$key]);
                    endif;
                endforeach;
            elseif($requestUserInfo['user_group_id']==Configure::read('warehouse_in_charge_ug')):
                if($key==$requestUserInfo['warehouse_id']):
                    unset($warehouses[$key]);
                endif;
            endif;
        endforeach;

        $userLevelWarehouses = $this->Warehouses->find('all', ['conditions'=>['status'=>1, 'unit_id'=>$user['administrative_unit_id']], 'fields'=>['id']])->hydrate(false)->toArray();

        $myLevelWarehouses = [];
        foreach($userLevelWarehouses as $userLevelWarehouse):
            $myLevelWarehouses[] = $userLevelWarehouse['id'];
        endforeach;

        $requestUserWarehouses = [];
        if($requestUserInfo['user_group_id']==Configure::read('warehouse_in_charge_ug')):
            $requestUserWarehouses[] = $requestUserInfo['warehouse_id'];
        elseif($requestUserInfo['user_group_id']==Configure::read('depot_in_charge_ug')):
            $requestUserDepotInfo = $this->Depots->get($requestUserInfo['depot_id']);
            $requestUserWarehouseIds = json_decode($requestUserDepotInfo['warehouses'], true);

            foreach($requestUserWarehouseIds as $requestUserWarehouse):
                $requestUserWarehouses[] = $requestUserWarehouse;
            endforeach;
        endif;

        foreach($items as $item):
            foreach($myLevelWarehouses as $warehouseId):
                $myDetail = [];
                $stock = $this->Stocks->find('all', ['conditions' => ['status' => 1, 'warehouse_id'=>$warehouseId, 'item_id'=>$item['item_id']], 'fields'=>['quantity']])->first();
                $myDetail['warehouse_id'] = $warehouseId;
                $myDetail['item_id'] = $item['item_id'];
                $myDetail['quantity'] = isset($stock['quantity'])?$stock['quantity']:0;
                $myWarehouseDetails[] = $myDetail;
            endforeach;
        endforeach;

        foreach($items as $item):
            foreach($requestUserWarehouses as $warehouseId):
                $requestDetail = [];
                $stock = $this->Stocks->find('all', ['conditions' => ['status' => 1, 'warehouse_id'=>$warehouseId, 'item_id'=>$item['item_id']], 'fields'=>['quantity']])->first();
                $requestDetail['warehouse_id'] = $warehouseId;
                $requestDetail['item_id'] = $item['item_id'];
                $requestDetail['required'] = $item['quantity'];
                $requestDetail['existing'] = isset($stock['quantity'])?$stock['quantity']:0;
                $requestWarehouseDetails[] = $requestDetail;
            endforeach;
        endforeach;

        $this->set(compact('requestWarehouseDetails', 'myWarehouseDetails', 'itemArray', 'warehouses', 'allWarehouses', 'id', 'orderNo', 'items', 'requestFrom', 'requestDate'));
        $this->set('_serialize', ['details']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Auth->user();
        $time = time();
        $this->loadModel('TransferItems');
        $this->loadModel('TransferResources');
        $this->loadModel('TransferEvents');

        if ($this->request->is('post'))
        {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus)
                {
                    $input = $this->request->data;
                    $event_id = $input['event_id'];
                    $itemDetail = $input['detail'];
                    $event = $this->TransferItems->get($event_id, ['contain' => ['TransferResources']]);

                    foreach($itemDetail as $warehouse_id=>$detail):
                        $resource = $this->TransferResources->newEntity();
                        $resourceData['reference_resource_id'] = $event['transfer_resource_id'];
                        $resourceData['resource_type'] = array_flip(Configure::read('transfer_resource_types'))['decide_storage'];
                        $resourceData['serial_no'] = $event['transfer_resource']['serial_no'];
                        $resourceData['created_by'] = $user['id'];
                        $resourceData['created_date'] = $time;
                        $resource = $this->TransferResources->patchEntity($resource, $resourceData);
                        $result = $this->TransferResources->save($resource);

                        // New event entry where recipient is HO user/ logged
                        $eventTbl = $this->TransferEvents->newEntity();
                        $eventData['transfer_resource_id'] = $result['id'];
                        $eventData['recipient_id'] = $user['id'];
                        $eventData['recipient_action'] = array_flip(Configure::read('transfer_event_types'))['make_challan_forward_send_delivery'];
                        $eventData['created_by'] = $user['id'];
                        $eventData['created_date'] = $time;
                        $eventTbl = $this->TransferEvents->patchEntity($eventTbl, $eventData);
                        $this->TransferEvents->save($eventTbl);

                        // Transfer items entry
                        foreach($detail as $item_id=>$quantity):
                            $item = $this->TransferItems->newEntity();
                            $itemData['transfer_resource_id'] = $result['id'];
                            $itemData['item_id'] = $item_id;
                            $itemData['quantity'] = $quantity;
                            $itemData['warehouse_id'] = $warehouse_id;
                            $itemData['created_by'] = $user['id'];
                            $itemData['created_date'] = $time;
                            $item = $this->TransferItems->patchEntity($item, $itemData);
                            $this->TransferItems->save($item);
                        endforeach;
                    endforeach;

                    // Update event
                    $transfer_events = TableRegistry::get('transfer_events');
                    $query = $transfer_events->query();
                    $query->update()->set(['is_action_taken' => 1])->where(['id' => $event_id])->execute();
                });
                $this->Flash->success('The Order has been made. Thank you!');
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('The Order has not been made. Please try again!');
            }
        }
        return $this->redirect(['controller' => 'DecideStorage', 'action' => 'index']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Transfer Item id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit()
    {
        $user = $this->Auth->user();
        $time = time();
        $id = $_REQUEST['id'];
        $recipient_id = $_REQUEST['recipient_id'];

        $this->loadModel('TransferEvents');
        $event = $this->TransferEvents->newEntity();
        $eventData['transfer_resource_id'] = $id;
        $eventData['recipient_id'] = $recipient_id;
        $eventData['recipient_action'] = array_flip(Configure::read('transfer_event_types'))['request'];
        $eventData['created_by'] = $user['id'];
        $eventData['created_date'] = $time;

        $event = $this->TransferEvents->patchEntity($event, $eventData);

        if ($this->TransferEvents->save($event)) {
            $this->Flash->success('Forwarding Complete');
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error('Forwarding not done. Please, try again.');
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Transfer Item id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->loadModel('TransferItems');
        $requestItem = $this->TransferItems->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $requestItem = $this->TransferItems->patchEntity($requestItem, $data);
        if ($this->TransferItems->save($requestItem)) {
            $this->Flash->success('The request item has been deleted.');
        } else {
            $this->Flash->error('The request item could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function distribute($id=null)
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
                        $existingStock = $this->Stocks->find('all', ['conditions'=>['warehouse_id'=>$itemInfo['warehouse_id'], 'item_id'=>$itemInfo['item_id']]])->first();
                        $stocks = TableRegistry::get('stocks');
                        $query = $stocks->query();
                        $query->update()->set(['quantity' => $existingStock['quantity']-$itemInfo['quantity']])->where(['id' => $existingStock['id']])->execute();
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
                        $existingStock = $this->Stocks->find('all', ['conditions'=>['warehouse_id'=>$itemInfo['warehouse_id'], 'item_id'=>$itemInfo['item_id']]])->first();
                        $stocks = TableRegistry::get('stocks');
                        $query = $stocks->query();
                        $query->update()->set(['quantity' => $existingStock['quantity']-$itemInfo['quantity']])->where(['id' => $existingStock['id']])->execute();
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

        $this->autoRender = false;
    }
}
