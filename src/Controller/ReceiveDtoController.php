<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * ReceiveDto Controller
 *
 * @property \App\Model\Table\ReceiveDtoTable $ReceiveDto
 */
class ReceiveDtoController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $user = $this->Auth->user();
        $deliverDto = TableRegistry::get('dto_events')->find('all')
            ->where(['recipient_id' => $user['id'], 'dto_events.action_status' => Configure::read('dts_action_status')['awaiting_dts_delivery']])
            ->contain(['Users'])
            ->toArray();
        //  echo "<pre>";print_r($deliverDto);die();
        //   $this->paginate($this->DeliverDo);

        $this->set(compact('deliverDto'));
        $this->set('_serialize', ['deliverDo']);
    }

    /**
     * View method
     *
     * @param string|null $id Receive Dto id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        //echo "<pre>";print_r($user);die();


        $dto_event_items = TableRegistry::get('dto_event_items')->find('all');
        $dto_event_items->select(['id' => 'dto_event_items.id',
            'quantity' => 'dto_event_items.quantity',
            'item_id' => 'dto_event_items.item_id',
            'item_name' => 'items.name',
            'unit_id' => 'units.id',
            'unit_name' => 'units.unit_display_name'])
            ->leftJoin('items', 'items.id=dto_event_items.item_id')
            ->leftJoin('units', 'units.id=dto_event_items.unit_id')
            ->where(['dto_event_items.dto_event_id' => $id]);
//        echo "<pre>";
//        print_r($dto_event_items->toArray());
//        die();

        if ($this->request->is('post')) {
            $data = $this->request->data;
            //   echo "<pre>";print_r($data);die();
            $time = time();
            //   echo "<pre>";print_r($items);die();
            $items = TableRegistry::get('dto_event_items')->find('all')->where(['dto_event_id' => $id]);
            foreach ($items as $item) {
                $stock = TableRegistry::get('stocks')->find('all')
                    ->where(['warehouse_id' => $user['warehouse_id'],
                        'item_id' => $item['item_id'],
                        'manufacture_unit_id' => $item['unit_id']])
                    ->first();
//                echo "<pre>";
//                print_r($stock);
//                die();
                if ($stock) {
                    $quantity = ($stock->quantity) + ($item['quantity']);
                    $set_stock = TableRegistry::get('stocks');
                    $query = $set_stock->query();
                    $query->update()->set(['quantity' => $quantity, 'updated_by' => $user['id'], 'updated_date' => $time])
                        ->where(['id' => $stock['id']])->execute();
                }

            }
            //   echo "<pre>";print_r($query);die();

            $do_event = TableRegistry::get('dto_events');
            $query = $do_event->query();
            $query->update()->set(['action_status' => Configure::read('dts_action_status')['delivery_accepted'], 'updated_by' => $user['id'], 'updated_date' => $time])->where(['id' => $id])->execute();

            if ($query) {
                $this->Flash->success(__('SUCCESS!! All information has bin save.'));
                return $this->redirect(['action' => 'index']);
            }

        }

        //   $warehouse_id = $user['warehouse_id'];
        $warehouses_id = $user['warehouse_id'];
        $this->set('dto_event_items', $dto_event_items);
        //  $this->set('items', $items);
        $this->set('id', $id);
        $this->set('warehouses_id', $warehouses_id);
        //   $this->set('warehouse_id', $warehouse_id);
        $this->set('_serialize', ['items']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $receiveDto = $this->ReceiveDto->newEntity();
        if ($this->request->is('post')) {
            $receiveDto = $this->ReceiveDto->patchEntity($receiveDto, $this->request->data);
            if ($this->ReceiveDto->save($receiveDto)) {
                $this->Flash->success(__('The receive dto has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The receive dto could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('receiveDto'));
        $this->set('_serialize', ['receiveDto']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Receive Dto id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $receiveDto = $this->ReceiveDto->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $receiveDto = $this->ReceiveDto->patchEntity($receiveDto, $this->request->data);
            if ($this->ReceiveDto->save($receiveDto)) {
                $this->Flash->success(__('The receive dto has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The receive dto could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('receiveDto'));
        $this->set('_serialize', ['receiveDto']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Receive Dto id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $receiveDto = $this->ReceiveDto->get($id);
        if ($this->ReceiveDto->delete($receiveDto)) {
            $this->Flash->success(__('The receive dto has been deleted.'));
        } else {
            $this->Flash->error(__('The receive dto could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
