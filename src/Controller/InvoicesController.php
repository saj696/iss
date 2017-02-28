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
class InvoicesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Invoices.id' => 'desc'
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
        $invoices = $this->Invoices->find('all', [
            'conditions' => ['Invoices.status !=' => 99, 'Invoices.created_by'=>$user['id']],
            'contain' => ['Customers']
        ]);
        $this->set('invoices', $this->paginate($invoices));
        $this->set('_serialize', ['invoices']);
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
        $invoice = $this->Invoices->get($id, [
            'contain' => ['Customers']
        ]);
        $this->set('invoice', $invoice);
        $this->set('_serialize', ['invoice']);
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
        $this->loadModel('Pos');
        $this->loadModel('Offers');
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('AdministrativeLevels');
        $this->loadModel('Customers');
        $this->loadModel('PoProducts');
        $this->loadModel('DepotCoverages');
        $this->loadModel('PoEvents');
        $this->loadModel('Users');
        $this->loadModel('Depots');
        $this->loadModel('Items');
        $this->loadModel('Invoices');
        $this->loadModel('InvoicedProducts');
        $this->loadModel('InvoiceCycleConfigurations');
        $this->loadModel('Prices');
        $this->loadModel('ItemUnits');
        $invoice = $this->Invoices->newEntity();

        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($invoice, $user, $time, &$saveStatus)
                {
                    $invoiceCycleInfo = $this->InvoiceCycleConfigurations->find('all', ['conditions'=>['status !='=>99]])->first();
                    $data = $this->request->data;
                    $invoiceData['customer_level_no'] = $data['customer_level_no'];
                    $customerUnitInfo = $this->AdministrativeUnits->get($data['customer_unit']);
                    $invoiceData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                    $invoiceData['customer_id'] = $data['customer_id'];
                    $customerInfo = $this->Customers->get($data['customer_id']);
                    if($customerInfo['is_mango']==1):
                        $invoiceData['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
                    else:
                        $invoiceData['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
                    endif;
                    $invoiceData['delivery_date'] = strtotime($data['delivery_date']);
                    $invoiceData['invoice_type'] = $data['invoice_type'];
                    $invoiceData['net_total'] = $data['total_amount_hidden'];

                    if($invoiceCycleInfo['invoice_approved_at']==array_flip(Configure::read('invoice_approved_at'))['Not Needed']){
                        $invoiceData['approval_status'] = array_flip(Configure::read('invoice_approval_status'))['not_required'];
                    } else {
                        $invoiceData['approval_status'] = array_flip(Configure::read('invoice_approval_status'))['waiting'];
                    }

                    $depotInfo = $this->Depots->get($user['depot_id']);
                    $depotUnitInfo = $this->AdministrativeUnits->get($depotInfo['unit_id']);
                    $invoiceData['depot_level_no'] = $depotInfo['level_no'];
                    $invoiceData['depot_unit_global_id'] = $depotUnitInfo['global_id'];
                    $invoiceData['depot_id'] = $user['depot_id'];
                    $invoiceData['due'] = $data['total_amount_hidden'];
                    $invoiceData['invoice_date'] = $time;

                    $invoiceData['created_by'] = $user['id'];
                    $invoiceData['created_date'] = $time;
                    $invoice = $this->Invoices->patchEntity($invoice, $invoiceData);
                    $result = $this->Invoices->save($invoice);

                    // Invoiced Products table insert
                    foreach($data['detail'] as $item_unit_id=>$itemDetail):
                        $invoicedProducts = $this->InvoicedProducts->newEntity();
                        $itemUnitInfo = $this->ItemUnits->get($item_unit_id);
                        $invoicedProductsData['invoice_id'] = $result['id'];
                        $invoicedProductsData['invoice_type'] = $data['invoice_type'];
                        $invoicedProductsData['customer_level_no'] = $data['customer_level_no'];
                        $invoicedProductsData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                        $invoicedProductsData['customer_id'] = $data['customer_id'];
                        $invoicedProductsData['customer_type'] = $invoiceData['customer_type'];
                        $invoicedProductsData['invoice_date'] = $time;
                        $invoicedProductsData['delivery_date'] = strtotime($data['delivery_date']);

                        $invoicedProductsData['depot_level_no'] = $invoiceData['depot_level_no'];
                        $invoicedProductsData['depot_unit_global_id'] = $invoiceData['depot_unit_global_id'];
                        $invoicedProductsData['depot_id'] = $user['depot_id'];

                        $invoicedProductsData['item_unit_id'] = $item_unit_id;
                        $invoicedProductsData['item_id'] = $itemUnitInfo['item_id'];
                        $invoicedProductsData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                        $invoicedProductsData['product_quantity'] = $itemDetail['item_quantity'];
                        $invoicedProductsData['bonus_quantity'] = $itemDetail['item_bonus'];
                        $invoicedProductsData['special_offer_bonus_quantity'] = $itemDetail['special_offer_item_bonus'];
                        $invoicedProductsData['instant_discount'] = $itemDetail['item_cash_discount'];
                        $invoicedProductsData['net_total'] = $itemDetail['item_net_total'];
                        $invoicedProductsData['due'] = $itemDetail['item_net_total'];
                        $invoicedProductsData['offer_id'] = $itemDetail['offer_id'];
                        if($itemDetail['offer_id']>0){
                            $offerInfo = $this->Offers->get($itemDetail['offer_id']);
                            $invoicedProductsData['is_credit_note_allowed'] = $offerInfo['is_credit_note_allowed'];
                        }
                        $invoicedProductsData['created_by'] = $user['id'];
                        $invoicedProductsData['created_date'] = $time;
                        $invoicedProducts = $this->InvoicedProducts->patchEntity($invoicedProducts, $invoicedProductsData);
                        $this->InvoicedProducts->save($invoicedProducts);
                    endforeach;

                    // Event creation
                    if($invoiceCycleInfo['invoice_approved_at']==array_flip(Configure::read('invoice_approved_at'))['Not Needed']){
                        $poEvent = $this->PoEvents->newEntity();
                        $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['invoice'];
                        $poEventData['reference_id'] = $result['id'];
                        $poEventData['recipient_id'] = $user['id'];
                        $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['make_chalan'];
                        $poEventData['created_by'] = $user['id'];
                        $poEventData['created_date'] = $time;
                        $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                        $this->PoEvents->save($poEvent);
                    } else {
                        if($invoiceCycleInfo['allow_delivery_before_approval']==1){
                            // Self event
                            $poEvent = $this->PoEvents->newEntity();
                            $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['invoice'];
                            $poEventData['reference_id'] = $result['id'];
                            $poEventData['recipient_id'] = $user['id'];
                            $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['make_chalan'];
                            $poEventData['created_by'] = $user['id'];
                            $poEventData['created_date'] = $time;
                            $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                            $this->PoEvents->save($poEvent);
                            // Approval users events
                            $approvalUsers = $this->Users->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$invoiceCycleInfo['invoice_approved_at'], 'user_group_id'=>$invoiceCycleInfo['approving_user_group']]]);
                            if(sizeof($approvalUsers)>0){
                                foreach($approvalUsers as $user){
                                    $poEvent = $this->PoEvents->newEntity();
                                    $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['invoice'];
                                    $poEventData['reference_id'] = $result['id'];
                                    $poEventData['recipient_id'] = $user->id;
                                    $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['invoice_approval'];
                                    $poEventData['created_by'] = $user['id'];
                                    $poEventData['created_date'] = $time;
                                    $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                                    $this->PoEvents->save($poEvent);
                                }
                            }
                        } else {
                            // Approval users events
                            $approvalUsers = $this->Users->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$invoiceCycleInfo['invoice_approved_at'], 'user_group_id'=>$invoiceCycleInfo['approving_user_group']]]);
                            if(sizeof($approvalUsers)>0){
                                foreach($approvalUsers as $user){
                                    $poEvent = $this->PoEvents->newEntity();
                                    $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['invoice'];
                                    $poEventData['reference_id'] = $result['id'];
                                    $poEventData['recipient_id'] = $user->id;
                                    $poEventData['event_type'] = array_flip(Configure::read('po_event_types'))['invoice_approval'];
                                    $poEventData['created_by'] = $user['id'];
                                    $poEventData['created_date'] = $time;
                                    $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                                    $this->PoEvents->save($poEvent);
                                }
                            }
                        }
                    }
                });

                $this->Flash->success('Invoice created successfully. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Invoice creation not possible. Please try again!');
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
        $customers = $this->Invoices->Customers->find('list', ['conditions' => ['status' => 1]]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $this->set(compact('invoice', 'customers', 'administrativeLevels', 'itemArray'));
        $this->set('_serialize', ['invoice']);
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
        $user = $this->Auth->user();
        $userAdministrativeUnit = $user['administrative_unit_id'];
        $this->loadModel('AdministrativeUnits');

        $userAdministrativeUnitInfo = $this->AdministrativeUnits->get($userAdministrativeUnit);
        $limitStart = pow(2,(Configure::read('max_level_no')- $user['level_no']-1)*5);
        $limitEnd = pow(2,(Configure::read('max_level_no')- $user['level_no'])*5);

        $data = $this->request->data;
        $level = $data['level'];

        $units = TableRegistry::get('administrative_units')->find('all');
        $units->select(['id', 'unit_name']);
        $units->where(['level_no'=>$level]);
        $units->where('global_id -'. $userAdministrativeUnitInfo['global_id'] .'>= '.$limitStart);
        $units->where('global_id -'. $userAdministrativeUnitInfo['global_id'] .'< '.$limitEnd);
        $units->toArray();

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
        $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields'=>['id', 'name', 'code']])->hydrate(false)->toArray();

        $dropArray = [];
        foreach($customers as $customer):
            $dropArray[$customer['id']] = $customer['name'].'-'.$customer['code'];
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

        $this->loadComponent('Common');
        $currentDue = $this->Common->getCustomerDue($customer_id, time());

        $arr = [];
        $arr['credit_limit'] = $customer->credit_limit?$customer->credit_limit:0;
        $arr['available_credit'] = ($customer->credit_limit - $currentDue)>0?($customer->credit_limit - $currentDue):0;
        $arr['cash_invoice_days'] = $customer->cash_invoice_days?$customer->cash_invoice_days:0;
        $arr['credit_invoice_days'] = $customer->credit_invoice_days?$customer->credit_invoice_days:0;
        $arr['address'] = $customer->address?$customer->address:'';

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

        $this->loadModel('Invoices');
        $oldest = $this->Invoices->find('all', ['conditions'=>['customer_id'=>$customer_id, 'due >'=>0, 'delivery_status'=>array_flip(Configure::read('invoice_delivery_status'))['delivered'], 'status'=>1], 'order'=>['delivery_date ASC'], 'limit'=>1])->first();

        if($oldest){
            $dateDiff = (time()-$oldest['invoice_date'])/(60*60*24);
            if($dateDiff>0){
                $invoiceArray['max_due_invoice_age'] = $dateDiff;
            }else{
                $invoiceArray['max_due_invoice_age'] = 0;
            }
        }else{
            $invoiceArray['max_due_invoice_age'] = 0;
        }

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
            $invoiceArray['last_payment_date'] = time();
        }else{
            $invoiceArray['due'] = 1;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
            $invoiceArray['last_payment_date'] = strtotime('01-01-2027');
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
                $wonOffers = $this->Common->getWonOffer($applicablePostfix, $invoiceArray, $offer->id);
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

        $this->loadModel('Invoices');
        $oldest = $this->Invoices->find('all', ['conditions'=>['customer_id'=>$customer_id, 'due >'=>0, 'delivery_status'=>array_flip(Configure::read('invoice_delivery_status'))['delivered'], 'status'=>1], 'order'=>['delivery_date ASC'], 'limit'=>1])->first();
        if($oldest){
            $dateDiff = (time()-$oldest['invoice_date'])/(60*60*24);
            if($dateDiff>0){
                $invoiceArray['max_due_invoice_age'] = $dateDiff;
            }else{
                $invoiceArray['max_due_invoice_age'] = 0;
            }
        }else{
            $invoiceArray['max_due_invoice_age'] = 0;
        }

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
            $invoiceArray['last_payment_date'] = time();
        }else{
            $invoiceArray['due'] = 1;
            $invoiceArray['delivery_date'] = strtotime(date('d-m-Y'));
            $invoiceArray['updated_date'] = strtotime(date('d-m-Y',strtotime(date("d-m-Y", time()) . " + 365 day")));
            $invoiceArray['last_payment_date'] = strtotime('01-01-2027');
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
                            $offerItems[$option->offer_id][] = $item['item_unit_id'];
                        }
                    }
                }
            }
        }

        $wonOffers = [];
        $ApplicableWonOffers = [];
        foreach($offerArray as $serial=>$offer){
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
                $ApplicableWonOffers[$serial] = $this->Common->getWonOffer($applicablePostfix, $invoiceArray, $offer->id);
            }
        }

        foreach($ApplicableWonOffers as $applicableWonOffer){
            foreach($applicableWonOffer as $won){
                $wonOffers[] = $won;
            }
        }

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('wonOffers','offerItems','itemArray'));
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
}
