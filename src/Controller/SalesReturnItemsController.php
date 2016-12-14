<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use App\View\Helper\SystemHelper;
use Cake\Datasource\ConnectionManager;

/**
 * SalesReturnItems Controller
 *
 * @property \App\Model\Table\SalesReturnItemsTable $SalesReturnItems
 */
class SalesReturnItemsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            // 'SalesReturn.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $salesReturns = TableRegistry::get('sales_returns')->find('all', [
            'conditions' => ['sales_returns.status !=' => 99],
            'contain' => ['Customers']
        ]);
        $this->set('salesReturns', $this->paginate($salesReturns));
        $this->set('_serialize', ['salesReturns']);
    }

    /**
     * View method
     *
     * @param string|null $id Sales Return Item id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $salesReturnItem = $this->SalesReturnItems->get($id, [
            'contain' => ['Items', 'Units', 'SalesReturns']
        ]);
        $this->set('salesReturnItem', $salesReturnItem);
        $this->set('_serialize', ['salesReturnItem']);
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
        $salesReturnItems = "";
        if ($this->request->is('post')) {
            $data = $this->request->data;

            $salesReturn = $this->SalesReturnItems->newEntity();
            $this->loadModel('AdministrativeUnits');
            $this->loadModel('SalesReturns');
            $this->loadModel('Stocks');

            $get_parent_global_id = $this->AdministrativeUnits->find('all', ['fields' => ['global_id', 'id'],
                'conditions' => ['id' => $data['SalesReturn']['parent_unit']]])->hydrate(false)->first();
            $salesReturnData['parent_global_id'] = $get_parent_global_id['global_id'];
            $salesReturnData['customer_id'] = $data['SalesReturn']['customer'];
            $salesReturnData['grand_total'] = $data['SalesReturn']['grand_total'];
            $salesReturnData['created_date'] = $time;
            $salesReturnData['created_by'] = $user['id'];
            $salesReturn = $this->SalesReturns->patchEntity($salesReturn, $salesReturnData);
            $conn = ConnectionManager::get('default');
            $stock_table = TableRegistry::get('stocks');
            $conn->transactional(function () use ($user, $time, $salesReturn, $data, $stock_table) {
                if ($this->SalesReturns->save($salesReturn)) {
                    $this->loadModel('Stocks');
                    foreach ($data['SalesReturnItems'] as $sales_return_items):
                        $sales_return_item_entity = $this->SalesReturnItems->newEntity();
                        $sales_return_item_data['item_id'] = $sales_return_items['item_id'];
                        $sales_return_item_data['sales_return_id'] = $salesReturn->id;
                        $sales_return_item_data['manufacture_unit_id'] = $sales_return_items['manufacture_unit_id'];
                        $sales_return_item_data['quantity'] = $sales_return_items['quantity'];
                        $sales_return_item_data['unit_price'] = $sales_return_items['unit_price'];
                        $sales_return_item_data['net_total'] = $sales_return_items['net_total'];
                        $sales_return_item_data['expire_date'] = strtotime($sales_return_items['expire_date']);
                        $sales_return_item_data['created_date'] = $time;
                        $sales_return_item_data['created_by'] = $user['id'];
                        $sales_return_item_entity = $this->SalesReturnItems->patchEntity($sales_return_item_entity, $sales_return_item_data);

                        $existing = $stock_table->find('all', ['contain' => ['Items', 'Units'], 'conditions' => ['warehouse_id' => $data['SalesReturn']['warehouse_id'],
                            'item_id' => $sales_return_items['item_id'],
                            'manufacture_unit_id' => $sales_return_items['manufacture_unit_id']
                        ]])->first();
                        $updateData['updated_by'] = $user['id'];
                        $updateData['updated_date'] = $time;
                        $updateData['quantity'] = $existing['quantity'] + $sales_return_items['net_total'];
                        $stock = $this->Stocks->patchEntity($existing, $updateData);

                        if ($this->Stocks->save($stock)) {

                        } else {
                            pr($stock->errors());
                            die;
                        }

                        if ($this->SalesReturnItems->save($sales_return_item_entity)) {
                        } else {
                            pr($sales_return_item_entity->errors());
                            die;
                        }
                    endforeach;
                    $this->Flash->success('Sales Return Items Saved.');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('The sales return item could not be saved. Please, try again.');
                }
            });
        }

        $this->loadModel('Units');
        $units = $this->Units->find('list', [
            'keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);
        $this->loadModel('AdministrativeLevels');
        $parent_info = $this->AdministrativeLevels->find('all', ['fields' => ['level_name', 'level_no'], 'conditions' => ['status' => 1]])->toArray();
        $parentLevels = [];
        foreach ($parent_info as $parent_info) {
            $parentLevels[$parent_info['level_no']] = $parent_info['level_name'];
        }
        $this->loadModel('Warehouses');

        if (!empty($user['depot_id'])) {
            $depots = TableRegistry::get('depots')->find('all')->where(['id' => $user['depot_id']])->select('warehouses', 'id')->first();
            $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id IN' => json_decode($depots['warehouses'])]]);
            $show_item_for_depot_user = 1;
        } else {
            $show_item_for_depot_user = 0;
            $items = SystemHelper::item_array($user['warehouse_id']);
            $warehouses = $this->Warehouses->find('list', ['conditions' => ['status' => 1, 'id' => $user['warehouse_id']]]);
        }

        $this->set(compact('salesReturnItems', 'items', 'units', 'salesReturns', 'parentLevels', 'warehouses', 'show_item_for_depot_user'));
        $this->set('_serialize', ['salesReturnItems']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Sales Return Item id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function ajax($param)
    {
        $this->autoRender = false;
        if ($param == "units"):
            $data = $this->request->data;
            $level = $data['level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields' => ['id', 'unit_name', 'global_id']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach ($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;

        elseif ($param == "customers"):
            $data = $this->request->data;
            $unit = $data['unit'];
            $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields' => ['id', 'name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach ($customers as $customer):
                $dropArray[$customer['id']] = $customer['name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;
        elseif ($param == "depotItems"):
            $data = $this->request->data;
            $warehouse_id = $data['warehouse_id'];
            $dropArray = SystemHelper::item_array($warehouse_id);
            $this->response->body(json_encode($dropArray));
            return $this->response;
            $data = $this->request->data;
        elseif ($param == "isInStock"):
            $data = $this->request->data;
            $item_id = $data['item_id'];
            $unit_id = $data['unit_id'];
            $warehouse_id = $data['warehouse_id'];
            $stock = TableRegistry::get('stocks')->find('all', ['conditions' => ['warehouse_id' => $warehouse_id, 'item_id' => $item_id,
                'manufacture_unit_id' => $unit_id
            ]])->first();
            $result = [];
            if (!empty($stock)) {
                $result['code'] = 200;
                $result['quantity'] = $stock['quantity'];
            } else {
                $result['code'] = 404;
            }
            $this->autoRender = false;
            $this->response->body(json_encode($result));
        endif;
    }

    public function edit($id = null)
    {
        die;
        $user = $this->Auth->user();
        $time = time();
        $salesReturnItem = $this->SalesReturnItems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $salesReturnItem = $this->SalesReturnItems->patchEntity($salesReturnItem, $data);
            if ($this->SalesReturnItems->save($salesReturnItem)) {
                $this->Flash->success('The sales return item has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The sales return item could not be saved. Please, try again.');
            }
        }
        $items = $this->SalesReturnItems->Items->find('list', ['conditions' => ['status' => 1]]);
        $manufactureUnits = $this->SalesReturnItems->ManufactureUnits->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('salesReturnItem', 'items', 'manufactureUnits', 'salesReturns'));
        $this->set('_serialize', ['salesReturnItem']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Sales Return Item id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $salesReturnItem = $this->SalesReturnItems->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $salesReturnItem = $this->SalesReturnItems->patchEntity($salesReturnItem, $data);
        if ($this->SalesReturnItems->save($salesReturnItem)) {
            $this->Flash->success('The sales return item has been deleted.');
        } else {
            $this->Flash->error('The sales return item could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
