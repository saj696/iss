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
class DecideStorageController extends AppController
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

        $events = $this->TransferEvents->find('all', [
            'conditions' => ['TransferEvents.status !=' => 99, 'TransferEvents.recipient_id'=>$user['id']],
            'contain' => ['TransferResources']
        ]);

        $this->loadModel('Items');
        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']];
        }

        $this->loadModel('Warehouses');
        $warehouses = $this->Warehouses->find('list', ['conditions'=>['status'=>1]])->toArray();
        $this->loadModel('Depots');
        $depots = $this->Depots->find('list', ['conditions'=>['status'=>1]])->toArray();

        $this->loadModel('Users');
        $users = $this->Users->find('list', ['conditions'=>['user_group_id !='=>1,'status'=>1]]);

        $events = $this->paginate($events);
        $this->set(compact('itemArray', 'users', 'events', 'warehouses', 'depots'));
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

        $requestUserInfo = $this->Users->get($event['created_by']);
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1]])->toArray();
        $allWarehouses = $warehouses;
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

        $this->set(compact('requestWarehouseDetails', 'myWarehouseDetails', 'itemArray', 'warehouses', 'allWarehouses', 'id'));
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
        $requestItem = $this->TransferItems->newEntity();

        if ($this->request->is('post'))
        {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus)
                {
                    $input = $this->request->data;
                    $detailArray = $input['details'];
                    $resource = $this->TransferResources->newEntity();

                    if($user['user_group_id']==Configure::read('depot_in_charge_ug')):
                        $resourceData['trigger_type'] = array_flip(Configure::read('trigger_types'))['depot'];
                        $resourceData['trigger_id'] = $user['depot_id'];
                    elseif($user['user_group_id']==Configure::read('warehouse_in_charge_ug')):
                        $resourceData['trigger_type'] = array_flip(Configure::read('trigger_types'))['warehouse'];
                        $resourceData['trigger_id'] = $user['warehouse_id'];
                    endif;

                    $resourceData['resource_type'] = array_flip(Configure::read('transfer_resource_types'))['decide_storage'];
                    $resourceData['created_by'] = $user['id'];
                    $resourceData['created_date'] = $time;
                    $resource = $this->TransferResources->patchEntity($resource, $resourceData);
                    $result = $this->TransferResources->save($resource);

                    if(isset($_POST['forward']))
                    {
                        $event = $this->TransferEvents->newEntity();
                        $eventData['transfer_resource_id'] = $result['id'];
                        $eventData['recipient_id'] = $input['recipient_id'];
                        $eventData['recipient_action'] = array_flip(Configure::read('transfer_event_types'))['request'];
                        $eventData['created_by'] = $user['id'];
                        $eventData['created_date'] = $time;
                        $event = $this->TransferEvents->patchEntity($event, $eventData);
                        $this->TransferEvents->save($event);
                    }

                    foreach($detailArray as $detail)
                    {
                        $transfer = $this->TransferItems->newEntity();
                        $itemData['transfer_resource_id'] = $result['id'];
                        $itemData['item_id'] = $detail['item_id'];
                        $itemData['quantity'] = $detail['quantity'];
                        $itemData['created_by'] = $user['id'];
                        $itemData['created_date'] = $time;
                        $transfer = $this->TransferItems->patchEntity($transfer, $itemData);
                        $this->TransferItems->save($transfer);
                    }
                });

                $this->Flash->success('The Request has been made. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('The Request has not been made. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('Items');
        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $dropArray = [];
        foreach($items as $item) {
            $dropArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']];
        }

        $this->loadModel('UserGroups');
        $userGroups = $this->UserGroups->find('list', ['conditions'=>['status'=>1]]);

        $transferResources = $this->TransferItems->TransferResources->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('requestItem', 'dropArray', 'transferResources', 'userGroups'));
        $this->set('_serialize', ['requestItem']);
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

    public function ajax()
    {
        $this->loadModel('TransferItems');
        $this->loadModel('TransferEvents');
        $this->loadModel('TransferResources');
        $this->loadModel('Users');
        $this->loadModel('Warehouses');
        $this->loadModel('Depots');
        $this->loadModel('Items');
        $this->loadModel('Stocks');

        $data = $this->request->data;
        $warehouse_id = $data['warehouse_id'];
        $event_id = $data['event_id'];

        $warehouseInfo = $this->Warehouses->get($warehouse_id);
        $eventDetail = $this->TransferEvents->get($event_id);
        $resource = $this->TransferResources->get($eventDetail['transfer_resource_id'], ['contain'=>['TransferItems']]);
        $items = $resource['transfer_items'];

        foreach($items as $item):
            $warehouseStockDetail = [];
            $itemInfo = $this->Items->get($item['item_id']);
            $stock = $this->Stocks->find('all', ['conditions' => ['status' => 1, 'warehouse_id'=>$warehouse_id, 'item_id'=>$item['item_id']], 'fields'=>['quantity']])->first()->toArray();
            $warehouseStockDetail['warehouse_id'] = $warehouse_id;
            $warehouseStockDetail['item_id'] = $item['item_id'];
            $warehouseStockDetail['item_name'] = $itemInfo['name'].' - '.$itemInfo['pack_size'].' '.Configure::read('pack_size_units')[$itemInfo['unit']];
            $warehouseStockDetail['existing'] = isset($stock['quantity'])?$stock['quantity']:0;
            $warehouseDetails[] = $warehouseStockDetail;
        endforeach;

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('warehouseDetails', 'warehouseInfo'));
    }

    public function process()
    {
        $data = $this->request->data;
        if($data) {
            $eventId = $data['event_id'];
            $decidedArray = $data['decided'];
            $this->loadModel('Warehouses');
            $warehouses = $this->Warehouses->find('list', ['conditions'=>['status'=>1]])->toArray();

            $this->loadModel('Items');
            $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
            $itemArray = [];
            foreach($items as $item) {
                $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']];
            }

            $this->viewBuilder()->layout('default');
            $this->set(compact('decidedArray', 'warehouses', 'itemArray', 'eventId'));
        } else {
            $this->Flash->error('Please, try again sequentially!');
            return $this->redirect(['action' => 'index']);
        }
    }
}
