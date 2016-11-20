<?php
namespace App\Controller;

use App\Controller\AppController;
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
class ApprovePosController extends AppController
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
        $this->loadModel('Pos');
        $this->loadModel('PoEvents');
        $events = $this->PoEvents->find('all', [
            'conditions' => ['Pos.status !=' => 99, 'recipient_id'=>$user['id'], 'event_type'=>array_flip(Configure::read('po_event_types'))['po']],
            'contain' => ['Pos'=>['PoProducts', 'Customers']]
        ]);

        $this->set('events', $this->paginate($events));
        $this->set('_serialize', ['events']);
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
        $this->loadModel('Pos');
        $po = $this->Pos->get($id, [
            'contain' => ['Customers']
        ]);
        $this->set('po', $po);
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
        $this->loadModel('Pos');
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

        $event = $this->PoEvents->get($id, [
            'contain' => ['Pos'=>['PoProducts', 'Customers']]
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($id, $event, $user, $time, &$saveStatus)
                {
                    $invoiceCycleInfo = $this->InvoiceCycleConfigurations->find('all', ['conditions'=>['status !='=>99]])->first();
                    $invoice = $this->Invoices->newEntity();
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
                    $invoiceData['po_id'] = $data['po_id'];
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
                    foreach($data['detail'] as $item_id=>$itemDetail):
                        $invoicedProducts = $this->InvoicedProducts->newEntity();
                        $invoicedProductsData['invoice_id'] = $result['id'];
                        $invoicedProductsData['customer_level_no'] = $data['customer_level_no'];
                        $invoicedProductsData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                        $invoicedProductsData['customer_id'] = $data['customer_id'];
                        $invoicedProductsData['customer_type'] = $invoiceData['customer_type'];
                        $invoicedProductsData['invoice_date'] = $time;
                        $invoicedProductsData['delivery_date'] = strtotime($data['delivery_date']);

                        $invoicedProductsData['depot_level_no'] = $invoiceData['depot_level_no'];
                        $invoicedProductsData['depot_unit_global_id'] = $invoiceData['depot_unit_global_id'];
                        $invoicedProductsData['depot_id'] = $user['depot_id'];

                        $invoicedProductsData['product_id'] = $item_id;
                        $invoicedProductsData['product_quantity'] = $itemDetail['item_quantity'];
                        $invoicedProductsData['bonus_quantity'] = $itemDetail['item_bonus'];
                        $invoicedProductsData['instant_discount'] = $itemDetail['item_cash_discount'];
                        $invoicedProductsData['net_total'] = $itemDetail['item_net_total'];
                        $invoicedProductsData['due'] = $itemDetail['item_net_total'];
                        $invoicedProductsData['created_by'] = $user['id'];
                        $invoicedProductsData['created_date'] = $time;
                        $invoicedProducts = $this->InvoicedProducts->patchEntity($invoicedProducts, $invoicedProductsData);
                        $this->InvoicedProducts->save($invoicedProducts);
                    endforeach;

                    // PO status update
                    $pos = TableRegistry::get('pos');
                    $query = $pos->query();
                    $query->update()->set(['po_status' => array_flip(Configure::read('po_status'))['approved']])->where(['id' => $data['po_id']])->execute();
                    // PO Event status update
                    $poEvent = TableRegistry::get('po_events');
                    $query = $poEvent->query();
                    $query->update()->set(['is_action_taken' => 1])->where(['id' => $id])->execute();

                    // Event creation
                    if($invoiceCycleInfo['invoice_approved_at']==array_flip(Configure::read('invoice_approved_at'))['Not Needed']){
                        $poEvent = $this->PoEvents->newEntity();
                        $poEventData['reference_type'] = array_flip(Configure::read('po_event_reference_type'))['invoice'];
                        $poEventData['reference_id'] = $data['po_id'];
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

                $this->Flash->success('PO approval done successfully. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('PO approval not possible. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }
        $customers = $this->Customers->find('list', ['conditions' => ['unit_global_id'=>$event['po']['customer_unit_global_id']]]);

        $administrativeUnits = $this->AdministrativeUnits->find('list', ['conditions'=>['level_no'=>$event['po']['customer_level_no']]]);
        $customerAdministrativeUnitInfo = $this->AdministrativeUnits->find('all', ['conditions'=>['global_id'=>$event['po']['customer_unit_global_id']]])->first();
        $customerAdministrativeUnit = $customerAdministrativeUnitInfo['id'];

        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        $itemUnitPriceArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';

            if($event['po']['invoice_type']==1):
                $itemUnitPriceArray[$item['id']] = $item['cash_sales_price'];
            elseif($event['po']['invoice_type']==2):
                $itemUnitPriceArray[$item['id']] = $item['credit_sales_price'];
            endif;
        }
        $this->set(compact('itemUnitPriceArray', 'event', 'customers', 'administrativeLevels', 'itemArray', 'administrativeUnits', 'customerAdministrativeUnit'));
        $this->set('_serialize', ['event']);
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
       $item_id = $data['item_id'];
       $invoice_type = $data['invoice_type'];

       $this->loadModel('Items');
       $item = $this->Items->find('all', ['conditions' => ['id'=>$item_id, 'status' => 1]])->first()->toArray();

       if($invoice_type==1) {
           $unit_price = $item['cash_sales_price'];
       } elseif($invoice_type==2) {
           $unit_price = $item['credit_sales_price'];
       } else {
           $unit_price = 0;
       }

//       App::import('Helper', 'SystemHelper');
//       $SystemHelper = new SystemHelper(new View());
//       $offers = $SystemHelper->item_offers($item_id);

       $itemName = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';
       $this->viewBuilder()->layout('ajax');
       $this->set(compact('itemName', 'item_id', 'unit_price'));
   }
}
