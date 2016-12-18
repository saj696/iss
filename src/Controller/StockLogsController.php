<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use stdClass;

/**
 * StockLogs Controller
 *
 * @property \App\Model\Table\StockLogsTable $StockLogs
 */
class StockLogsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'StockLogs.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $stockLogs = $this->StockLogs->find('all', [
                'contain' => ['Warehouses','Items','Units'],
            'conditions' => ['StockLogs.status !=' => 99]
        ]);
        $this->set('stockLogs', $this->paginate($stockLogs));
        $this->set('_serialize', ['stockLogs']);
    }

    /**
     * View method
     *
     * @param string|null $id Reduced Stock id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $stockLogs = $this->StockLogs->get($id, [
            'contain' => ['Warehouses', 'Items', 'Units']
        ]);
        $this->set('stockLogs', $stockLogs);
        $this->set('_serialize', ['stockLogs']);
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
        $stockLogs = $this->StockLogs->newEntity();
        if ($this->request->is('post')) {

            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus) {
                    $input = $this->request->data;
                    $this->loadModel('Stocks');
                    $detailArray = $input['details'];

                    foreach ($detailArray as $detail) {
                        $existing = TableRegistry::get('stocks')->find('all',
                            ['conditions' => ['warehouse_id' => $input['warehouse_id'],
                                'id' => $detail['stock_id']
                            ]])->first();
                        $updateData['quantity'] = $existing['quantity'] - $detail['quantity'];
                        $stock = $this->Stocks->patchEntity($existing, $updateData);
                        $stockLogs = $this->StockLogs->newEntity();
                        $data['warehouse_id'] = $input['warehouse_id'];
                        $data['stock_id'] = $detail['stock_id'];
                        $data['type'] = $detail['type'];
                        $data['quantity'] = $detail['quantity'];
                        $data['item_id'] = $existing['item_id'];
                        $data['manufacture_unit_id'] = $existing['manufacture_unit_id'];
                        $data['created_by'] = $user['id'];
                        $data['created_date'] = $time;
                        $stockLogs = $this->StockLogs->patchEntity($stockLogs, $data);
                        $this->StockLogs->save($stockLogs);
                        $this->Stocks->save($stock);
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
                return $this->redirect(['action' => 'index']);
            }
        }
        $warehouses = $this->StockLogs->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        $this->set(compact('stockLogs', 'warehouses', 'items'));
        $this->set('_serialize', ['stockLogs']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Reduced Stock id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $stockLogs = $this->StockLogs->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $stockLogs = $this->StockLogs->patchEntity($stockLogs, $data);
            if ($this->StockLogs->save($stockLogs)) {
                $this->Flash->success('The reduced stock has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The reduced stock could not be saved. Please, try again.');
            }
        }
        $warehouses = $this->StockLogs->Warehouses->find('list', ['conditions' => ['status' => 1]]);
      //  $items = $this->StockLogs->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('stockLogs', 'warehouses', 'items'));
        $this->set('_serialize', ['stockLogs']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Reduced Stock id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $stockLogs = $this->StockLogs->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $stockLogs = $this->StockLogs->patchEntity($stockLogs, $data);
        if ($this->StockLogs->save($stockLogs)) {
            $this->Flash->success('The reduced stock has been deleted.');
        } else {
            $this->Flash->error('The reduced stock could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    //plz update by new helper..pending
    public function ajax()
    {
        $data = $this->request->data;
        $store = $data['store'];
        $this->loadModel('Stocks');
        $items_units = $this->Stocks->find('all')->contain(['Items', 'Units'])->where(['Stocks.warehouse_id' => $store, 'Stocks.status' => 1])->hydrate(false)->toArray();
        // $items_units = $this->Stocks->find('all')->contain(['Items', 'Units'])->where(['stocks.warehouse_id' => $store, 'stocks.status' => 1])->hydrate(false)->toArray();
        $dropArray = [];
        foreach ($items_units as $item_unit):
            $dropArray[$item_unit['id']] = $item_unit['item']['name'] . '-' . $item_unit['unit']['unit_display_name'];
        endforeach;
        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function existing()
    {
        $data = $this->request->data;

        $id = $data['stock_id'];
        $warehouse_id = $data['store_id'];
        $item = TableRegistry::get('stocks')->find('all', ['conditions' => ['warehouse_id' => $warehouse_id, 'id' => $id]])->first()->toArray();

        $this->response->body($item['quantity']);
        $this->autoRender = false;
        return $this->response;
    }
}
