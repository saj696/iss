<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use App\View\Helper\SystemHelper;

/**
 * Stocks Controller
 *
 * @property \App\Model\Table\StocksTable $Stocks
 */
class StocksController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Stocks.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {

        ///pr(SystemHelper::get_item_unit_array());die;

        $user = $this->Auth->user();
        $stocks = $this->Stocks->find('all', [
            'conditions' => ['Stocks.status !=' => 99, 'Stocks.warehouse_id' => $user['warehouse_id']],
            'contain' => ['Warehouses', 'Items', 'Units']
        ]);

        //debug($stocks->toArray()); die;
        $this->set('stocks', $this->paginate($stocks));
        $this->set('_serialize', ['stocks']);
    }

    /**
     * View method
     *
     * @param string|null $id Stock id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $stock = $this->Stocks->get($id, [
            'contain' => ['Warehouses', 'Items', 'Units']
        ]);
        $this->set('stock', $stock);
        $this->set('_serialize', ['stock']);
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
        $stock = $this->Stocks->newEntity();
        $this->loadModel('StockLogs');
        if ($this->request->is('post')) {

            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus) {
                    $input = $this->request->data;
                    //pr($input);die;
                    $detailArray = $input['details'];

                    foreach ($detailArray as $detail) {
                        $existing = TableRegistry::get('stocks')->find('all', ['conditions' => ['warehouse_id' => $input['warehouse_id'],
                            'item_id' => $detail['item_id'],
                            'manufacture_unit_id' => $detail['manufacture_unit_id']
                        ]])->first();

                        if ($existing) {
                            $updateData['quantity'] = $existing['quantity'] + $detail['quantity'];
                            $updateData['approved_quantity'] = 0;
                            //  $updateData['approved_quantity'] = $existing['approved_quantity'] + $detail['approved_quantity'];
                            $stock = $this->Stocks->patchEntity($existing, $updateData);
                            if ($this->Stocks->save($stock)) {
                                $stock_logs = $this->StockLogs->newEntity();
                                $log['stock_id'] = $stock->id;
                                $log['warehouse_id'] = $input['warehouse_id'];
                                $log['quantity'] = $updateData['quantity'];
                                $log['type'] = $detail['type'];
                                $log['created_by'] = $user['id'];
                                $log['item_id'] = $detail['item_id'];
                                $log['manufacture_unit_id'] = $detail['manufacture_unit_id'];
                                $log['created_date'] = $time;
                                $stock_logs = $this->StockLogs->PatchEntity($stock_logs, $log);
                                $this->StockLogs->save($stock_logs);
                            }

                        } else {
                            $stock = $this->Stocks->newEntity();
                            $data['warehouse_id'] = $input['warehouse_id'];
                            $data['item_id'] = $detail['item_id'];
                            $data['manufacture_unit_id'] = $detail['manufacture_unit_id'];
                            $data['quantity'] = $detail['quantity'];
                            $data['approved_quantity'] = 0;
                            $data['created_by'] = $user['id'];
                            $data['created_date'] = $time;
                            $stock = $this->Stocks->patchEntity($stock, $data);
                            if ($this->Stocks->save($stock)) {
                                $stock_logs = $this->StockLogs->newEntity();
                                $log['stock_id'] = $stock->id;
                                $log['warehouse_id'] = $input['warehouse_id'];
                                $log['item_id'] = $detail['item_id'];
                                $log['manufacture_unit_id'] = $detail['manufacture_unit_id'];
                                $log['quantity'] = $detail['quantity'];
                                $log['type'] = $detail['type'];
                                $log['created_by'] = $user['id'];
                                $log['created_date'] = $time;
                                $stock_logs = $this->StockLogs->PatchEntity($stock_logs, $log);
                                $this->StockLogs->save($stock_logs);
                            }
                        }
                    }
                });

                $this->Flash->success('The Stock has been updated. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                $this->Flash->error('The Stock has not been updated. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('Items');
        $this->loadModel('Units');
        $this->loadModel('Warehouses');
        $this->loadModel('WarehouseItems');
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        $warehouse_item_all = $this->WarehouseItems->find('all', ['contain' => ['Items'], 'conditions' => ['WarehouseItems.status' => 1, 'Items.status' => 1]]);
        $items = [];
        foreach ($warehouse_item_all as $warehouse_item):
            $item_name = $warehouse_item['use_alias'] == 1 ? $warehouse_item['item']['alias'] : $warehouse_item['item']['name'];
            $items[$warehouse_item['item']['id']] = $item_name;
        endforeach;

        $units = $this->Units->find('list', [
            'keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);

        $this->set(compact('stock', 'warehouses', 'items', 'units'));
        $this->set('_serialize', ['stock']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Stock id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        die;
        $user = $this->Auth->user();
        $time = time();
        $stock = $this->Stocks->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $stock = $this->Stocks->patchEntity($stock, $data);
            if ($this->Stocks->save($stock)) {
                $this->Flash->success('The stock has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The stock could not be saved. Please, try again.');
            }
        }
        //  $this->loadModel('Items');
        // $this->loadModel('Units');
        $this->loadModel('Warehouses');
        $this->loadModel('WarehouseItems');
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        $warehouse_item_all = $this->WarehouseItems->find('all', ['contain' => ['Items'], 'conditions' => ['WarehouseItems.status' => 1, 'Items.status' => 1]]);
        $items = [];
        foreach ($warehouse_item_all as $warehouse_item):
            $item_name = $warehouse_item['use_alias'] == 1 ? $warehouse_item['item']['alias'] : $warehouse_item['item']['name'];
            $items[$warehouse_item['item']['id']] = $item_name;
        endforeach;

        $units = $this->Stocks->Units->find('list', [
            'keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);

        $this->set(compact('stock', 'warehouses', 'items', 'units'));
        $this->set('_serialize', ['stock']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Stock id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $stock = $this->Stocks->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $stock = $this->Stocks->patchEntity($stock, $data);
        if ($this->Stocks->save($stock)) {
            $this->Flash->success('The stock has been deleted.');
        } else {
            $this->Flash->error('The stock could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
