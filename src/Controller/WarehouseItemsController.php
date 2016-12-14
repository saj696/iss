<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Datasource\ConnectionManager;

/**
 * WarehouseItems Controller
 *
 * @property \App\Model\Table\WarehouseItemsTable $WarehouseItems
 */
class WarehouseItemsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'WarehouseItems.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $warehouseItems = $this->WarehouseItems->find('all', [
            'conditions' => ['WarehouseItems.status !=' => 99],
            'contain' => ['Warehouses', 'Items']
        ]);
        $this->set('warehouseItems', $this->paginate($warehouseItems));
        $this->set('_serialize', ['warehouseItems']);
    }

    /**
     * View method
     *
     * @param string|null $id Warehouse Item id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $warehouseItem = $this->WarehouseItems->get($id, [
            'contain' => ['Warehouses', 'Items']
        ]);
        $this->set('warehouseItem', $warehouseItem);
        $this->set('_serialize', ['warehouseItem']);
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
        $warehouseItem = "";
        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus) {
                    $data = $this->request->data;
                    $data['status'] = 1;
                    $detailArray = $data['WarehouseItems'];

                    foreach ($detailArray as $detail) {
                        $warehouseItem = $this->WarehouseItems->newEntity();
                        $data['item_id'] = $detail['item_id'];
                        $data['warehouse_id'] = $detail['warehouse_id'];
                        $data['use_alias'] = $detail['use_alias'];
                        $data['created_by'] = $user['id'];
                        $data['created_date'] = $time;
                        $warehouseItem = $this->WarehouseItems->patchEntity($warehouseItem, $data);
                        if ($this->WarehouseItems->save($warehouseItem)) {
                            $this->Flash->success('Warehouse items have been saved.');
                        } else {
                            $this->Flash->error('Warehouse items can not be saved.');
                        }
                    }
                });


                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                die();
                $this->Flash->success("Warehouse items haven't  saved . ");
                return $this->redirect(['action' => 'index']);
            }
        }
        $warehouses = $this->WarehouseItems->Warehouses->find('list', ['conditions' => ['status' => 1]]);
        $items = $this->WarehouseItems->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('warehouseItem', 'warehouses', 'items'));
        $this->set('_serialize', ['warehouseItem']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Warehouse Item id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $warehouseItem = $this->WarehouseItems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $warehouseItem = $this->WarehouseItems->patchEntity($warehouseItem, $data);
            if ($this->WarehouseItems->save($warehouseItem)) {
                $this->Flash->success('The warehouse item has been saved . ');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The warehouse item could not be saved . Please, try again . ');
            }
        }
        $warehouses = $this->WarehouseItems->Warehouses->find('list', ['conditions' => ['status' => 1]]);
        $items = $this->WarehouseItems->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('warehouseItem', 'warehouses', 'items'));
        $this->set('_serialize', ['warehouseItem']);
    }

    public function itemAvailable()
    {
        $this->autoRender = false;
        $item_id = $this->request->data['item_id'];
        $warehouse_id = $this->request->data['warehouse_id'];
        $response = $this->WarehouseItems->find('all', ['conditions' => ['status' => 1, 'warehouse_id' => $warehouse_id, 'item_id' => $item_id]])->count();
        $result = $response > 0 ? 0 : 1;
        echo $result;
        die;
    }

    /**
     * Delete method
     *
     * @param string|null $id Warehouse Item id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $warehouseItem = $this->WarehouseItems->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $warehouseItem = $this->WarehouseItems->patchEntity($warehouseItem, $data);
        if ($this->WarehouseItems->save($warehouseItem)) {
            $this->Flash->success('The warehouse item has been deleted . ');
        } else {
            $this->Flash->error('The warehouse item could not be deleted . Please, try again . ');
        }
        return $this->redirect(['action' => 'index']);
    }
}
