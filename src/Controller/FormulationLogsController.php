<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * FormulationLogs Controller
 *
 * @property \App\Model\Table\FormulationLogsTable $FormulationLogs
 * @property bool|object Common
 */
class FormulationLogsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'FormulationLogs.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $formulationLogs = $this->FormulationLogs->find('all', [
            'conditions' => ['FormulationLogs.status !=' => 99]
        ]);
        $this->set('formulationLogs', $this->paginate($formulationLogs));
        $this->set('_serialize', ['formulationLogs']);
    }

    /**
     * View method
     *
     * @param string|null $id Formulation Log id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $formulationLog = $this->FormulationLogs->get($id, [
            'contain' => []
        ]);
        $this->set('formulationLog', $formulationLog);
        $this->set('_serialize', ['formulationLog']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->loadModel('FormulationLogs');
        $this->loadModel('StockLogs');
        $this->loadModel('ItemUnits');
        $this->loadModel('Stocks');
        $user = $this->Auth->user();
        $time = time();
        $stockLog = $this->StockLogs->newEntity();
        if ($this->request->is('post')) {

            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($stockLog, $user, $time, &$saveStatus)
                {
                    $data = $this->request->data;
                    $data['create_by'] = $user['id'];
                    $data['create_date'] = $time;

//            Add more operations
                    foreach($data['details'] as $stockID => $stockDetails):
                        $itemUnit = $this->ItemUnits->find('all',['conditions' => ['id' => $stockDetails['item_unit']], 'fields'=>['item_id','manufacture_unit_id'] ])->hydrate(false)->first();
                        $stockID = $this->Stocks->find('all',['conditions' => ['item_id' => $itemUnit['item_id'], 'manufacture_unit_id' => $itemUnit['manufacture_unit_id']], 'fields'=>['id'] ])->hydrate(false)->first();
                        $stockRow = $this->Stocks->get($stockID);
                        $stockRow->quantity = ($stockRow->quantity) - $stockDetails['amount'];
                        $this->Stocks->save($stockRow);

//            Stock log data insert
                        $stockLog = $this->StockLogs->newEntity();
                        $stockLogData['warehouse_id'] = $data['warehouse_id'];
                        $stockLogData['manufacture_unit_id'] = $itemUnit['manufacture_unit_id'];
                        $stockLogData['item_id'] = $itemUnit['item_id'];
                        $stockLogData['stock_id'] = $stockID['id'];
                        $stockLogData['type'] = 9;
                        $stockLogData['quantity'] = $stockDetails['amount'];
                        $stockLogData['status'] = 1;
                        $stockLogData['created_by'] = $user['id'];
                        $stockLogData['created_date'] = $time;
                        $stockLog = $this->StockLogs->patchEntity($stockLog, $stockLogData);
                        $this->StockLogs->save($stockLog);
                    endforeach;

//            Add item unit for formulate product
                    $stockID = $this->Stocks->find('all',['conditions' => ['item_id' => $data['item_id'], 'manufacture_unit_id' => $data['manufacture_unit_id']], 'fields'=>['id'] ])->hydrate(false)->first();
                    if($stockID):
                        $stockRow = $this->Stocks->get($stockID);
                        $stockRow->quantity = ($stockRow->quantity) + ($data['output_result'] + $data['output_gain']);
                        $this->Stocks->save($stockRow);
                    else:
//                if not present in the stock table
                        $stockAdd = $this->Stocks->newEntity();
                        $stockAddData['warehouse_id'] = $data['warehouse_id'];
                        $stockAddData['item_id'] = $data['item_id'];
                        $stockAddData['manufacture_unit_id'] = $data['manufacture_unit_id'];
                        $stockAddData['quantity'] = $data['output_result'] + $data['output_gain'];
                        $stockAddData['approved_quantity'] = 0;
                        $stockAddData['status'] = $data['warehouse_id'];
                        $stockAddData['status'] = 1;
                        $stockAddData['created_by'] = $user['id'];
                        $stockAddData['created_date'] = $time;
                        $stockAdd = $this->Stocks->patchEntity($stockAdd, $stockAddData);
                        $this->Stocks->save($stockAdd);
                    endif;

//                    add formulate product in stock log table
                    $stockIDForLog = $this->Stocks->find('all',['conditions' => ['item_id' => $data['item_id'], 'manufacture_unit_id' => $data['manufacture_unit_id']], 'fields'=>['id'] ])->hydrate(false)->first();
                    $stockLog = $this->StockLogs->newEntity();
                    $stockLogData['warehouse_id'] = $data['warehouse_id'];
                    $stockLogData['manufacture_unit_id'] = $data['manufacture_unit_id'];
                    $stockLogData['item_id'] = $data['item_id'];
                    $stockLogData['stock_id'] = $stockIDForLog['id'];
                    $stockLogData['type'] = 10;
                    $stockLogData['quantity'] = $data['output_result'];
                    $stockLogData['status'] = 1;
                    $stockLogData['created_by'] = $user['id'];
                    $stockLogData['created_date'] = $time;
                    $stockLog = $this->StockLogs->patchEntity($stockLog, $stockLogData);
                    $this->StockLogs->save($stockLog);

                    if($data['output_gain']):
                        $stockLog = $this->StockLogs->newEntity();
                        $stockLogData['type'] = 5;
                        $stockLogData['quantity'] = $data['output_gain'];
                        $stockLog = $this->StockLogs->patchEntity($stockLog, $stockLogData);
                        $this->StockLogs->save($stockLog);
                    endif;


//                        Formulation log table data insert
                    $inputName =  $this->Common->specific_item_name_resolver($data['warehouse_id'],$data['Item']);
                    $formulationLog = $this->FormulationLogs->newEntity();
                    $formulationLogData['input_name'] = $inputName['name'];
                    $formulationLogData['output_name'] = $data['item_name'];
                    $formulationLogData['status'] = 1;
                    $formulationLogData['output_gain'] = $data['output_gain'];
                    $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $formulationLogData);
                    $this->FormulationLogs->save($formulationLog);
                });

                $this->Flash->success('Formulation Generation Successful');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Formulation Generation Unsuccessful');
                return $this->redirect(['action' => 'index']);
            }

        }

//        Show Warehouse
        $this->loadModel('Warehouses');
        $warehouseNames = $this->Warehouses->find('list', ['conditions' => ['id' => $user['warehouse_id']], 'fields' => ['id', 'name']])->hydrate(false)->toArray();

        $this->set(compact('stockLog', 'warehouseNames'));
        $this->set('_serialize', ['formulationLog']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Formulation Log id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $formulationLog = $this->FormulationLogs->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $data);
            if ($this->FormulationLogs->save($formulationLog)) {
                $this->Flash->success('The formulation log has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The formulation log could not be saved. Please, try again.');
            }
        }
        $this->set(compact('formulationLog'));
        $this->set('_serialize', ['formulationLog']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Formulation Log id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $formulationLog = $this->FormulationLogs->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $data);
        if ($this->FormulationLogs->save($formulationLog)) {
            $this->Flash->success('The formulation log has been deleted.');
        } else {
            $this->Flash->error('The formulation log could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }


// Warehouse trigger
    public function wareHouseTrigger()
    {
        $data = $this->request->data;
        $wareHouseData = $data['warehouse'];
        $itemName = $this->Common->item_name_resolver($wareHouseData);
        $this->response->body(json_encode($itemName));
        return $this->response;

    }


// Check stock quantity
    public function stock()
    {
        $data = $this->request->data;
        $itemUnit = $data['itemUnit'];

//        Load Item Unit Model
        $this->loadModel('ItemUnits');
        $itemUnitData = $this->ItemUnits->get($itemUnit);

        $this->loadModel('Stocks');
        $quantity = $this->Stocks->find('all', ['conditions' => ['item_id' => $itemUnitData['item_id'], 'manufacture_unit_id' => $itemUnitData['manufacture_unit_id']], 'fields' => 'quantity'])->hydrate(false)->first();

        $this->loadModel('Units');
        $unitType = $this->Units->find('all', ['conditions' => ['id' => $itemUnitData['manufacture_unit_id']], 'fields' => ['unit_type', 'converted_quantity']])->hydrate(false)->first();
        $compact = [
            'unitType' => $unitType['unit_type'],
            'convertedQuantity' => $unitType['converted_quantity'],
            'quantity' => $quantity['quantity']
        ];
        $this->response->body(json_encode($compact));
        return $this->response;
    }

//    Find item unit based on item
    public function item()
    {
        $this->autoRender = false;
        $data = $this->request->data;
        $item = $data['item'];

        $item_unit_table = TableRegistry::get('item_units');
//        $itemID = $item_unit_table->find('all')->where(['id' => $item])->first();

        // pr($result->toArray());die;
        $result = $item_unit_table->find('all')->contain(['Items', 'Units'])->where([
            'item_units.item_id' => $item,
            'item_units.status' => 1,
            'Units.status' => 1
        ])->hydrate(false);

        $unitBsaedItem = [];
        foreach ($result as $key => $value):
            $unitBsaedItem[$value['id']] = SystemHelper::getItemAlias($value['item']['id']) . '--' . $value['unit']['unit_display_name'];
        endforeach;

        $this->response->body(json_encode($unitBsaedItem));
        return $this->response;
    }

//    Find out the total final result
    public function outputGeneration()
    {
        $user = $this->Auth->user();
        $wareHouseID = $user['warehouse_id'];
        $data = $this->request->data;
        $itemVal = $data['itemVal'];
        $totalAmount = $data['totalAmount'];

//      output item id from production rule table
        $resultInfo = TableRegistry::get('production_rules')->find('all')->where(['input_item_id' =>$itemVal ])->first();

        if(empty($resultInfo['output_item_id'])){
            $output = 0;
            $this->response->body(json_encode($output));
            return $this->response;
        }
        else{
            $bulkResult = TableRegistry::get('units')->find('all')->where(['id' =>$resultInfo['output_unit_id'] ])->first();
            $outputName = $this->Common->specific_item_name_resolver($wareHouseID, $resultInfo['output_item_id']);
            $result = ((float)($resultInfo['output_quantity']) * (float)$totalAmount) / (float)($resultInfo['input_quantity']);
            $output = [
                'itemId' => $outputName['id'],
                'itemName' => $outputName['name'],
                'bulkid' => $bulkResult['id'],
                'bulkName' => $bulkResult['unit_display_name'],
                'resultName' => round($result,4),
            ];
            if($result):
                $this->response->body(json_encode($output));
                return $this->response;
            endif;
        }
    }
}
