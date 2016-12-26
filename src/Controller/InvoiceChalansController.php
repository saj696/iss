<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use Exception;

/**
 * InvoiceChalans Controller
 *
 * @property \App\Model\Table\InvoiceChalansTable $InvoiceChalans
 */
class InvoiceChalansController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'PoEvents.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = $this->Auth->user();
        $this->loadModel('PoEvents');
        $invoices = $this->PoEvents->find('all', [
            'conditions' => ['PoEvents.status !=' => 99, 'recipient_id'=>$user['id'], 'is_action_taken'=>0, 'event_type'=>array_flip(Configure::read('po_event_types'))['make_chalan']],
            'contain'=>['Invoices'=>['InvoicedProducts', 'Customers']]
        ]);

        $this->set('invoices', $this->paginate($invoices));
        $this->set('_serialize', ['invoices']);
    }

    /**
     * View method
     *
     * @param string|null $id Invoice Chalan id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $invoiceChalan = $this->InvoiceChalans->get($id, [
            'contain' => []
        ]);
        $this->set('invoiceChalan', $invoiceChalan);
        $this->set('_serialize', ['invoiceChalan']);
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
        $this->loadModel('InvoiceChalans');
        $this->loadModel('InvoiceChalanDetails');
        $this->loadModel('Depots');
        $this->loadModel('Stocks');
        $this->loadModel('PoEvents');
        $this->loadModel('Users');
        $this->loadModel('ItemUnits');

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($user, $time, &$saveStatus)
            {
                if ($this->request->is('post')) {
                    $data = $this->request->data;
                    $invoiceIds = $data['invoiceIds'];
                    $chalan_no = $data['chalan_no'];
                    $detail = $data['detail'];

                    if(isset($_POST['send'])) {
                        $invoiceChalan = $this->InvoiceChalans->newEntity();
                        $invoiceChalanData['reference_invoices'] = json_encode($invoiceIds);
                        $invoiceChalanData['chalan_no'] = $chalan_no;
                        $invoiceChalanData['chalan_status'] = array_flip(Configure::read('invoice_chalan_status'))['delivered'];
                        $invoiceChalanData['created_by'] = $user['id'];
                        $invoiceChalanData['created_date'] = $time;
                        $invoiceChalan = $this->InvoiceChalans->patchEntity($invoiceChalan, $invoiceChalanData);
                        $chalanResult = $this->InvoiceChalans->save($invoiceChalan);
                        // Chalan detail delivery
                        foreach($detail as $item_unit_id=>$quantity){
                            $chalanDetail = $this->InvoiceChalanDetails->newEntity();
                            $itemUnitInfo = $this->ItemUnits->get($item_unit_id);
                            $chalanDetailData['invoice_chalan_id'] = $chalanResult['id'];
                            $chalanDetailData['item_unit_id'] = $item_unit_id;
                            $chalanDetailData['item_id'] = $itemUnitInfo['item_id'];
                            $chalanDetailData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                            $chalanDetailData['quantity'] = $quantity;
                            $chalanDetail = $this->InvoiceChalanDetails->patchEntity($chalanDetail, $chalanDetailData);
                            $this->InvoiceChalanDetails->save($chalanDetail);
                        }
                        // Invoice delivery status change
                        foreach($invoiceIds as $invoiceId){
                            $invoice = TableRegistry::get('invoices');
                            $query = $invoice->query();
                            $query->update()->set(['delivery_status' => array_flip(Configure::read('invoice_delivery_status'))['delivered']])->where(['id' => $invoiceId])->execute();
                        }
                        // Warehouse Stock reduce
                        $depotInfo = $this->Depots->get($user['depot_id']);
                        $warehouses = json_decode($depotInfo['warehouses'], true);
                        $warehouse_id = $warehouses[0]; // We are supporting single warehouse in a depot now.
                        foreach($detail as $item_unit_id=>$quantity) {
                            $itemUnitInfo = $this->ItemUnits->get($item_unit_id);
                            $stockInfo = $this->Stocks->find('all', ['conditions'=>['status !='=>99, 'warehouse_id'=>$warehouse_id, 'item_id'=>$itemUnitInfo['item_id'], 'manufacture_unit_id'=>$itemUnitInfo['manufacture_unit_id']]])->first();

                            if($stockInfo && ($stockInfo->quantity > $quantity)) {
                                $newStockQuantity = $stockInfo->quantity - $quantity;
                                $stock = TableRegistry::get('stocks');
                                $query = $stock->query();
                                $query->update()->set(['quantity' => $newStockQuantity])->where(['id' => $stockInfo->id])->execute();
                            } else {
                                $this->Flash->error('Stock is not enough, delivery not possible. Please try again!');
                                throw new Exception('error');
                            }
                        }
                    } elseif(isset($_POST['forward'])) {
                        $invoiceChalan = $this->InvoiceChalans->newEntity();
                        $invoiceChalanData['reference_invoices'] = json_encode($invoiceIds);
                        $invoiceChalanData['chalan_no'] = $chalan_no;
                        $invoiceChalanData['chalan_status'] = array_flip(Configure::read('invoice_chalan_status'))['forwarded'];
                        $invoiceChalanData['created_by'] = $user['id'];
                        $invoiceChalanData['created_date'] = $time;
                        $invoiceChalan = $this->InvoiceChalans->patchEntity($invoiceChalan, $invoiceChalanData);
                        $chalanResult = $this->InvoiceChalans->save($invoiceChalan);
                        // Chalan detail delivery
                        foreach($detail as $item_unit_id=>$quantity){
                            $chalanDetail = $this->InvoiceChalanDetails->newEntity();
                            $itemUnitInfo = $this->ItemUnits->get($item_unit_id);
                            $chalanDetailData['invoice_chalan_id'] = $chalanResult['id'];
                            $chalanDetailData['item_unit_id'] = $item_unit_id;
                            $chalanDetailData['item_id'] = $itemUnitInfo['item_id'];
                            $chalanDetailData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                            $chalanDetailData['quantity'] = $quantity;
                            $chalanDetail = $this->InvoiceChalanDetails->patchEntity($chalanDetail, $chalanDetailData);
                            $this->InvoiceChalanDetails->save($chalanDetail);
                        }

                        // Forward to warehouse in charge
                        $depotInfo = $this->Depots->get($user['depot_id']);
                        $warehouses = json_decode($depotInfo['warehouses'], true);
                        $warehouse_id = $warehouses[0]; // We support single warehouse in a depot now.
                        $warehouseUserInfo = $this->Users->find('all', ['conditions'=>['status !='=>99, 'warehouse_id'=>$warehouse_id]])->first();

                        if($warehouseUserInfo){
                            // Event entry
                            $poEvent = $this->PoEvents->newEntity();
                            $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['chalan'];
                            $poEventData['reference_id'] = $chalanResult['id'];
                            $poEventData['recipient_id'] = $warehouseUserInfo['id'];
                            $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['deliver'];
                            $poEventData['created_by'] = $user['id'];
                            $poEventData['created_date'] = $time;
                            $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                            $this->PoEvents->save($poEvent);
                        } else {
                            $this->Flash->error('Forward not possible, no warehouse in charge. Please try again!');
                            throw new Exception('error');
                        }
                    }

                    // Event is_action_taken 1
                    $eventIds = $data['eventIds'];
                    foreach($eventIds as $eventId){
                        $event = TableRegistry::get('po_events');
                        $query = $event->query();
                        $query->update()->set(['is_action_taken' => 1])->where(['id' => $eventId])->execute();
                    }
                }
            });

            $this->Flash->success('Successfully done.');
            return $this->redirect(['action' => 'index']);
        } catch (Exception $e) {
//            echo '<pre>';
//            print_r($e);
//            echo '</pre>';
//            die();
            $this->Flash->error('Failed. Please try again!');
            return $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Invoice Chalan id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $invoiceChalan = $this->InvoiceChalans->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $invoiceChalan = $this->InvoiceChalans->patchEntity($invoiceChalan, $data);
            if ($this->InvoiceChalans->save($invoiceChalan)) {
                $this->Flash->success('The invoice chalan has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The invoice chalan could not be saved. Please, try again.');
            }
        }
        $this->set(compact('invoiceChalan'));
        $this->set('_serialize', ['invoiceChalan']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Invoice Chalan id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $invoiceChalan = $this->InvoiceChalans->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $invoiceChalan = $this->InvoiceChalans->patchEntity($invoiceChalan, $data);
        if ($this->InvoiceChalans->save($invoiceChalan)) {
            $this->Flash->success('The invoice chalan has been deleted.');
        } else {
            $this->Flash->error('The invoice chalan could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function chalanInvoice()
    {
        $this->loadModel('Invoices');
        $this->loadModel('InvoicedProducts');
        $data = $this->request->data;
        $invoiceIds = $data['invoice_ids'];

        if(sizeof($invoiceIds)>0){
            $invoices = [];
            $eventIds = [];
            foreach($invoiceIds as $event_id=>$id):
                $eventIds[] = $event_id;
                $invoices[] = $this->Invoices->find('all', ['contain'=>['InvoicedProducts', 'Customers'], 'conditions'=>['Invoices.status !='=>99, 'Invoices.id'=>$id]])->first();
            endforeach;

            App::import('Helper', 'SystemHelper');
            $SystemHelper = new SystemHelper(new View());
            $itemArray = $SystemHelper->get_item_unit_array();

            $this->set(compact('invoices', 'itemArray', 'invoiceIds', 'eventIds'));
            $this->set('_serialize', ['invoices']);
        } else{
            $this->Flash->error('Please come again sequentially. Thank you!');
            return $this->redirect(['action' => 'index']);
        }
    }

    public function makeChalan()
    {
        $user = $this->Auth->user();
        $this->loadModel('Invoices');
        $this->loadModel('Items');
        $data = $this->request->data;
        $invoiceIds = $data['invoiceIds'];
        $eventIds = $data['eventIds'];

        if(sizeof($invoiceIds)>0){
            App::import('Helper', 'SystemHelper');
            $SystemHelper = new SystemHelper(new View());
            $itemArray = $SystemHelper->get_item_unit_array();

            $invoices = [];
            foreach($invoiceIds as $id):
                $invoices[] = $this->Invoices->find('all', ['contain'=>['InvoicedProducts', 'Customers'], 'conditions'=>['Invoices.status !='=>99, 'Invoices.id'=>$id]])->first();
            endforeach;

            $info = [];
            foreach($invoices as $invoice) {
                foreach($invoice['invoiced_products'] as $itemDetail){
                    $arr = [];
                    $arr['item_unit_id'] = $itemDetail['item_unit_id'];
                    $arr['product_quantity'] = $itemDetail['product_quantity'];
                    $arr['bonus_quantity'] = $itemDetail['bonus_quantity'];
                    $arr['special_offer_bonus_quantity'] = $itemDetail['special_offer_bonus_quantity'];
                    $info[] = $arr;
                }
            }

            $returnData = [];
            foreach ($info as $key=>$item) {
                $key = $item['item_unit_id'];
                if (!array_key_exists($key, $returnData)) {
                    $returnData[$key] = [
                        'item_unit_id' => $item['item_unit_id'],
                        'product_quantity' => $item['product_quantity'],
                        'bonus_quantity' => $item['bonus_quantity'],
                        'special_offer_bonus_quantity' => $item['special_offer_bonus_quantity'],
                    ];
                } else {
                    $returnData[$key]['product_quantity'] = $returnData[$key]['product_quantity'] + $item['product_quantity'];
                    $returnData[$key]['bonus_quantity'] = $returnData[$key]['bonus_quantity'] + $item['bonus_quantity'];
                    $returnData[$key]['special_offer_bonus_quantity'] = $returnData[$key]['special_offer_bonus_quantity'] + $item['special_offer_bonus_quantity'];
                }
                $key++;
            }

            // Serial Management
            $this->loadModel('Serials');
            $serial_for = array_flip(Configure::read('serial_types'))['sales_delivery_chalan'];
            $year = date('Y');
            $trigger_type = array_flip(Configure::read('serial_trigger_types'))['depot'];
            $trigger_id = $user['depot_id'];

            $existence = $this->Serials->find('all', ['conditions'=>['serial_for'=>$serial_for, 'year'=>$year, 'trigger_type'=>$trigger_type, 'trigger_id'=>$trigger_id]])->first();

            if ($existence) {
                $serial = TableRegistry::get('serials');
                $query = $serial->query();
                $query->update()->set(['serial_no' => $existence['serial_no']+1])->where(['id' => $existence['id']])->execute();
                $sl_no = $existence['serial_no']+1;
            } else {
                $serial = $this->Serials->newEntity();
                $serialData['trigger_type'] = $trigger_type;
                $serialData['trigger_id'] = $trigger_id;
                $serialData['serial_for'] = $serial_for;
                $serialData['year'] = $year;
                $serialData['serial_no'] = 1;
                $serialData['created_by'] = $user['id'];
                $serialData['created_date'] = time();
                $serial = $this->Serials->patchEntity($serial, $serialData);
                $this->Serials->save($serial);
                $sl_no = 1;
            }

            $this->set(compact('returnData', 'itemArray', 'sl_no', 'invoiceIds', 'eventIds'));
            $this->set('_serialize', ['returnData']);
        } else{
            $this->Flash->error('Please come again sequentially. Thank you!');
            return $this->redirect(['action' => 'index']);
        }
    }
}
