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
            ->orWhere(['DoEvents.action_status' => Configure::read('do_object_event_action_status')['approved']])
            ->orWhere(['DoEvents.action_status' => Configure::read('do_object_event_action_status')['Action_Taken']])
            ->order(['DoEvents.id' => 'DESC']) ;
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
        $doEvent = $this->DoEvents->get($id, [
            'contain' => ['Senders','DoObjects']
        ]);
      //  echo "<pre>";print_r($doEvent);die();
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
            $do_objects = TableRegistry::get('do_objects');
            $query = $do_objects->query();
            $query->update()
                ->set(['action_status' => Configure::read('do_object_action_status')['Approved']])
                ->where(['id' => $doEvent->do_object_id])
                ->execute();

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

      //  $doEvent = $this->DoEvents->get($id);
        $do_items = TableRegistry::get('do_object_items')
            ->find('all', ['conditions' => ['do_object_id' => $doEvent->do_object_id]])
            ->contain(['Items.ItemUnits.Units', 'DoObjects'])
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
        $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '>= ' . 0);
        $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '<= ' . $limitEnd);
        $warehouses->where('warehouses.status!= 99');
//echo "<pre>";print_r($warehouses->toArray());die();
        $this->set('id', $id);
        $this->set('warehouses', $warehouses);
        $this->set('do_items', $do_items);
        $this->set('doEvent', $doEvent);
        $this->set('warehouse_id', $warehouse_id);
        $this->set('_serialize', ['doEvent']);
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
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $user = $this->Auth->user();


            $userAdmin = $user['administrative_unit_id'];
            $this->loadModel('AdministrativeUnits');

            $userAdminGlobal = $this->AdministrativeUnits->get($userAdmin);
            $limitStart = pow(2, (Configure::read('max_level_no') - $user['level_no'] - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $user['level_no']) * 5);

            $warehouses = TableRegistry::get('warehouses')->query();
            //  $warehouses->contain('AdministrativeUnits');
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '>= ' . 0);
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '< ' . $limitEnd);
            $warehouses->where('warehouses.status!= 99');

            $this->set(compact('warehouses', 'data'));
        }else{
            $this->Flash->error('Sorry!! Please try again');
            return $this->redirect(['action' => 'index']);
        }
//
//            echo "<pre>";
//            print_r($do_items->toArray());
//            die();

    }

    public function ajaxMakeDoDs()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $this->viewBuilder()->layout('ajax');
            $user = $this->Auth->user();

            //  echo "<pre>";print_r();die();
            $do_object_ids = [];

            foreach ($data['events']['events'] as $key => $row) {
                $doEvent = $this->DoEvents->get($row);
                $do_object_ids[] = $doEvent->do_object_id;
            }


            $do_items = TableRegistry::get('do_object_items')->find()
                ->select(['item_id' => 'do_object_items.item_id',
                    'unit_id' => 'do_object_items.unit_id',
                    'item_id' => 'items.id',
                    'item_name' => 'items.name',
                    'unit_name' => 'units.unit_display_name',
                    'unit_size' => 'units.unit_size',
                    'unit_type' => 'units.unit_type',
                    'converted_quantity' => 'units.converted_quantity'
                ])
                ->where(['do_object_items.do_object_id IN' => $do_object_ids])
                ->select(['asked_quantity' => 'SUM(do_object_items.approved_quantity)'])
                ->leftJoin('items', 'items.id=do_object_items.item_id')
                ->leftJoin('units', 'units.id=do_object_items.unit_id')
                ->group(['do_object_items.item_id', 'do_object_items.unit_id'])
                ->order(['do_object_items.item_id' => 'DESC']);
         //   ->hydrate(false);
            $do_items = $do_items->toArray();

//echo "<pre>";print_r($do_items);die();
            foreach ($do_items as $row) {

                $stocks = TableRegistry::get('stocks')->find()
                    ->where(['manufacture_unit_id' => $row->unit_id, 'item_id' => $row->item_id, 'warehouse_id' => $data['warehouse_id']])
                    ->first();
                if ($stocks) {
                    $row['stock_amount'] = $stocks['quantity'];
                } else {
                    $row['stock_amount'] = 0;
                }
            }

            foreach ($do_items as $row) {
                $r = $row['asked_quantity'] - $row['stock_amount'];
                if ($r > 0) {
                    if ($row['unit_type'] == array_flip(Configure::read('pack_size_units'))['gm'] || $row['unit_type'] == array_flip(Configure::read('pack_size_units'))['ml']) {
                        $row['require'] = ($r * ($row['converted_quantity'] ? $row['converted_quantity'] : 1)) / 1000;
                    } else {
                        $row['require'] = ($r * ($row['converted_quantity'] ? $row['converted_quantity'] : 1)) / 1;
                    }
                } else {
                    $row['require'] = 0;
                }

                if ($r > 0) {
                    $row['further_needed'] = $r;
                } else {
                    $row['further_needed'] = 0;
                }
            }
//echo "<pre>";print_r($do_items);die();
            $userAdmin = $user['administrative_unit_id'];
            $this->loadModel('AdministrativeUnits');

            $userAdminGlobal = $this->AdministrativeUnits->get($userAdmin);
            $limitStart = pow(2, (Configure::read('max_level_no') - $user['level_no'] - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $user['level_no']) * 5);

            $warehouses = TableRegistry::get('warehouses')->query();
            //  $warehouses->contain('AdministrativeUnits');
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '>= ' . 0);
            $warehouses->where('global_id -' . $userAdminGlobal['global_id'] . '< ' . $limitEnd);
            $warehouses->where('warehouses.status!= 99');

            $parent_warehouse_id = $data['warehouse_id'];
            $this->set(compact('do_items', 'warehouses', 'do_object_ids', 'parent_warehouse_id'));

        }
    }

    public function getItemName()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            // echo "<pre>";print_r($data);die();
            $items = $this->Common->item_name_resolver($data['warehouse_id']);
            $this->response->body(json_encode($items));
            return $this->response;
            //  echo "<pre>";print_r($items);die();
        }
    }

    public function fixDoQuantities()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {

            $user = $this->Auth->user();
            $time = time();
            $data = $this->request->data;
          // echo "<pre>";print_r($data);die();
            $this->loadModel('ddos');
            $this->loadModel('ddos_items');
            $this->loadModel('dds');
            $this->loadModel('do_events');

            //serial number
            $this->loadModel('do_events');
            // Serials Table Insert/ update
            $this->loadModel('Serials');
            $serial_for = array_flip(Configure::read('serial_types'))['do_ds_object'];
            $year = date('Y');

            if ($user['user_group_id'] == Configure::read('depot_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['depot'];
                $trigger_id = $user['depot_id'];
            elseif ($user['user_group_id'] == Configure::read('warehouse_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['warehouse'];
                $trigger_id = $user['warehouse_id'];
            else:
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['others'];
                $trigger_id = $user['administrative_unit_id'];
            endif;

            $existence = $this->Serials->find('all', ['conditions' => ['serial_for' => $serial_for, 'year' => $year, 'trigger_type' => $trigger_type, 'trigger_id' => $trigger_id]])->first();
            $serial_no = null;
            if ($existence) {
                $serial = TableRegistry::get('serials');
                $query = $serial->query();
                $query->update()->set(['serial_no' => $existence['serial_no'] + 1])->where(['id' => $existence['id']])->execute();
                $serial_no = $existence['serial_no'] + 1;
            } else {
                $serial = $this->Serials->newEntity();
                $serialData['trigger_type'] = $trigger_type;
                $serialData['trigger_id'] = $trigger_id;
                $serialData['serial_for'] = $serial_for;
                $serialData['year'] = $year;
                $serialData['serial_no'] = 1;
                $serialData['created_by'] = $user['id'];
                $serialData['created_date'] = $time;
                $serial = $this->Serials->patchEntity($serial, $serialData);
                $this->Serials->save($serial);
                $serial_no = 1;

            }
         //   echo "<pre>";print_r($serial_no);die();

            //End serial number
            $dos_ids=[];
            $dos_items_ids=[];
            $pi_ids=[];
            foreach ($data['do_object_items'] as $row) {

                // validation test
                if (!$this->checkWareHouseSuperVisorAvailability($row['warehouse_id'])) {
                    $this->Flash->error('Sorry!! System Did Not Found  One Of WareHouses Incharge ID');
                    return $this->redirect(['action' => 'index']);
                }
            }
            foreach($data['pi_ids'] as $key=>$id){
                $pi_ids[$key]['id']=$id;
                $pi_ids[$key]['status']='false';
            }

            foreach($data['pi_ids'] as $key=>$id){
                $do_event = TableRegistry::get('do_events');
                $query = $do_event->query();
                $query->update()->set(['action_status' => Configure::read('do_object_event_action_status')['Action_Taken']])->where(['do_object_id'=>$id,'action_status' => Configure::read('do_object_event_action_status')['approved'],'events_tepe'=>1])->execute();

            }

            // Khala shuru

            foreach ($data['do_object_items'] as $row) {

                $getDOs=TableRegistry::get('ddos')->find('all')->where(['do_delivering_warehouse'=>$row['warehouse_id'],'do_receiving_warehouse'=>$row['destination_id'],'created_date'=>$time])->first();
                if($getDOs){
                    $do_id=$getDOs['id'];
                }else{
                    $set_DOs = $this->ddos->newEntity();
                    $dos_Data['date'] = $time;
                    $dos_Data['do_delivering_warehouse'] = $row['warehouse_id'];
                    $dos_Data['do_receiving_warehouse'] = $row['destination_id'];
                    $dos_Data['do_ds_serial_number'] = $serial_no;
                    $dos_Data['created_by'] = $user['id'];
                    $dos_Data['created_date'] = $time;
                    $set_DOs = $this->ddos->patchEntity($set_DOs, $dos_Data);
                    $inserted_dos_data =   $this->ddos->save($set_DOs);
                    $do_id= $inserted_dos_data['id'];
                    $dos_ids[]=$do_id;

                    //INSERT data into DO_events table for DO's

                    $set_do_event = $this->do_events->newEntity();
                    $do_event_Data['sender_id'] = $user['id'];
                    $do_event_Data['recipient_id'] = $this->getWareHouseSuperVisorUserID($row['warehouse_id']);
                    $do_event_Data['do_object_id'] = $do_id;
                    $do_event_Data['events_tepe'] =  Configure::read('object_type')['DO'];
                    $do_event_Data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_do_delivery'];
                    $do_event_Data['created_by'] = $user['id'];
                    $do_event_Data['created_date'] = $time;
                    $set_do_event = $this->do_events->patchEntity($set_do_event, $do_event_Data);
                    $inserted_do_event_data =   $this->do_events->save($set_do_event);

                }

                //INSERT data in to do_items table

                     $set_dos_item= $this->ddos_items->newEntity();
                     $dos_item_data['ddo_id']=$do_id;
                     $dos_item_data['item_id']=$row['item_id'];
                     $dos_item_data['unit_id']=$row['do_unit'];
                     $dos_item_data['quantity']=$row['do_quantity'];
                     $set_dos_item = $this->ddos_items->patchEntity($set_dos_item, $dos_item_data);
                     $inserted_dos_item_data =   $this->ddos_items->save($set_dos_item);
                     $dos_items_ids[]=$inserted_dos_item_data['id'];


            }

            //INSERT data in to ds table
            $set_ds= $this->dds->newEntity();
            $ds_data['date']=$time;
            $ds_data['pi_ids']=json_encode($pi_ids);
            $ds_data['do_ids']=json_encode($dos_ids);
            $ds_data['do_ds_serial_number']=$serial_no;
            $set_ds = $this->dds->patchEntity($set_ds, $ds_data);
            $inserted_ds_data =   $this->dds->save($set_ds);

            //INSERT data into DO_events table for Ds's

            $set_do_event = $this->do_events->newEntity();
            $do_event_Data['sender_id'] = $user['id'];
            $do_event_Data['recipient_id'] =$this->getWareHouseSuperVisorUserID($data['parent_warehouse_id']);
            $do_event_Data['do_object_id'] = $inserted_ds_data['id'];
            $do_event_Data['events_tepe'] =  Configure::read('object_type')['DS'];
            $do_event_Data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_ds_delivery'];
            $do_event_Data['created_by'] = $user['id'];
            $do_event_Data['created_date'] = $time;
            $set_do_event = $this->do_events->patchEntity($set_do_event, $do_event_Data);
            $inserted_do_event_data =   $this->do_events->save($set_do_event);


            $this->Flash->success('SUCCESS!! All information is saved');
            return $this->redirect(['action' => 'index']);

        }
    }

    private function checkWareHouseSuperVisorAvailability($wareHouse_id)
    {
        $user = TableRegistry::get('users')->find('all')->where(['warehouse_id' => $wareHouse_id])->first();
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    private function getWareHouseSuperVisorUserID($wareHouse_id)
    {
        $user = TableRegistry::get('users')->find('all')->where(['warehouse_id' => $wareHouse_id])->first();
        if ($user) {
            return $user['id'];
        } else {
            return false;
        }
    }

    public function getItemUnits()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {

            $data = $this->request->data;
            $item_unit = TableRegistry::get('item_units')->find('all',
                ['conditions' => ['item_id' => $data['item_id']]])
                ->contain(['Units'])
                ->hydrate(false)->toArray();
            //   echo "<pre>";print_r($item_unit);die();
            $item_units = [];
            foreach ($item_unit as $unit):
                $item_units[$unit['unit']['id']] = $unit['unit']['unit_display_name'];
            endforeach;

            $this->response->body(json_encode($item_units));
            return $this->response;
        }
    }

    public function getWarehouseBulkStock(){
        if ($this->request->is(['patch', 'post', 'put'])) {

            $data = $this->request->data;
            $quantity  = $this->Common->get_bulk_unit_sum_from_stock($data['warehouse_id'],$data['item_id']);

            $this->response->body($quantity);
            return $this->response;
        }

    }
}




