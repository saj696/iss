<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * DoEvents Controller
 *
 * @property \App\Model\Table\DoEventsTable $DoEvents
 */
class DoEventsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'DoEvents.id' => 'desc'
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
        $doEvents = $this->DoEvents->find('all', [
            'conditions' => ['DoEvents.recipient_id ' => $user['id']],
            'contain' => ['Senders', 'DoObjects']
        ])
            ->where(['DoEvents.action_status' => Configure::read('do_object_event_action_status')['awaiting_approval']])
            ->orWhere(['DoEvents.action_status' => Configure::read('do_object_event_action_status')['approved']]);
        // echo "<pre>";print_r($doEvents->toArray());die();
        $this->set('doEvents', $doEvents);
        $this->set('_serialize', ['doEvents']);
    }

    /**
     * View method
     *
     * @param string|null $id Do Event id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $doEvent = $this->DoEvents->get($id);
        if ($this->request->is('post')) {
//echo "<pre>";print_r( Configure::read('do_object_event_action_status')['approved']);die();
            $data = $this->request->data;
            //echo "<pre>";print_r($data['approve_quantity'][1]);die();
            foreach ($data['item_id'] as $key => $item) {
                //  echo "<pre>";print_r($key);die();
                $do_object_items = TableRegistry::get('do_object_items');
                $query = $do_object_items->query();
                $query->update()
                    ->set(['approved_quantity' => $data['approve_quantity'][$key]])
                    ->where(['unit_id' => $data['item_unit_id'][$key],
                        'do_object_id' => $doEvent->do_object_id,
                        'item_id' => $item])
                    ->execute();

            }

            $do_event = TableRegistry::get('do_events');
            $q = $do_event->query();
            $q->update()
                ->set(['action_status' => Configure::read('do_object_event_action_status')['approved']])
                ->where(['do_object_id' => $doEvent->do_object_id])
                ->execute();

            if ($q) {
                $this->Flash->success('The do event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do event could not be saved. Please, try again.');
            }
        }

        $doEvent = $this->DoEvents->get($id);
        $do_items = TableRegistry::get('do_object_items')
            ->find('all', ['conditions' => ['do_object_id' => $doEvent->do_object_id]])
            ->contain(['Items.ItemUnits', 'DoObjects'])
            ->toArray();
//echo "<pre>";print_r($do_items);die();

        if ($do_items[0]['do_object']['target_type'] == Configure::read('target_type')['sales_point_(depot)']) {
            $warehouse = TableRegistry::get('depots')->get($do_items[0]['do_object']['target_id']);
            $warehouse_id = json_decode($warehouse->warehouses)[0];
        } else {
            $warehouse_id = $do_items[0]['do_object']['target_id'];
        }


        $userAdmin = $user['administrative_unit_id'];
        $this->loadModel('AdministrativeUnits');

        $userAdminGlobal = $this->AdministrativeUnits->get($userAdmin);
        $limitStart = pow(2, (Configure::read('max_level_no') - $user['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $user['level_no']) * 5);

        $warehouses = TableRegistry::get('warehouses')->query();
        //  $warehouses->contain('AdministrativeUnits');
        $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '>= ' . $limitStart);
        $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '< ' . $limitEnd);
        $warehouses->where('warehouses.status!= 99');
//echo "<pre>";print_r($warehouses->toArray());die();
        $this->set('id', $id);
        $this->set('warehouses', $warehouses);
        $this->set('do_items', $do_items);
        $this->set('doEvent', $doEvent);
        $this->set('warehouse_id', $warehouse_id);
        $this->set('_serialize', ['doEvent']);
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
        $doEvent = $this->DoEvents->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
            if ($this->DoEvents->save($doEvent)) {
                $this->Flash->success('The do event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do event could not be saved. Please, try again.');
            }
        }
        $senders = $this->DoEvents->Senders->find('list', ['conditions' => ['status' => 1]]);
        $recipients = $this->DoEvents->Recipients->find('list', ['conditions' => ['status' => 1]]);
        $doObjects = $this->DoEvents->DoObjects->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('doEvent', 'senders', 'recipients', 'doObjects'));
        $this->set('_serialize', ['doEvent']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Do Event id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $doEvent = $this->DoEvents->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
            if ($this->DoEvents->save($doEvent)) {
                $this->Flash->success('The do event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do event could not be saved. Please, try again.');
            }
        }
        $senders = $this->DoEvents->Senders->find('list', ['conditions' => ['status' => 1]]);
        $recipients = $this->DoEvents->Recipients->find('list', ['conditions' => ['status' => 1]]);
        $doObjects = $this->DoEvents->DoObjects->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('doEvent', 'senders', 'recipients', 'doObjects'));
        $this->set('_serialize', ['doEvent']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Do Event id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $doEvent = $this->DoEvents->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
        if ($this->DoEvents->save($doEvent)) {
            $this->Flash->success('The do event has been deleted.');
        } else {
            $this->Flash->error('The do event could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getItems()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Auth->user();

            $data = $this->request->data;
            //  echo "<pre>";print_r($data);die();
            $this->viewBuilder()->layout('ajax');
            $sender_warehouse_id = $data['sender_warehouse_id'];
            $stocks = TableRegistry::get('stocks')
                ->find('all', ['conditions' => ['warehouse_id' => $data['warehouse_id'], 'item_id IN' => $data['item_ids'], 'manufacture_unit_id IN' => $data['item_unit_ids']]])
                ->contain(['Items', 'Units'])
                ->toArray();
            //  echo "<pre>";print_r($stocks);die();
            $this->set(compact('stocks', 'sender_warehouse_id'));
            // echo "<pre>";print_r($stocks);die();
        }
    }

    public function makeDoDs()
    {
            $data = $this->request->data;
            $user = $this->Auth->user();





            $userAdmin = $user['administrative_unit_id'];
            $this->loadModel('AdministrativeUnits');

            $userAdminGlobal = $this->AdministrativeUnits->get($userAdmin);
            $limitStart = pow(2, (Configure::read('max_level_no') - $user['level_no'] - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $user['level_no']) * 5);

            $warehouses = TableRegistry::get('warehouses')->query();
            //  $warehouses->contain('AdministrativeUnits');
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '>= ' . $limitStart);
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '< ' . $limitEnd);
            $warehouses->where('warehouses.status!= 99');

            $this->set(compact('warehouses','data'));
//
//            echo "<pre>";
//            print_r($do_items->toArray());
//            die();

    }

    public function ajaxMakeDoDs(){
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
          //  echo "<pre>";print_r();die();
            $do_object_ids = [];

            foreach ($data['events']['events'] as $key => $row) {
                $doEvent = $this->DoEvents->get($row);
                $do_object_ids[] = $doEvent->do_object_id;
            }

            $do_items = TableRegistry::get('do_object_items')->find()
                ->select(['item_id'=>'do_object_items.item_id',
                    'unit_id'=> 'do_object_items.unit_id',
                    'item_id'=>'items.id',
                    'item_name'=>'items.name',
                    'unit_name'=>'units.unit_display_name'
                ])
                ->where(['do_object_items.do_object_id IN' => $do_object_ids])

                ->select(['total_amount'=>'SUM(do_object_items.approved_quantity)'])
                ->leftJoin('items', 'items.id=do_object_items.item_id')
                ->leftJoin('units', 'units.id=do_object_items.unit_id')
                ->group(['do_object_items.item_id','do_object_items.unit_id'])
            ->order(['do_object_items.item_id'=>'DESC']);


            foreach($do_items as $row){
                $stocks = TableRegistry::get('stocks')->find()
                    ->where(['manufacture_unit_id'=>$row->unit_id,'item_id'=>$row->item_id])
                    ->first();
             if($stocks){
                 $row['stock_amount']= $stocks['quantity'];
             }else{
                 $row['stock_amount']=0;
             }
            }

            echo "<pre>";print_r($do_items->toArray());die();
        }
    }
}
