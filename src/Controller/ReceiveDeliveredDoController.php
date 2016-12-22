<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
/**
 * ReceiveDeliveredDo Controller
 *
 * @property \App\Model\Table\ReceiveDeliveredDoTable $ReceiveDeliveredDo
 */
class ReceiveDeliveredDoController extends AppController
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
            ->where(['recipient_id' => $user['id'], 'events_tepe' => Configure::read('object_type')['DOD'],
                'do_events.action_status' => Configure::read('do_object_event_action_status')['awaiting_reception']])
            ->contain(['Senders'])
            ->toArray();
        //  echo "<pre>";print_r($deliverDo);die();
        //   $this->paginate($this->DeliverDo);

        $this->set(compact('deliverDo'));
        $this->set('_serialize', ['deliverDo']);
    }

    /**
     * View method
     *
     * @param string|null $id Receive Delivered Do id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->loadModel('stocks');

        $user = $this->Auth->user();
        //echo "<pre>";print_r($user);die();


        $do_events = TableRegistry::get('do_events')->get($id);

        $items = TableRegistry::get('ddos_items')->find('all')
            ->where(['ddo_id' => $do_events->do_object_id])
            ->contain(['Items', 'Units', 'Ddos'])->toArray();
       // echo "<pre>";print_r($items[0]['ddo']['do_receiving_warehouse']);die();

        if ($this->request->is('post')) {

            $time = time();
           //   echo "<pre>";print_r($items);die();
            foreach ($items as $item) {
                $stock = TableRegistry::get('stocks')->find('all')
                    ->where(['warehouse_id' => $item['ddo']['do_receiving_warehouse'],
                        'item_id' => $item['item']['id'],
                        'manufacture_unit_id' => $item['unit']['id']])
                    ->first();
//Todo Insert item if not in stock table
                if($stock){
                    $quantity=($stock->quantity )+($item['quantity']);
                    $set_stock = TableRegistry::get('stocks');
                    $query = $set_stock->query();
                    $query->update()->set(['quantity' => $quantity, 'updated_by' => $user['id'], 'updated_date' => $time])
                        ->where(['id' => $stock['id']])->execute();
                }else{
                    $stock = $this->stocks->newEntity();
                    $data['warehouse_id'] = $item['ddo']['do_receiving_warehouse'];
                    $data['item_id'] = $item['item']['id'];
                    $data['manufacture_unit_id'] = $item['unit']['id'];
                    $data['quantity'] = $item['quantity'];
                    $data['approved_quantity'] = 0;
                    $data['created_by'] = $user['id'];
                    $data['created_date'] = $time;
                    $stock = $this->stocks->patchEntity($stock, $data);
                    $this->stocks->save($stock);
                }


            }
         //   echo "<pre>";print_r($query);die();

            $do_event = TableRegistry::get('do_events');
            $query = $do_event->query();
            $query->update()->set(['action_status' => Configure::read('do_object_event_action_status')['delivery_accepted'], 'updated_by' => $user['id'], 'updated_date' => $time])->where(['id' => $id])->execute();

            if ($query) {
                $this->Flash->success(__('SUCCESS!! All information has bin save.'));
                return $this->redirect(['action' => 'index']);
            }

        }

        $warehouse_id = $user['warehouse_id'];

        $this->set('items', $items);
        $this->set('id', $id);
        $this->set('warehouse_id', $warehouse_id);
        $this->set('_serialize', ['items']);
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


}
