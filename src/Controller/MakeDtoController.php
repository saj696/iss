<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * MakeDto Controller
 *
 * @property \App\Model\Table\MakeDtoTable $MakeDto
 */
class MakeDtoController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $user = $this->Auth->user();
        $time = time();

        $user_warehouse_id = $user['warehouse_id'];

        if ($this->request->is('post')) {
            $data = $this->request->data;
            //echo "<pre>";print_r($data);die();
            $this->loadModel('dto_events');
            $this->loadModel('dto_event_items');
            foreach ($data['item'] as $item) {

                    $set_stock = TableRegistry::get('stocks');
                    $query = $set_stock->query();
                    $query->update()->set(['quantity' => $item['stock_quantity']-$item['quantity'], 'updated_by' => $user['id'], 'updated_date' => $time])
                        ->where(['warehouse_id' => $user_warehouse_id,'manufacture_unit_id'=>$item['unit_id'],'item_id'=>$item['item_id']])->execute();

            }

            $set_dto_event = $this->dto_events->newEntity();
            $dto_event_Data['sender_id'] = $user['id'];
            $dto_event_Data['recipient_id'] = $this->getWareHouseSuperVisorUserID($data['warehouse']);
            $dto_event_Data['action_status'] = Configure::read('dts_action_status')['awaiting_dts_delivery'];
            $dto_event_Data['created_by'] = $user['id'];
            $dto_event_Data['created_date'] = $time;
            $set_dto_event = $this->dto_events->patchEntity($set_dto_event, $dto_event_Data);

            $dto_event_data = $this->dto_events->save($set_dto_event);
            //   echo "<pre>";print_r($dto_event_data->id);die();
            $dto_items_ids = [];
            foreach ($data['item'] as $item) {
                if ($item['quantity'] > 0) {
                    $set_dto_item = $this->dto_event_items->newEntity();
                    $dos_item_data['dto_event_id'] = $dto_event_data->id;
                    $dos_item_data['item_id'] = $item['item_id'];
                    $dos_item_data['unit_id'] = $item['unit_id'];
                    $dos_item_data['quantity'] = $item['quantity'];
                    $set_dto_item = $this->dto_event_items->patchEntity($set_dto_item, $dos_item_data);
                    $inserted_dto_item_data = $this->dto_event_items->save($set_dto_item);
                    $dto_items_ids[] = $inserted_dto_item_data->id;
                }
            }

            if ($dto_items_ids) {
                $this->Flash->success(__('The make dto has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The make dto could not be saved. Please, try again.'));
            }

        }


        $warehouses = TableRegistry::get('direct_transfer_permissions')->find('all')
            ->select(['id' => 'warehouses.id', 'warehouse_name' => 'warehouses.name'])
            ->leftJoin('warehouses', 'warehouses.id=direct_transfer_permissions.to_warehouse')
            ->where(['direct_transfer_permissions.from_warehouse' => $user['warehouse_id']])
            ->hydrate(false);

        $items = $this->Common->item_name_resolver($user_warehouse_id);
//echo "<pre>";print_r($items);die();
        $this->set(compact('warehouses', 'items', 'user_warehouse_id'));
        $this->set('_serialize', ['makeDto']);
    }

    /**
     * View method
     *
     * @param string|null $id Make Dto id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $makeDto = $this->MakeDto->get($id, [
            'contain' => []
        ]);

        $this->set('makeDto', $makeDto);
        $this->set('_serialize', ['makeDto']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
//    public function add()
//    {
//        $makeDto = $this->MakeDto->newEntity();
//        if ($this->request->is('post')) {
//            $makeDto = $this->MakeDto->patchEntity($makeDto, $this->request->data);
//            if ($this->MakeDto->save($makeDto)) {
//                $this->Flash->success(__('The make dto has been saved.'));
//
//                return $this->redirect(['action' => 'index']);
//            } else {
//                $this->Flash->error(__('The make dto could not be saved. Please, try again.'));
//            }
//        }
//        $this->set(compact('makeDto'));
//        $this->set('_serialize', ['makeDto']);
//    }

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
            //   echo "<pre>";print_r($item_units);die();
            $this->response->body(json_encode($item_units));
            return $this->response;


        }
    }

    public function getItemUnitStockAmount()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $user = $this->Auth->user();
            $stocks=TableRegistry::get('stocks')->find('all')
                ->where(['warehouse_id'=>$user['warehouse_id'],'manufacture_unit_id'=>$data['unit_id'],'item_id'=>$data['item_id']])->first();
            $this->response->body($stocks->quantity);
            return $this->response;
        }

    }
}
