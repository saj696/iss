<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;

/**
 * Packages Controller
 *
 * @property \App\Model\Table\PackagesTable $Packages
 */
class PackagesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Packages.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        return $this->redirect(['action' => 'add']);
        $packages = $this->Packages->find('all', [
            'conditions' => ['Packages.status !=' => 99],
            'contain' => ['Warehouses', 'Items', 'Units']
        ]);
        $this->set('packages', $this->paginate($packages));
        $this->set('_serialize', ['packages']);
    }

    /**
     * View method
     *
     * @param string|null $id Package id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $package = $this->Packages->get($id, [
            'contain' => ['Warehouses', 'Items', 'Units']
        ]);
        $this->set('package', $package);
        $this->set('_serialize', ['package']);
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
        $package = "";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $this->loadModel('Stocks');
            $this->loadModel('StockLogs');
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($user, $time, $data) {
                foreach ($data['Packages'] as $packages) {
                    $existing = TableRegistry::get('stocks')->find('all', ['conditions' => ['warehouse_id' => $data['warehouse_id'],
                        'item_id' => $packages['item_id'],
                        'manufacture_unit_id' => $packages['manufacture_unit_id']
                    ]])->first();

                    if ($existing) {
                        $updateData['quantity'] = $existing['quantity'] + $packages['quantity'];
                        $updateData['approved_quantity'] = 0;
                        $stock = $this->Stocks->patchEntity($existing, $updateData);
                        if ($this->Stocks->save($stock)) {
                            $stock_logs = $this->StockLogs->newEntity();
                            $log['stock_id'] = $stock->id;
                            $log['warehouse_id'] = $data['warehouse_id'];
                            $log['quantity'] = $packages['quantity'];
                            $log['type'] = 7; //conversion
                            $log['created_by'] = $user['id'];
                            $log['item_id'] = $packages['item_id'];
                            $log['manufacture_unit_id'] = $packages['manufacture_unit_id'];
                            $log['created_date'] = $time;
                            $log['created_by'] = $user['id'];
                            $stock_logs = $this->StockLogs->PatchEntity($stock_logs, $log);
                            $this->StockLogs->save($stock_logs);
                        }

                    } else {
                        $stock = $this->Stocks->newEntity();
                        $data['warehouse_id'] = $data['warehouse_id'];
                        $data['item_id'] = $packages['item_id'];
                        $data['manufacture_unit_id'] = $packages['manufacture_unit_id'];
                        $data['quantity'] = $packages['quantity'];
                        $data['approved_quantity'] = 0;
                        $data['created_by'] = $user['id'];
                        $data['created_date'] = $time;
                        $stock = $this->Stocks->patchEntity($stock, $data);
                        if ($this->Stocks->save($stock)) {
                            $stock_logs = $this->StockLogs->newEntity();
                            $log['stock_id'] = $stock->id;
                            $log['warehouse_id'] = $data['warehouse_id'];
                            $log['item_id'] = $packages['item_id'];
                            $log['manufacture_unit_id'] = $packages['manufacture_unit_id'];
                            $log['quantity'] = $packages['quantity'];
                            $log['type'] = 7;
                            $log['created_by'] = $user['id'];
                            $log['created_date'] = $time;
                            $stock_logs = $this->StockLogs->PatchEntity($stock_logs, $log);
                            $this->StockLogs->save($stock_logs);
                        }
                    }

                }
                $this->Flash->success('Stock Updated');
                return $this->redirect(['controller' => 'Stocks', 'action' => 'index']);
            });
        }
        $this->loadModel('Warehouses');
        $this->loadModel('Units');
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        $items = SystemHelper::item_array();
        $units = $this->Units->find('list', ['keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);
        $this->set(compact('package', 'warehouses', 'items', 'units'));
        $this->set('_serialize', ['package']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Package id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */


    public function usedItems()
    {
        $user = $this->Auth->user();
        $time = time();
        $this->loadModel('Warehouses');
        $this->loadModel('Units');
        $this->loadModel('Stocks');
        $this->loadModel('StockLogs');
        $used_items = "";
        if ($this->request->is('post')) {

            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus) {
                    $input = $this->request->data;
                    $detailArray = $input['UsedItems'];
                    foreach ($detailArray as $detail) {
                        $existing = TableRegistry::get('stocks')->find('all', ['contain' => ['Items', 'Units'], 'conditions' => ['warehouse_id' => $input['warehouse_id'],
                            'item_id' => $detail['item_id'],
                            'manufacture_unit_id' => $detail['manufacture_unit_id']
                        ]])->first();
                        $stock_log_quantity = 0; //same amount changes in stock
                        //first check it's bulk or not
                        if ($existing['unit']['unit_size'] == 0) {
                            if ($existing['unit']['unit_type'] == 1 || $existing['unit']['unit_type'] == 3) {
                                $reduction = $detail['quantity'] * 1000;
                                $updateData['quantity'] = $existing['quantity'] - $reduction;
                                $stock_log_quantity = $reduction;
                            } else {
                                $updateData['quantity'] = $existing['quantity'] - $detail['quantity'];
                                $stock_log_quantity = $detail['quantity'];
                            }
                        } else {
                            if ($existing['unit']['unit_type'] == 1 || $existing['unit']['unit_type'] == 3) {
                                $factor = $detail['quantity'] * 1000;
                                $updateData['quantity'] = $existing['quantity'] - ($factor / $existing['unit']['unit_size']);

                                $stock_log_quantity = $factor / $existing['unit']['unit_size'];

                            } else {
                                $updateData['quantity'] = $existing['quantity'] - ($detail['quantity'] / $existing['unit']['unit_size']);
                                $stock_log_quantity = $detail['quantity'] / $existing['unit']['unit_size'];
                            }
                        }
                        $updateData['updated_by'] = $user['id'];
                        $updateData['updated_date'] = $time;
                        $stock = $this->Stocks->patchEntity($existing, $updateData);
                        $stockLogs = $this->StockLogs->newEntity();
                        $this->Stocks->save($stock);
                        $data['warehouse_id'] = $input['warehouse_id'];
                        $data['type'] = 8;//usage
                        $data['quantity'] = $stock_log_quantity;
                        $data['stock_id'] = $stock->id;
                        $data['item_id'] = $existing['item_id'];
                        $data['manufacture_unit_id'] = $existing['manufacture_unit_id'];
                        $data['created_by'] = $user['id'];
                        $data['created_date'] = $time;
                        $stockLogs = $this->StockLogs->patchEntity($stockLogs, $data);
                        $this->StockLogs->save($stockLogs);
                    }
                });
                $this->Flash->success('The Stock has been updated. Thank you!');
                return $this->redirect(['controller' => 'Stocks', 'action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('The Stock has not been updated. Please try again!');
                return $this->redirect(['controller' => 'Stocks', 'action' => 'index']);
            }
        }
        $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        $items = SystemHelper::item_array();
        $units = $this->Units->find('list', ['keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);
        $this->set(compact('used_items', 'warehouses', 'items', 'units'));
        $this->set('_serialize', ['used_items']);
    }

    public function stockQuantity()
    {
        $this->autoRender = false;
        $data = $this->request->data;
        $warehouse_id = $data['warehouse_id'];
        $item_id = $data['item_id'];
        $unit_id = $data['unit_id'];
        $quantity = TableRegistry::get('stocks')->find('all', ['contain' => ['Items', 'Units'], 'conditions' => ['warehouse_id' => $warehouse_id,
            'item_id' => $item_id, 'manufacture_unit_id' => $unit_id]])->first();

        if (!empty($quantity)) {
            $response_array = [
                'code' => 200,
                'unit_type' => $quantity['unit']['unit_type'],
                'unit_size' => $quantity['unit']['unit_size'],
                'result' => $quantity['quantity']
            ];
            $this->response->body(json_encode($response_array));
        } else {
            $response_array = [
                'code' => 404,
                'result' => "No Stock Found"
            ];
            $this->response->body(json_encode($response_array));
        }
    }

    public
    function edit($id = null)
    {
        die;
        $user = $this->Auth->user();
        $time = time();
        $package = $this->Packages->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $package = $this->Packages->patchEntity($package, $data);
            if ($this->Packages->save($package)) {
                $this->Flash->success('The package has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The package could not be saved. Please, try again.');
            }
        }
        $warehouses = $this->Packages->Warehouses->find('list', ['conditions' => ['status' => 1]]);
        $items = $this->Packages->Items->find('list', ['conditions' => ['status' => 1]]);
        $manufactureUnits = $this->Packages->Units->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('package', 'warehouses', 'items', 'manufactureUnits'));
        $this->set('_serialize', ['package']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Package id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public
    function delete($id = null)
    {

        $package = $this->Packages->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $package = $this->Packages->patchEntity($package, $data);
        if ($this->Packages->save($package)) {
            $this->Flash->success('The package has been deleted.');
        } else {
            $this->Flash->error('The package could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
