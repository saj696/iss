<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * MakeScheduledDeliveries Controller
 *
 * @property \App\Model\Table\MakeScheduledDeliveriesTable $MakeScheduledDeliveries
 */
class MakeScheduledDeliveriesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $user = $this->Auth->user();
        $deliverDo = TableRegistry::get('do_events')->find('all')
            ->where(['recipient_id' => $user['id'],
                'events_tepe' => Configure::read('object_type')['DS'],

            ])
            ->where(['do_events.action_status' => Configure::read('do_object_event_action_status')['awaiting_ds_delivery']])
            ->orWhere(['do_events.action_status' => Configure::read('do_object_event_action_status')['partial_ds_delivery']])
            ->contain(['Senders'])
            ->order(['do_events.id' => 'DESC'])
            ->toArray();
        //    echo "<pre>";print_r($deliverDo);die();
        //   $this->paginate($this->DeliverDo);

        $this->set(compact('deliverDo'));
        $this->set('_serialize', ['deliverDo']);
    }

    /**
     * View method
     *
     * @param string|null $id Make Scheduled Delivery id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $do_event_id = $id;
        $do_events = TableRegistry::get('do_events')->get($id);
        $ds_tbl_id = $do_events->do_object_id;
        $makeScheduledDelivery = TableRegistry::get('dds')->get($do_events->do_object_id);
        $do_objects = [];
        foreach (json_decode($makeScheduledDelivery->pi_ids) as $key => $pi_id) {

            $do_object = TableRegistry::get('do_objects')->get($pi_id->id);
            //Warehouse
            if ($do_object->target_type == 1) {
                $do_objects[$key]['type'] = "Warehouse";
                $warehouse = TableRegistry::get('warehouses')->get($do_object->target_id);
                $do_objects[$key]['target_name'] = $warehouse->name;
            } else {
                $do_objects[$key]['type'] = "Depot";
                $depots = TableRegistry::get('depots')->get($do_object->target_id);
                $do_objects[$key]['target_name'] = $depots->name;
            }
            $do_objects[$key]['id'] = $do_object->id;
            $do_objects[$key]['sl'] = $do_object->serial_no;
            $do_objects[$key]['date'] = date('d-M-Y', $do_object->date);

        }
        //echo "<pre>";print_r($do_objects);die();
        $this->set('do_event_id', $do_event_id);
        $this->set('ds_tbl_id', $ds_tbl_id);
        $this->set('do_objects', $do_objects);
        $this->set('_serialize', ['makeScheduledDelivery']);
    }

    public function ajaxMakeScheduledDeliv()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->viewBuilder()->layout('ajax');
            $user = $this->Auth->user();

            $data = $this->request->data;
            $do_event_id = $data['do_event_id'];
            $ds_tbl_id = $data['ds_tbl_id'];
            $do_object_id = $data['do_object_id'];


            $do_object = TableRegistry::get('do_objects')->find('all');
            $do_object->select(['id' => 'do_objects.id',
                'serial_no' => 'do_objects.serial_no',
                'date' => 'do_objects.date',
                'delivery_status' => 'do_objects.action_status',
                'requested_by_name' => 'users.full_name_en',
            ])
                ->leftJoin('users', 'users.id=do_objects.target_id')
                ->where(['do_objects.id' => $do_object_id]);
            $do_object = $do_object->first();

            $do_object_item = TableRegistry::get('do_object_items')->find('all');
            $do_object_item->select(['id' => 'do_object_items.id',
                'approved_quantity' => 'do_object_items.approved_quantity',
                'item_id' => 'do_object_items.item_id',
                'item_name' => 'items.name',
                'unit_id' => 'units.id',
             //   'current_stock' => 'stocks.quantity',
                'unit_name' => 'units.unit_display_name'

            ])
                ->leftJoin('items', 'items.id=do_object_items.item_id')
                ->leftJoin('units', 'units.id=do_object_items.unit_id')
             //   ->leftJoin('stocks', 'stocks.item_id=do_object_items.item_id AND stocks.manufacture_unit_id=do_object_items.unit_id')
                ->where(['do_object_items.do_object_id' => $data['do_object_id']])
              //  ->where(['stocks.warehouse_id' => $user['warehouse_id']])
                ->hydrate(false)->toArray();
				  $do_object_items=[];
          foreach($do_object_item as $key=>$row){
                $stock=TableRegistry::get('stocks')->find('all')
                    ->where(['warehouse_id' => $user['warehouse_id'],'item_id'=>$row['item_id'],'manufacture_unit_id'=>$row['unit_id']])->hydrate(false)->first();
               $do_object_items[$key]=$row;
               $do_object_items[$key]['current_stock']=$stock['quantity']?$stock['quantity']:0;					
                
            }

            

            $ds_tbl = TableRegistry::get('dds')->get($ds_tbl_id);
            $ddoss = [];
            foreach (json_decode($ds_tbl->do_ids) as $key => $do_id) {
                $ddos = TableRegistry::get('ddos')->get($do_id);
                $ddoss[$key] = $ddos;
            }

            foreach ($ddoss as $val) {
                $val['do_delivering_warehouse_name'] = $this->getWarehouseName($val['do_delivering_warehouse']);
                $val['do_receiving_warehouse_name'] = $this->getWarehouseName($val['do_receiving_warehouse']);
            }
//            echo "<pre>";
//            print_r($ddoss);
//            die();

            //  echo "<pre>";print_r($do_object->first());die();
            $this->set('ddoss', $ddoss);
            $this->set('do_object', $do_object);
            $this->set('do_object_items', $do_object_items);
            $this->set('do_event_id', $do_event_id);
            $this->set('ds_tbl_id', $ds_tbl_id);
            $this->set('do_object_id', $do_object_id);

            //echo "<pre>";print_r($data);die();


        }
    }

    public function makeScheduledDeliv($do_event_id, $ds_tbl_id, $do_object_id)
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Auth->user();
            $do_items = TableRegistry::get('do_object_items')->find('all')->where(['do_object_id' => $do_object_id])->toArray();
            foreach ($do_items as $row) {
                $stock = TableRegistry::get('stocks')->find('all')->where(['item_id' => $row['item_id'], 'manufacture_unit_id' => $row['unit_id'], 'warehouse_id' => $user['warehouse_id'], 'quantity >=' => $row['approved_quantity']])->first();
                if (!$stock) {
                    $this->Flash->error('Sorry!! dont have sufficient amount in stock');
                    return $this->redirect(['action' => 'index']);
                }
            }

            $get_ds_tbl = TableRegistry::get('dds')->get($ds_tbl_id);
            // echo "<pre>";print_r(json_decode($get_ds_tbl->pi_ids));die();
            $do_data = json_decode($get_ds_tbl->pi_ids);
            foreach ($do_data as $row) {
                if ($row->id == $do_object_id) {
                    $row->status = 'true';
                }
            }

            $set_dds = TableRegistry::get('dds');
            $query = $set_dds->query();
            $query->update()->set(['pi_ids' => json_encode($do_data)])->where(['id' => $ds_tbl_id])->execute();

            $check_val = 'true';
            foreach ($do_data as $row) {
                if ($row->status == 'false') {
                    $check_val = 'false';
                }
            }
            //if true then this even status will ve full delivered else partial
            if ($check_val == 'true') {
                $set_do_event = TableRegistry::get('do_events');
                $query = $set_do_event->query();
                $query->update()->set(['action_status' => Configure::read('do_object_event_action_status')['full_ds_delivery']])->where(['id' => $do_event_id])->execute();
            } else {
                $set_do_event = TableRegistry::get('do_events');
                $query = $set_do_event->query();
                $query->update()->set(['action_status' => Configure::read('do_object_event_action_status')['partial_ds_delivery']])->where(['id' => $do_event_id])->execute();

            }


            foreach ($do_items as $row) {
                $stock = TableRegistry::get('stocks')->find('all')->where(['item_id' => $row['item_id'], 'manufacture_unit_id' => $row['unit_id'], 'warehouse_id' => $user['warehouse_id']])->first();
                $stock_id = $stock->id;
                $set_stocks = TableRegistry::get('stocks');
                $query = $set_stocks->query();
                $query->update()->set(['quantity' => $stock->quantity - $row['approved_quantity']])->where(['id' => $stock_id])->execute();
            }

            $get_do_object = TableRegistry::get('do_objects')->get($do_object_id);

            if ($get_do_object->target_type == 1) {
                $get_user_info = TableRegistry::get('users')->find('all')->where(['warehouse_id' => $get_do_object->target_id])->first();
                $send_to = $get_user_info->id;
            } else {
                $get_user_info = TableRegistry::get('users')->find('all')->where(['depot_id' => $get_do_object->target_id])->first();
                $send_to = $get_user_info->id;
            }

            $this->loadModel('do_events');
            $set_do_event = $this->do_events->newEntity();
            $do_event_Data['sender_id'] = $user['id'];
            $do_event_Data['recipient_id'] = $send_to;
            $do_event_Data['do_object_id'] = $do_object_id;
            $do_event_Data['events_tepe'] = Configure::read('object_type')['DELIVERED_PI'];
            $do_event_Data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_scheduled_delivery_acceptance'];
            $do_event_Data['created_by'] = $user['id'];
            $do_event_Data['created_date'] = time();
            $set_do_event = $this->do_events->patchEntity($set_do_event, $do_event_Data);

            if ($this->do_events->save($set_do_event)) {
                $this->Flash->success(__('SUCCESS!! All information has bin save.'));
                return $this->redirect(['action' => 'index']);
            }

        }

    }

    public function viewDosItems($id)
    {
        $user = $this->Auth->user();
        //echo "<pre>";print_r($user);die();


        //   $do_events = TableRegistry::get('do_events')->get($id);

        $items = TableRegistry::get('ddos_items')->find('all')
            ->where(['ddo_id' => $id])
            ->contain(['Items', 'Units', 'Ddos'])->toArray();
        // echo "<pre>";print_r($items[0]['ddo']['do_receiving_warehouse']);die();


        $warehouse_id = $user['warehouse_id'];

        $this->set('items', $items);
        $this->set('id', $id);
        $this->set('warehouse_id', $warehouse_id);
        $this->set('_serialize', ['items']);
    }

    private function getWarehouseName($id)
    {
        $warehouse = TableRegistry::get('warehouses')->get($id);
        return $warehouse->name;
    }


}
