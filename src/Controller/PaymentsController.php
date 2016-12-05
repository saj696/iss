<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * Payments Controller
 *
 * @property \App\Model\Table\PaymentsTable $Payments
 */
class PaymentsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Payments.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $payments = $this->Payments->find('all', [
            'contain' => ['Customers'],
            'conditions' =>['Payments.status !=' => 99]
        ]);
        $this->set('payments', $this->paginate($payments) );
        $this->set('_serialize', ['payments']);
    }

    /**
     * View method
     *
     * @param string|null $id Payment id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user=$this->Auth->user();
        $payment = $this->Payments->get($id, [
            'contain' => []
        ]);
        $this->set('payment', $payment);
        $this->set('_serialize', ['payment']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user=$this->Auth->user();
        $time=time();
        $payment = $this->Payments->newEntity();
        if ($this->request->is('post')){
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($payment, $user, $time, &$saveStatus)
                {
                    $data=$this->request->data;
                    $this->loadModel('InvoicePayments');
                    $this->loadModel('Customers');
                    $this->loadModel('Invoices');
                    $this->loadModel('InvoicedProducts');
                    $this->loadModel('InvoicedProductsPayments');
                    $data['created_by']=$user['id'];
                    $data['created_date']=$time;
                    $data['collection_date'] = strtotime($data['collection_date']);

//          insert payments table
                    $customerInfo = $this->Customers->find('all',['conditions'=> ['id' => $data['customer_id']], 'fields' =>['unit_global_id','customer_type']])->hydrate(false)->first();
                    $data['customer_type'] = $customerInfo['customer_type'];
                    $data['parent_global_id'] = $customerInfo['unit_global_id'];
                    $payment = $this->Payments->patchEntity($payment, $data);
                    $invoiceDatas = $this->Payments->save($payment);
                    $amount = $data['amount'];
//          insert invoice payments table
                    foreach($invoiceDatas['invoice_details'] as $invoiceDataID => $invoiceDataDetails):
                        $invoicePayments = $this->InvoicePayments->newEntity();
                        $invoiceData['customer_type'] = $data['customer_type'];
                        $invoiceData['customer_id'] = $data['customer_id'];
                        $invoiceData['parent_global_id'] = $data['parent_global_id'];
                        $invoiceData['invoice_id'] = $invoiceDataID;
                        $invoiceData['invoice_date'] = $invoiceDataDetails['invoice_date'];
                        $invoicesUpdate = $this->Invoices->get($invoiceDataID);
                        $invoiceData['invoice_delivery_date'] = $invoicesUpdate['delivery_date'];
                        $invoiceData['payment_id'] = $invoiceDatas['id'];
                        $invoiceData['payment_collection_date'] = $invoiceDatas['collection_date'];
                        $invoiceData['invoice_wise_payment_amount'] = $invoiceDataDetails['current_payment'];
                        $invoiceData['created_by'] = $user['id'];
                        $invoiceData['created_date'] = $time;
                        $invoiceData['status'] = 1;
                        if($invoiceData['invoice_wise_payment_amount']):
                            $invoicePayments = $this->InvoicePayments->patchEntity($invoicePayments, $invoiceData);
                            $this->InvoicePayments->save($invoicePayments);
                        endif;

//              update invoices table due
                        $invoicesUpdate->due  = $invoicesUpdate['due'] - $invoiceData['invoice_wise_payment_amount'];
                        $this->Invoices->save($invoicesUpdate);
//              update invoiced products table product wise due
                        $invoicedProducts = $this->InvoicedProducts->find('all',['conditions' => ['invoice_id' => $invoiceDataID], 'fields' => ['id','due']])->hydrate(false)->toArray();
//              Create a key value pair array
                        $arangedArr=[];
                        foreach($invoicedProducts as $invoicedProduct):
                            $arangedArr[$invoicedProduct['id']] = $invoicedProduct['due'];
                        endforeach;
//              Condition check for update
                        $var = false;
                        foreach($arangedArr as $id => $due):
                            if($amount>0):
                                if($amount>=$due):
                                    if($due>0):
                                        $var = $due - $amount;
                                        if($var<0):
                                            $var = 0;
                                        else:
                                            $var = $var;
                                        endif;
                                    else:
                                        $var = 0;
                                    endif;
                                else:
                                    $var = $due - $amount;
                                endif;
                                $amount = $amount - $due;
                            endif;
                            if($var !== false){
                                $invoicedProductsUpdate  = $this->InvoicedProducts->get($id);
                                $itemID = $invoicedProductsUpdate['item_id'];
                                $manufactureID = $invoicedProductsUpdate['manufacture_unit_id'];
                                $tempOne = $invoicedProductsUpdate['due'];
                                $invoicedProductsUpdate->due = $var;
                                $tempTwo = $this->InvoicedProducts->save($invoicedProductsUpdate);
                                $temp = $tempOne - $tempTwo['due'];
                                $var=false;

//                              insert invoice product payments table
                                if($temp):
                                    $inProPay = $this->InvoicedProductsPayments->newEntity();
                                    $inProPayData['customer_type'] = $data['customer_type'];
                                    $inProPayData['customer_id'] = $data['customer_id'];
                                    $inProPayData['parent_global_id'] = $customerInfo['unit_global_id'];
                                    $inProPayData['invoice_id'] = $invoiceDataID;
                                    $inProPayData['item_id'] = $itemID;
                                    $inProPayData['manufacture_unit_id'] = $manufactureID;
                                    $inProPayData['invoice_delivery_date'] = $invoicesUpdate['delivery_date'];
                                    $inProPayData['invoice_payment_id'] = $invoicePayments['id'];
                                    $inProPayData['payment_collection_date'] = $invoiceData['payment_collection_date'];
                                    $inProPayData['item_wise_payment_amount'] = $temp;
                                    $inProPayData['status'] = 1;
                                    $inProPayData['created_by'] = $user['id'];;
                                    $inProPayData['created_date'] = $time;
                                    $inProPay = $this->InvoicedProductsPayments->patchEntity($inProPay, $inProPayData);
                                    $this->InvoicedProductsPayments->save($inProPay);
                                endif;
                            }
                        endforeach;
                    endforeach;
                });

                $this->Flash->success('Payment Successful');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Payment Unsuccessful');
                return $this->redirect(['action' => 'index']);
            }
        }
//        Administrative levels
        $this->loadModel('AdministrativeLevels');
        $parantsLevels= [];
        $parentDatas = $this->AdministrativeLevels->find('all',['fields' => ['level_name', 'level_no'],'conditions'=>['status' => 1]])->toArray();
        foreach($parentDatas as $parentData)
        {
            $parantsLevels[$parentData['level_no']] = $parentData['level_name'];
        }
        $this->set(compact('payment','parantsLevels'));
        $this->set('_serialize', ['payment']);

    }

    /**
     * Edit method
     *
     * @param string|null $id Payment id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user=$this->Auth->user();
        $time=time();
        $payment = $this->Payments->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $data=$this->request->data;
            $data['update_by']=$user['id'];
            $data['update_date']=$time;
            $payment = $this->Payments->patchEntity($payment, $data);
            if ($this->Payments->save($payment))
            {
                $this->Flash->success('The payment has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The payment could not be saved. Please, try again.');
            }
        }
        $this->set(compact('payment'));
        $this->set('_serialize', ['payment']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Payment id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $payment = $this->Payments->get($id);
        $user=$this->Auth->user();
        $data=$this->request->data;
        $data['updated_by']=$user['id'];
        $data['updated_date']=time();
        $data['status']=99;
        $payment = $this->Payments->patchEntity($payment, $data);
        if ($this->Payments->save($payment))
        {
            $this->Flash->success('The payment has been deleted.');
        }
        else
        {
            $this->Flash->error('The payment could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function ajax($param)
    {
        if($param == "units"):
            $data = $this->request->data;
            $level = $data['level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;

        elseif($param == "customers"):
            $data = $this->request->data;
            $unit = $data['unit'];
            $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields'=>['id', 'name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach($customers as $customer):
                $dropArray[$customer['id']] = $customer['name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;

        elseif($param == "dueInvoice"):
            $data = $this->request->data;
            $customer = $data['customer'];
            $paymentBasis = TableRegistry::get('payment_basis')->find('all', ['conditions' => ['status' => 1], 'fields'=>['basis']])->first();
            if(($paymentBasis['basis']) == 1):
//                table payment basis and condition check customer id and due greater than zero
                $invoices = TableRegistry::get('invoices')->find('all',['conditions' => ['customer_id' => $customer, 'due >' =>0 ], 'fields' => ['id','net_total','due','invoice_date'], 'limit'=>25, 'order' => ['invoices.id ASC'] ])->hydrate(false)->toArray();
                $invoiceArray = [];
                foreach($invoices as $invoice):
                    $invoiceArray[$invoice['id']] = 'Invoice Date :'.' '.date('d-m-y',$invoice['invoice_date']).', Net Total :'. ' '.$invoice['net_total'].', Due :'.' '.$invoice['due'];
                endforeach;
                $this->response->body(json_encode($invoiceArray));
                return $this->response;

            else:
                $invoices = TableRegistry::get('invoices')->find('all',['conditions' => ['customer_id' => $customer], 'fields' => ['id','net_total','due','invoice_date']])->hydrate(false)->toArray();
                $invoiceArray = [];
                foreach($invoices as $invoice):
                    $invoiceArray[$invoice['id']] = 'Invoice Date :'.' '.date('d-m-y',$invoice['invoice_date']).', Net Total :'. ' '.$invoice['net_total'].', Due :'.' '.$invoice['due'];
                endforeach;
                $this->response->body(json_encode($invoiceArray));
                return $this->response;
            endif;

        elseif($param == "paymentTable"):
            $data = $this->request->data;
            $dueInvoice = $data['dueInvoice'];
            $invoiceTables = TableRegistry::get('invoices')->find('all',['conditions' => ['id' => $dueInvoice], 'fields' => ['id','net_total','due','invoice_date'] ])->hydrate(false)->toArray();

            $dropArray = [];
            foreach($invoiceTables as $invoiceTable):
                $dropArray['id'] = $invoiceTable['id'];
                $dropArray['net_total'] = $invoiceTable['net_total'];
                $dropArray['due'] = $invoiceTable['due'];
                $dropArray['invoice_date'] = $invoiceTable['invoice_date'];
            endforeach;
            $this->viewBuilder()->layout('ajax');
            $this->set(compact('dropArray'));

        else:

        endif;
    }
}

