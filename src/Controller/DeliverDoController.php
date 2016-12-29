<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * DeliverDo Controller
 *
 * @property \App\Model\Table\DeliverDoTable $DeliverDo
 */
class DeliverDoController extends AppController
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
            ->where(['recipient_id' => $user['id'], 'events_tepe' => Configure::read('object_type')['DO'],
                'do_events.action_status' => Configure::read('do_object_event_action_status')['awaiting_do_delivery']])
            ->contain(['Senders'])
            ->toArray();
        //   echo "<pre>";print_r($deliverDo);die();
        //   $this->paginate($this->DeliverDo);

        $this->set(compact('deliverDo'));
        $this->set('_serialize', ['deliverDo']);
    }

    /**
     * View method
     *
     * @param string|null $id Deliver Do id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        //echo "<pre>";print_r($user);die();


        $do_events = TableRegistry::get('do_events')->get($id);

        $ddos=TableRegistry::get('ddos')->get($do_events->do_object_id);
        $receivingWareHouse=TableRegistry::get('warehouses')->get($ddos->do_receiving_warehouse);


        $items = TableRegistry::get('ddos_items')->find('all')
            ->where(['ddo_id' => $do_events->do_object_id])
            ->contain(['Items', 'Units', 'Ddos'])->toArray();
        //echo "<pre>";print_r($items[0]['ddo']['do_receiving_warehouse']);die();

        if ($this->request->is('post')) {
            foreach ($items as $item) {
                $stock = TableRegistry::get('stocks')->find('all')
                    ->where(['warehouse_id' => $item['ddo']['do_delivering_warehouse'],
                        'item_id' => $item['item']['id'],
                        'manufacture_unit_id' => $item['unit']['id'],
                        'quantity >' => $item['quantity']])
                    ->first();
                if (!$stock) {
                    $this->Flash->error(__('Insufficient Stock.'));
                    return $this->redirect(['action' => 'index']);
                }
            }
            $time = time();
            //  echo "<pre>";print_r($items);die();
            foreach ($items as $item) {
                $stock = TableRegistry::get('stocks')->find('all')
                    ->where(['warehouse_id' => $item['ddo']['do_delivering_warehouse'],
                        'item_id' => $item['item']['id'],
                        'manufacture_unit_id' => $item['unit']['id']])
                    ->first();

                $set_stock = TableRegistry::get('stocks');
                $query = $set_stock->query();
                $query->update()->set(['quantity' => $stock->quantity - $item['quantity'], 'updated_by' => $user['id'], 'updated_date' => $time])->where(['id' => $stock->id])->execute();

            }
            $do_event = TableRegistry::get('do_events');
            $query = $do_event->query();
            $query->update()->set(['action_status' => Configure::read('do_object_event_action_status')['delivered'], 'updated_by' => $user['id'], 'updated_date' => $time])->where(['id' => $id])->execute();

            $this->loadModel('do_events');

            $set_do_event = $this->do_events->newEntity();
            $do_event_Data['sender_id'] = $user['id'];
            $do_event_Data['recipient_id'] = $this->getWareHouseSuperVisorUserID($items[0]['ddo']['do_receiving_warehouse']);
            $do_event_Data['do_object_id'] = $items[0]['ddo']['id'];
            $do_event_Data['events_tepe'] = Configure::read('object_type')['DOD'];
            $do_event_Data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_reception'];
            $do_event_Data['created_by'] = $user['id'];
            $do_event_Data['created_date'] = $time;
            $set_do_event = $this->do_events->patchEntity($set_do_event, $do_event_Data);

            if ($this->do_events->save($set_do_event)) {
                $this->Flash->success(__('SUCCESS!! All information has bin save.'));
                return $this->redirect(['action' => 'index']);
            }

        }

        $warehouse_id = $user['warehouse_id'];

        $this->set('items', $items);
        $this->set('receivingWareHouse', $receivingWareHouse);
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
