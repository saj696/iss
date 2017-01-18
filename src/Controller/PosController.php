<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * Pos Controller
 *
 * @property \App\Model\Table\PosTable $Pos
 */
class PosController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Pos.id' => 'desc'
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
        $pos = $this->Pos->find('all', [
            'conditions' => ['Pos.status !=' => 99, 'Pos.created_by'=>$user['id']],
            'contain' => ['Customers']
        ]);
        $this->set('pos', $this->paginate($pos));
        $this->set('_serialize', ['pos']);
    }

    /**
     * View method
     *
     * @param string|null $id Po id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $po = $this->Pos->get($id, [
            'contain' => ['Customers']
        ]);
        $this->set('po', $po);
        $this->set('_serialize', ['po']);
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
        $po = $this->Pos->newEntity();
        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($po, $user, $time, &$saveStatus)
                {
                    $data = $this->request->data;
                    $this->loadModel('AdministrativeUnits');
                    $this->loadModel('Customers');
                    $this->loadModel('PoProducts');
                    $this->loadModel('DepotCoverages');
                    $this->loadModel('PoEvents');
                    $this->loadModel('Users');
                    $this->loadModel('Depots');
                    $this->loadModel('ItemUnits');

                    $poData['customer_level_no'] = $data['customer_level_no'];
                    $customerUnitInfo = $this->AdministrativeUnits->get($data['customer_unit']);
                    $poData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                    $poData['customer_id'] = $data['customer_id'];
                    $customerInfo = $this->Customers->get($data['customer_id']);
                    if($customerInfo['is_mango']==1):
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
                    else:
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
                    endif;
                    $poData['po_date'] = strtotime($data['po_date']);
                    if($data['delivery_date']):
                        $poData['delivery_date'] = strtotime($data['delivery_date']);
                    endif;
                    $poData['invoice_type'] = $data['invoice_type'];
                    $poData['net_total'] = $data['total_amount_hidden'];
                    if(isset($_POST['forward'])):
                        $poData['po_status'] = array_flip(Configure::read('po_status'))['forwarded'];
                    else:
                        $poData['po_status'] = array_flip(Configure::read('po_status'))['saved'];
                    endif;
                    $poData['field_po_no'] = $data['field_po_no'];
                    $poData['created_by'] = $user['id'];
                    $poData['created_date'] = $time;
                    $po = $this->Pos->patchEntity($po, $poData);
                    $result = $this->Pos->save($po);

                    // PO Products table insert
                    foreach($data['detail'] as $item_unit_id=>$itemDetail):
                        $poProducts = $this->PoProducts->newEntity();
                        $itemUnitInfo = $this->ItemUnits->get($item_unit_id);
                        $poProductData['po_id'] = $result['id'];
                        $poProductData['item_unit_id'] = $item_unit_id;
                        $poProductData['item_id'] = $itemUnitInfo['item_id'];
                        $poProductData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                        $poProductData['product_quantity'] = $itemDetail['item_quantity'];
                        $poProductData['bonus_quantity'] = $itemDetail['item_bonus'];
                        $poProductData['special_offer_bonus_quantity'] = $itemDetail['special_offer_item_bonus'];
                        $poProductData['instant_discount'] = $itemDetail['item_cash_discount'];
                        $poProductData['net_total'] = $itemDetail['item_net_total'];
                        $poProductData['created_by'] = $user['id'];
                        $poProductData['created_date'] = $time;
                        $poProducts = $this->PoProducts->patchEntity($poProducts, $poProductData);
                        $this->PoProducts->save($poProducts);
                    endforeach;

                    // Event Creation
                    if(isset($_POST['forward']))
                    {
                        $poEvent = $this->PoEvents->newEntity();
                        $customerLevel = $data['customer_level_no'];
                        $customerLevelDepotCoverage = $this->DepotCoverages->find('all', ['conditions'=>['level_no'=>$customerLevel]])->first();
                        $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotCoverage['depot_id']]])->first();

                        if($depotInCharge['id']):
                            $recipient_id = $depotInCharge['id'];
                        else:
                            $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$customerLevel]])->first();
                            $customerLevelDepotId = $customerLevelDepot['id'];
                            if($customerLevelDepotId):
                                $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotId]])->first();
                                $recipient_id = $depotInCharge['id'];
                            else:
                                for($i=$customerLevel; $i>=0; $i--):
                                    $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$i]])->first();
                                    $customerLevelDepotId = $customerLevelDepot['id'];
                                    $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotId]])->first();
                                    $recipient_id = $depotInCharge['id'];
                                    if($recipient_id>0):
                                        break;
                                    else:
                                        continue;
                                    endif;
                                endfor;
                            endif;
                        endif;

                        if($recipient_id && $recipient_id>0):
                            $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['po'];
                            $poEventData['reference_id'] = $result['id'];
                            $poEventData['recipient_id'] = $recipient_id;
                            $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['po'];
                            $poEventData['created_by'] = $user['id'];
                            $poEventData['created_date'] = $time;
                            $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                            $this->PoEvents->save($poEvent);
                        else:
                            $this->Flash->error('No Depot In Charge. Please try again!');
                            throw new \Exception('error');
                        endif;
                    }
                });

                $this->Flash->success('PO done successfully. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('PO not possible. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }
        $customers = $this->Pos->Customers->find('list', ['conditions' => ['status' => 1]]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $this->set(compact('po', 'customers', 'administrativeLevels', 'itemArray'));
        $this->set('_serialize', ['po']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Po id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $po = $this->Pos->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $po = $this->Pos->patchEntity($po, $data);
            if ($this->Pos->save($po)) {
                $this->Flash->success('The po has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The po could not be saved. Please, try again.');
            }
        }

        $customers = $this->Pos->Customers->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('po', 'customerUnitGlobals', 'customers'));
        $this->set('_serialize', ['po']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Po id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $po = $this->Pos->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $po = $this->Pos->patchEntity($po, $data);
        if ($this->Pos->save($po)) {
            $this->Flash->success('The po has been deleted.');
        } else {
            $this->Flash->error('The po could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getUnit()
    {
        $data = $this->request->data;
        $level = $data['level'];
        $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();

        $dropArray = [];
        foreach($units as $unit):
            $dropArray[$unit['id']] = $unit['unit_name'];
        endforeach;

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function getCustomer()
    {
        $data = $this->request->data;
        $unit = $data['unit'];
        $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields'=>['id', 'name']])->hydrate(false)->toArray();

        $dropArray = [];
        foreach($customers as $customer):
            $dropArray[$customer['id']] = $customer['name'];
        endforeach;

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function getCustomerDetail()
    {
        $this->autoRender = false;
        $data = $this->request->data;
        $customer_id = $data['customer_id'];
        $customer = TableRegistry::get('customers')->find('all', ['conditions' => ['id' => $customer_id]])->first();

        $arr = [];
        $arr['credit_limit'] = $customer->credit_limit?$customer->credit_limit:0;
        $arr['available_credit'] = $customer->credit_limit?$customer->credit_limit:0;
        $arr['cash_invoice_days'] = $customer->cash_invoice_days?$customer->cash_invoice_days:0;
        $arr['credit_invoice_days'] = $customer->credit_invoice_days?$customer->credit_invoice_days:0;

        $arr = json_encode($arr);
        $this->response->body($arr);
        return $this->response;
    }

    public function loadItem()
    {
        $data = $this->request->data;
        $this->loadModel('ItemUnits');
        $this->loadModel('Prices');

        $item_unit_id = $data['item_unit_id'];
        $invoice_type = $data['invoice_type'];

        $itemPrices = $this->Prices->find('all', ['conditions'=>['item_unit_id'=>$item_unit_id]])->first();

        if ($invoice_type == 1) {
            $unit_price = $itemPrices['cash_sales_price'];
        } elseif ($invoice_type == 2) {
            $unit_price = $itemPrices['credit_sales_price'];
        } else {
            $unit_price = 0;
        }

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $itemName = $itemArray[$item_unit_id];
        $this->viewBuilder()->layout('ajax');
        $this->set(compact('itemName', 'item_unit_id', 'unit_price'));
    }

    public function loadOffer(){
        $data = $this->request->data;
        $item_unit_id = $data['item_unit_id'];
        $invoice_type = $data['invoice_type'];
        $customer_id = $data['customer_id'];
        $item_quantity = $data['item_quantity'];
        $customer_level_no = $data['level_no'];
        $customer_unit = $data['customer_unit'];

        $this->loadModel('AdministrativeUnits');
        $this->loadModel('Customers');
        $this->loadModel('ItemUnits');
        $this->loadModel('OfferItems');
        $this->loadModel('Offers');
        $this->loadModel('Offers');
        $this->loadModel('ItemBonuses');

        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());

        $ItemUnitInfo = $this->ItemUnits->get($item_unit_id);
        $bonusQuantityInfo = $this->ItemBonuses->find('all', ['conditions'=>[
            'item_id'=>$ItemUnitInfo['item_id'],
            'manufacture_unit_id'=>$ItemUnitInfo['manufacture_unit_id'],
            'order_quantity_from <='=>$item_quantity,
            'order_quantity_to >='=>$item_quantity
        ]])->where(['invoice_type IN'=>[$invoice_type, 3]])->first();

        $customerUnitInfo = $this->AdministrativeUnits->get($customer_unit);
        $customerInfo = $this->Customers->get($customer_id);

        $invoiceArray = [];

        $invoiceArray['customer_level_no'] = $customer_level_no;
        $invoiceArray['customer_unit_global_id'] = $customerUnitInfo['global_id'];
        if($customerInfo['is_mango']==1):
            $invoiceArray['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
        else:
            $invoiceArray['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
        endif;
        $invoiceArray['customer_id'] = $customer_id;
        $invoiceArray['delivery_date'] = time();
        $invoiceArray['invoice_type'] = $invoice_type;
        $invoiceArray['invoice_date'] = time();

        if($invoice_type==1){
            $invoiceArray['due'] = 0;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y'));
        }else{
            $invoiceArray['due'] = 1;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
        }

        $invoiceArray['invoiced_products'][0]['customer_level_no'] = $customer_level_no;
        $invoiceArray['invoiced_products'][0]['customer_unit_global_id'] = $customerUnitInfo['global_id'];
        $invoiceArray['invoiced_products'][0]['customer_type'] = $invoiceArray['customer_type'];
        $invoiceArray['invoiced_products'][0]['customer_id'] = $customer_id;
        $invoiceArray['invoiced_products'][0]['invoice_date'] = time();
        $invoiceArray['invoiced_products'][0]['delivery_date'] = time();
        $invoiceArray['invoiced_products'][0]['item_id'] = $ItemUnitInfo['item_id'];
        $invoiceArray['invoiced_products'][0]['manufacture_unit_id'] = $ItemUnitInfo['manufacture_unit_id'];
        $invoiceArray['invoiced_products'][0]['product_quantity'] = $item_quantity;

        if($invoice_type==1){
            $invoiceArray['invoiced_products'][0]['due'] = 0;
            $invoiceArray['invoiced_products'][0]['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['invoiced_products'][0]['updated_date'] = strtotime(date('d-m-Y'));
        }else{
            $invoiceArray['invoiced_products'][0]['due'] = 1;
            $invoiceArray['invoiced_products'][0]['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['invoiced_products'][0]['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
        }

        // offer check
        $options = $this->OfferItems->find('all', ['conditions'=>[
            'item_unit_id'=>$item_unit_id,
            'program_period_start <='=>time(),
            'program_period_end >='=>time(),
            'invoicing !='=>array_flip(Configure::read('special_offer_invoicing'))['Cumulative'],
            'offer_payment_mode !='=>array_flip(Configure::read('offer_payment_mode'))['Delayed']
        ]])->where(['invoice_type IN'=>[array_flip(Configure::read('special_offer_invoice_types'))['Both'], $invoice_type]]);

        $offerArray = [];

        if(sizeof($options)>0){
            foreach($options as $option){
                if($option->offer_id>0){
                    if(!in_array($option->offer_id, $offerArray)){
                        $offerArray[] = $option->offer_id;
                    }
                }
            }
        }

        $wonOffers = [];
        foreach($offerArray as $offer){
            $offer = $this->Offers->get($offer);
            $conditions = json_decode($offer['conditions'], true);

            foreach($conditions as $k=>$condition){
                if($condition['level']==5 && $condition['context']==array_flip(Configure::read('offer_contexts'))['Invoice'] && $condition['time_level']==array_flip(Configure::read('offer_time_level'))['Instant']){
                    $conditionKey = $k;
                }
            }

            $conditionPostfix = json_decode($offer['condition_postfix'], true);

            if(isset($conditionKey)){
                $applicablePostfix = $conditionPostfix[$conditionKey];

                // general condition check
                $general = $applicablePostfix['general'];
                foreach($general as $k=>$genPost){
                    if($genPost['type']=='function'){
                        if($genPost['name']=='item_unit_quantity' || $genPost['name']=='item_bulk_quantity'){
                            $argArray = explode(',', $genPost['arg']);
                            $result = $FunctionHelper->$genPost['name']($argArray[0],$argArray[1],$invoiceArray);
                        }elseif($genPost['name']=='is_mango_customer'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='is_cash_invoice'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='payment_date'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='invoice_item_payment_age'){
                            $argArray = explode(',', $genPost['arg']);
                            $result = $FunctionHelper->$genPost['name']($argArray[0],$argArray[1],$invoiceArray);
                        }

                        if(isset($result)){
                            $general[$k]['type']='number';
                            $general[$k]['number']=$result;
                            unset($general[$k]['name']);
                            unset($general[$k]['arg']);
                        }
                    }
                }

                $generalEvaluation = $FunctionHelper->postfix_evaluator($general);

                // If general condition is true then specific condition will be evaluated
                if($generalEvaluation){
                    $specific = $applicablePostfix['specific'];
                    foreach($specific as $key=>$specPost){
                        foreach($specPost['condition'] as $sk=>$specCon){
                            if($specCon['type']=='function'){
                                if($specCon['name']=='item_unit_quantity' || $specCon['name']=='item_bulk_quantity'){
                                    $argArray = explode(',', $specCon['arg']);
                                    $result = $FunctionHelper->$specCon['name']($argArray[0],$argArray[1],$invoiceArray);
                                }elseif($specCon['name']=='is_mango_customer'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }elseif($specCon['name']=='is_cash_invoice'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }elseif($specCon['name']=='invoice_item_payment_age'){
                                    $argArray = explode(',', $specCon['arg']);
                                    $result = $FunctionHelper->$specCon['name']($argArray[0],$argArray[1],$invoiceArray);
                                }elseif($specCon['name']=='payment_date'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }

                                if(isset($result)){
                                    $specPost['condition'][$sk]['type'] = 'number';
                                    $specPost['condition'][$sk]['number'] = $result;
                                    unset($specPost['condition'][$sk]['name']);
                                    unset($specPost['condition'][$sk]['arg']);
                                }
                            }
                        }

                        $specConEvaluation = $FunctionHelper->postfix_evaluator($specPost['condition']);

                        // If specific condition is true then amount will be evaluated
                        if($specConEvaluation){
                            foreach($specPost['amount'] as $k=>$specAmount){
                                if($specAmount['type']=='function'){
                                    if($specAmount['name']=='item_unit_quantity' || $specAmount['name']=='item_bulk_quantity'){
                                        $argArray = explode(',', $specAmount['arg']);
                                        $result = $FunctionHelper->$specAmount['name']($argArray[0],$argArray[1],$invoiceArray);
                                    }elseif($specAmount['name']=='is_mango_customer'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }elseif($specAmount['name']=='is_cash_invoice'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }elseif($specAmount['name']=='invoice_item_payment_age'){
                                        $argArray = explode(',', $specAmount['arg']);
                                        $result = $FunctionHelper->$specAmount['name']($argArray[0],$argArray[1],$invoiceArray);
                                    }elseif($specAmount['name']=='payment_date'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }
                                    if(isset($result)){
                                        $specPost['amount'][$k]['type'] = 'number';
                                        $specPost['amount'][$k]['number'] = $result;
                                        unset($specPost['amount'][$k]['name']);
                                        unset($specPost['amount'][$k]['arg']);
                                    }
                                }
                            }
                            $specAmountEvaluation = $FunctionHelper->postfix_evaluator($specPost['amount']);
                            $wonOffers[$key]['value_or_quantity'] = $specAmountEvaluation;
                            $wonOffers[$key]['offer_type'] = $specific[$key]['offer_type'];
                            $wonOffers[$key]['offer_name'] = $specific[$key]['offer_name'];
                            $wonOffers[$key]['offer_unit_name'] = $specific[$key]['offer_unit_name'];
                            $wonOffers[$key]['amount_type'] = $specific[$key]['amount_type'];
                            $wonOffers[$key]['payment_mode'] = $specific[$key]['payment_mode'];
                            $wonOffers[$key]['amount_unit'] = $specific[$key]['amount_unit'];
                        }
                    }
                }
            }
        }

        $wonOffers = array_values($wonOffers);

        if(isset($wonOffers[0])){
            $wonOffers[0]['bonus_quantity'] = isset($bonusQuantityInfo->bonus_quantity)?$bonusQuantityInfo->bonus_quantity:0;
            $arr = json_encode($wonOffers[0]);
        }else{
            $arr = json_encode(0);
        }

        $this->response->body($arr);
        return $this->response;
    }

    public function checkOffer(){
        $data = $this->request->data;
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());

        $invoice_type = $data['invoice_type'];
        $customer_id = $data['customer_id'];
        $customer_level_no = $data['level_no'];
        $customer_unit = $data['customer_unit'];
        $item_array = $data['item_array'];

        $this->loadModel('AdministrativeUnits');
        $this->loadModel('Customers');
        $this->loadModel('ItemUnits');
        $this->loadModel('OfferItems');
        $this->loadModel('Offers');
        $customerUnitInfo = $this->AdministrativeUnits->get($customer_unit);
        $customerInfo = $this->Customers->get($customer_id);

        $invoiceArray = [];
        $offerArray = [];

        $invoiceArray['customer_level_no'] = $customer_level_no;
        $invoiceArray['customer_unit_global_id'] = $customerUnitInfo['global_id'];
        if($customerInfo['is_mango']==1):
            $invoiceArray['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
        else:
            $invoiceArray['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
        endif;
        $invoiceArray['customer_id'] = $customer_id;
        $invoiceArray['delivery_date'] = time();
        $invoiceArray['invoice_type'] = $invoice_type;
        $invoiceArray['invoice_date'] = time();

        if($invoice_type==1){
            $invoiceArray['due'] = 0;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y'));
        }else{
            $invoiceArray['due'] = 1;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
        }

        foreach($item_array as $key=>$item){
            $invoiceArray['invoiced_products'][$key]['customer_level_no'] = $customer_level_no;
            $invoiceArray['invoiced_products'][$key]['customer_unit_global_id'] = $customerUnitInfo['global_id'];
            $invoiceArray['invoiced_products'][$key]['customer_type'] = $invoiceArray['customer_type'];
            $invoiceArray['invoiced_products'][$key]['customer_id'] = $customer_id;
            $invoiceArray['invoiced_products'][$key]['invoice_date'] = time();
            $invoiceArray['invoiced_products'][$key]['delivery_date'] = time();
            $ItemUnitInfo = $this->ItemUnits->get($item['item_unit_id']);
            $invoiceArray['invoiced_products'][$key]['item_id'] = $ItemUnitInfo['item_id'];
            $invoiceArray['invoiced_products'][$key]['manufacture_unit_id'] = $ItemUnitInfo['manufacture_unit_id'];
            $invoiceArray['invoiced_products'][$key]['product_quantity'] = $item['item_quantity'];

            if($invoice_type==1){
                $invoiceArray['invoiced_products'][$key]['due'] = 0;
                $invoiceArray['invoiced_products'][$key]['delivery_date'] = strtotime(date('d-m-Y'));
                $invoiceArray['invoiced_products'][$key]['updated_date'] = strtotime(date('d-m-Y'));
            }else{
                $invoiceArray['invoiced_products'][$key]['due'] = 1;
                $invoiceArray['invoiced_products'][$key]['delivery_date'] = strtotime(date('d-m-Y'));
                $invoiceArray['invoiced_products'][$key]['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
            }

            // offer check
            $options = $this->OfferItems->find('all', ['conditions'=>[
                'item_unit_id'=>$item['item_unit_id'],
                'program_period_start <='=>time(),
                'program_period_end >='=>time(),
                'invoicing !='=>array_flip(Configure::read('special_offer_invoicing'))['Cumulative'],
                'offer_payment_mode !='=>array_flip(Configure::read('offer_payment_mode'))['Delayed']
            ]])->where(['invoice_type IN'=>[array_flip(Configure::read('special_offer_invoice_types'))['Both'], $invoice_type]]);

            if(sizeof($options)>0){
                foreach($options as $option){
                    if($option->offer_id>0){
                        if(!in_array($option->offer_id, $offerArray)){
                            $offerArray[] = $option->offer_id;
                        }
                    }
                }
            }
        }

        $wonOffers = [];
        foreach($offerArray as $offer){
            $offer = $this->Offers->get($offer);
            $conditions = json_decode($offer['conditions'], true);

            foreach($conditions as $k=>$condition){
                if($condition['level']==5 && $condition['context']==array_flip(Configure::read('offer_contexts'))['Invoice'] && $condition['time_level']==array_flip(Configure::read('offer_time_level'))['Instant']){
                    $conditionKey = $k;
                }
            }

            $conditionPostfix = json_decode($offer['condition_postfix'], true);

            if(isset($conditionKey)){
                $applicablePostfix = $conditionPostfix[$conditionKey];

                // general condition check
                $general = $applicablePostfix['general'];
                foreach($general as $k=>$genPost){
                    if($genPost['type']=='function'){
                        if($genPost['name']=='item_unit_quantity' || $genPost['name']=='item_bulk_quantity'){
                            $argArray = explode(',', $genPost['arg']);
                            $result = $FunctionHelper->$genPost['name']($argArray[0],$argArray[1],$invoiceArray);
                        }elseif($genPost['name']=='is_mango_customer'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='is_cash_invoice'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='payment_date'){
                            $result = $FunctionHelper->$genPost['name']($invoiceArray);
                        }elseif($genPost['name']=='invoice_item_payment_age'){
                            $argArray = explode(',', $genPost['arg']);
                            $result = $FunctionHelper->$genPost['name']($argArray[0],$argArray[1],$invoiceArray);
                        }

                        if(isset($result)){
                            $general[$k]['type']='number';
                            $general[$k]['number']=$result;
                            unset($general[$k]['name']);
                            unset($general[$k]['arg']);
                        }
                    }
                }

                $generalEvaluation = $FunctionHelper->postfix_evaluator($general);

                // If general condition is true then specific condition will be evaluated
                if($generalEvaluation){
                    $specific = $applicablePostfix['specific'];
                    foreach($specific as $key=>$specPost){
                        foreach($specPost['condition'] as $k=>$specCon){
                            if($specCon['type']=='function'){
                                if($specCon['name']=='item_unit_quantity' || $specCon['name']=='item_bulk_quantity'){
                                    $argArray = explode(',', $specCon['arg']);
                                    $result = $FunctionHelper->$specCon['name']($argArray[0],$argArray[1],$invoiceArray);
                                }elseif($specCon['name']=='is_mango_customer'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }elseif($specCon['name']=='is_cash_invoice'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }elseif($specCon['name']=='invoice_item_payment_age'){
                                    $argArray = explode(',', $specCon['arg']);
                                    $result = $FunctionHelper->$specCon['name']($argArray[0],$argArray[1],$invoiceArray);
                                }elseif($specCon['name']=='payment_date'){
                                    $result = $FunctionHelper->$specCon['name']($invoiceArray);
                                }

                                if(isset($result)){
                                    $specPost['condition'][$k]['type'] = 'number';
                                    $specPost['condition'][$k]['number'] = $result;
                                    unset($specPost['condition'][$k]['name']);
                                    unset($specPost['condition'][$k]['arg']);
                                }
                            }
                        }

                        $specConEvaluation = $FunctionHelper->postfix_evaluator($specPost['condition']);

                        // If specific condition is true then amount will be evaluated
                        if($specConEvaluation){
                            foreach($specPost['amount'] as $k=>$specAmount){
                                if($specAmount['type']=='function'){
                                    if($specAmount['name']=='item_unit_quantity' || $specAmount['name']=='item_bulk_quantity'){
                                        $argArray = explode(',', $specAmount['arg']);
                                        $result = $FunctionHelper->$specAmount['name']($argArray[0],$argArray[1],$invoiceArray);
                                    }elseif($specAmount['name']=='is_mango_customer'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }elseif($specAmount['name']=='is_cash_invoice'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }elseif($specAmount['name']=='invoice_item_payment_age'){
                                        $argArray = explode(',', $specAmount['arg']);
                                        $result = $FunctionHelper->$specAmount['name']($argArray[0],$argArray[1],$invoiceArray);
                                    }elseif($specAmount['name']=='payment_date'){
                                        $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                    }
                                    if(isset($result)){
                                        $specPost['amount'][$k]['type'] = 'number';
                                        $specPost['amount'][$k]['number'] = $result;
                                        unset($specPost['amount'][$k]['name']);
                                        unset($specPost['amount'][$k]['arg']);
                                    }
                                }
                            }
                            $specAmountEvaluation = $FunctionHelper->postfix_evaluator($specPost['amount']);
                            $wonOffers[$key]['value'] = $specAmountEvaluation;
                            $wonOffers[$key]['offer_type'] = $specific[$key]['offer_type'];
                            $wonOffers[$key]['offer_name'] = $specific[$key]['offer_name'];
                            $wonOffers[$key]['offer_unit_name'] = $specific[$key]['offer_unit_name'];
                            $wonOffers[$key]['amount_type'] = $specific[$key]['amount_type'];
                            $wonOffers[$key]['payment_mode'] = $specific[$key]['payment_mode'];
                            $wonOffers[$key]['amount_unit'] = $specific[$key]['amount_unit'];
                        }
                    }
                }
            }
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('wonOffers'));
    }

    public function checkInvoiceTypeEligibility(){
        $data = $this->request->data;
        $invoice_type = $data['invoice_type'];
        $customer_id = $data['customer_id'];
        $cash_invoice_days = $data['cash_invoice_days'];
        $credit_invoice_days = $data['credit_invoice_days'];

        $this->loadModel('Invoices');
        $oldest = $this->Invoices->find('all', ['conditions'=>['customer_id'=>$customer_id, 'due >'=>0, 'delivery_status'=>array_flip(Configure::read('invoice_delivery_status'))['delivered'], 'status'=>1], 'order'=>['delivery_date ASC'], 'limit'=>1])->first();

        if(sizeof($oldest)>0){
            $dateDiff = (time()-$oldest['delivery_date'])/(60*60*24);
            if($invoice_type==1 && $dateDiff > $cash_invoice_days){
                $arr = json_encode(0);
                $this->response->body($arr);
            }elseif($invoice_type==2 && $dateDiff > $credit_invoice_days){
                $arr = json_encode(0);
                $this->response->body($arr);
            }else{
                $arr = json_encode(1);
                $this->response->body($arr);
            }
        }else{
            $arr = json_encode(1);
            $this->response->body($arr);
        }
        return $this->response;
    }

    public function forward($id)
    {
        $user = $this->Auth->user();
        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($id, $user, &$saveStatus)
            {
                $this->loadModel('AdministrativeUnits');
                $this->loadModel('Customers');
                $this->loadModel('PoProducts');
                $this->loadModel('DepotCoverages');
                $this->loadModel('PoEvents');
                $this->loadModel('Users');
                $this->loadModel('Depots');

                $poInfo = $this->Pos->get($id);

                $poEvent = $this->PoEvents->newEntity();
                $customerLevel = $poInfo['customer_level_no'];
                $customerLevelDepotCoverage = $this->DepotCoverages->find('all', ['conditions'=>['status'=>1, 'level_no'=>$customerLevel]])->first();
                $depotInCharge = $this->Users->find('all', ['conditions'=>['status'=>1, 'depot_id'=>$customerLevelDepotCoverage['depot_id']]])->first();

                if($depotInCharge['id']):
                    $recipient_id = $depotInCharge['id'];
                else:
                    $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status'=>1, 'level_no'=>$customerLevel]])->first();
                    $customerLevelDepotId = $customerLevelDepot['id'];
                    if($customerLevelDepotId):
                        $depotInCharge = $this->Users->find('all', ['conditions'=>['status'=>1, 'depot_id'=>$customerLevelDepotId]])->first();
                        $recipient_id = $depotInCharge['id'];
                    else:
                        for($i=$customerLevel; $i>=0; $i--):
                            $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status'=>1, 'level_no'=>$i]])->first();
                            $customerLevelDepotId = $customerLevelDepot['id'];
                            $depotInCharge = $this->Users->find('all', ['status'=>1, 'conditions'=>['depot_id'=>$customerLevelDepotId]])->first();
                            $recipient_id = $depotInCharge['id'];
                            if($recipient_id>0):
                                break;
                            else:
                                continue;
                            endif;
                        endfor;
                    endif;
                endif;

                if($recipient_id && $recipient_id>0):
                    // Event entry
                    $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['po'];
                    $poEventData['reference_id'] = $id;
                    $poEventData['recipient_id'] = $recipient_id;
                    $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['po'];
                    $poEventData['created_by'] = $user['id'];
                    $poEventData['created_date'] = time();
                    $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                    $this->PoEvents->save($poEvent);

                    // PO status update
                    $pos = TableRegistry::get('pos');
                    $query = $pos->query();
                    $query->update()->set(['po_status' => array_flip(Configure::read('po_status'))['forwarded']])->where(['id' => $id])->execute();
                else:
                    $this->Flash->error('No Depot In Charge. Please try again!');
                    throw new \Exception('error');
                endif;
            });

            $this->Flash->success('PO forwarding done successfully. Thank you!');
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
            $this->Flash->error('PO forwarding not possible. Please try again!');
            return $this->redirect(['action' => 'index']);
        }
    }
}
