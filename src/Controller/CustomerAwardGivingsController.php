<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;

/**
 * CustomerAwardGivings Controller
 *
 * @property \App\Model\Table\CustomerAwardGivingsTable $CustomerAwardGivings
 */
class CustomerAwardGivingsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'CustomerAwardGivings.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $customerAwardGivings = $this->CustomerAwardGivings->find('all', [
            'conditions' => ['CustomerAwardGivings.status !=' => 99],
            'contain' => ['CustomerAwards', 'Customers', 'Awards']
        ]);
        $this->set('customerAwardGivings', $this->paginate($customerAwardGivings));
        $this->set('_serialize', ['customerAwardGivings']);
    }

    /**
     * View method
     *
     * @param string|null $id Customer Award Giving id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $customerAwardGiving = $this->CustomerAwardGivings->get($id, [
            'contain' => ['CustomerAwards', 'Customers', 'Awards']
        ]);
        $this->set('customerAwardGiving', $customerAwardGiving);
        $this->set('_serialize', ['customerAwardGiving']);
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
        $customerAwardGiving = $this->CustomerAwardGivings->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $customerAwardGiving = $this->CustomerAwardGivings->patchEntity($customerAwardGiving, $data);
            if ($this->CustomerAwardGivings->save($customerAwardGiving)) {
                $this->Flash->success('The customer award giving has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The customer award giving could not be saved. Please, try again.');
            }
        }

        $this->loadModel('AdministrativeLevels');
        $this->loadModel('account_heads');
        $parantsLevels = $this->AdministrativeLevels->find('list', ['keyField' => 'level_no', 'keyValue' => 'level_name', 'conditions' => ['status' => 1]]);
        $awardTypes = $this->account_heads->find('list', ['keyField' => 'code', 'keyValue' => 'name', 'conditions' => ['account_selector' => 1]]);

        //$customerAwards = $this->CustomerAwardGivings->CustomerAwards->find('list');
        //  $parentGlobals = $this->CustomerAwardGivings->ParentGlobals->find('list', ['conditions'=>['status'=>1]]);
        $awards = $this->CustomerAwardGivings->Awards->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('customerAwardGiving', 'parantsLevels', 'awardTypes', 'customerAwards', 'awards'));
        $this->set('_serialize', ['customerAwardGiving']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Award Giving id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $customerAwardGiving = $this->CustomerAwardGivings->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $customerAwardGiving = $this->CustomerAwardGivings->patchEntity($customerAwardGiving, $data);
            if ($this->CustomerAwardGivings->save($customerAwardGiving)) {
                $this->Flash->success('The customer award giving has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The customer award giving could not be saved. Please, try again.');
            }
        }
        $customerAwards = $this->CustomerAwardGivings->CustomerAwards->find('list', ['conditions' => ['status' => 1]]);
        $customers = $this->CustomerAwardGivings->Customers->find('list', ['conditions' => ['status' => 1]]);
        $parentGlobals = $this->CustomerAwardGivings->ParentGlobals->find('list', ['conditions' => ['status' => 1]]);
        $awards = $this->CustomerAwardGivings->Awards->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('customerAwardGiving', 'customerAwards', 'customers', 'parentGlobals', 'awards'));
        $this->set('_serialize', ['customerAwardGiving']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Award Giving id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $customerAwardGiving = $this->CustomerAwardGivings->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $customerAwardGiving = $this->CustomerAwardGivings->patchEntity($customerAwardGiving, $data);
        if ($this->CustomerAwardGivings->save($customerAwardGiving)) {
            $this->Flash->success('The customer award giving has been deleted.');
        } else {
            $this->Flash->error('The customer award giving could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }


    public function ajax($param)
    {
        if ($param == "units"):
            $data = $this->request->data;
            $level = $data['level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields' => ['id', 'unit_name']])->hydrate(false)->toArray();
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

        elseif ($param == "dueInvoice"):
            $data = $this->request->data;
            $customer = $data['customer'];
            $paymentBasis = TableRegistry::get('payment_basis')->find('all', ['conditions' => ['status' => 1], 'fields' => ['basis']])->first();
            if (($paymentBasis['basis']) == 1):
                $invoices = TableRegistry::get('invoices')->find('all', ['conditions' => ['customer_id' => $customer], 'fields' => ['id', 'net_total', 'due', 'invoice_date'], 'limit' => 25, 'order' => ['invoices.id ASC']])->hydrate(false)->toArray();
                $invoiceArray = [];
                foreach ($invoices as $invoice):
                    $invoiceArray[$invoice['id']] = 'Invoice Date :' . ' ' . date('d-m-y', $invoice['invoice_date']) . ', Net Total :' . ' ' . $invoice['net_total'] . ', Due :' . ' ' . $invoice['due'];
                endforeach;
                $this->response->body(json_encode($invoiceArray));
                return $this->response;

            else:
                $invoices = TableRegistry::get('invoices')->find('all', ['conditions' => ['customer_id' => $customer], 'fields' => ['id', 'net_total', 'due', 'invoice_date']])->hydrate(false)->toArray();
                $invoiceArray = [];
                foreach ($invoices as $invoice):
                    $invoiceArray[$invoice['id']] = 'Invoice Date :' . ' ' . date('d-m-y', $invoice['invoice_date']) . ', Net Total :' . ' ' . $invoice['net_total'] . ', Due :' . ' ' . $invoice['due'];
                endforeach;
                $this->response->body(json_encode($invoiceArray));
                return $this->response;
            endif;

        elseif ($param == "paymentTable"):
            $data = $this->request->data;
            $dueInvoice = $data['dueInvoice'];
            $invoiceTables = TableRegistry::get('invoices')->find('all', ['conditions' => ['id' => $dueInvoice], 'fields' => ['id', 'net_total', 'due', 'invoice_date']])->hydrate(false)->toArray();

            $dropArray = [];
            foreach ($invoiceTables as $invoiceTable):
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


    public function getCustomerAwards()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->viewBuilder()->layout('ajax');
            $data = $this->request->data;
            // echo "<pre>";print_r($data);die();
            $customer_awards = TableRegistry::get('customer_awards')->find('all',
                ['contain' => ['Awards', 'CustomerOffers'],
                    'conditions' => ['customer_id' => $data['customer_id'], 'award_account_code' => $data['award_account_code'], 'action_status !=' => Configure::read('customer_award_status.delivered')]
                ])
                ->hydrate(false)
                ->toArray();
            //echo "<pre>";print_r($customer_awards);die();

            $this->set(compact('customer_awards'));


        }
    }

    public function deliverAward($id = null)
    {
        $user = $this->Auth->user();
        $time = time();

        $customer_awards = TableRegistry::get('customer_awards');

        $query = $customer_awards->query();
        $val = $query->update()
            ->set(['action_status' => Configure::read('customer_award_status.delivered'), 'remaining_amount' => 0, 'updated_by' => $user['id'], 'updated_date' => $time])
            ->where(['id' => $id])
            ->execute();

        if ($val) {
            $customer_awards = $customer_awards->get($id);


            $customer_award_givings = $this->CustomerAwardGivings->newEntity();
            $data = [];
            $data['customer_award_id'] = $customer_awards->id;
            $data['customer_id'] = $customer_awards->customer_id;
            $data['parent_global_id'] = $customer_awards->parent_global_id;
            $data['award_account_code'] = $customer_awards->award_account_code;
            $data['award_id'] = $customer_awards->award_id;
            $data['amount'] = $customer_awards->remaining_amount;
            $data['giving_mode'] = Configure::read('customer_award_giving_status.delivered');
            $data['award_giving_date'] = $time;
            $data['status'] = 1;
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $customer_award_givings = $this->CustomerAwardGivings->patchEntity($customer_award_givings, $data);
            //  echo "<pre>";print_r($customer_award_givings);die();
            if ($this->CustomerAwardGivings->save($customer_award_givings)) {
                $this->Flash->success('The customer award giving has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The customer award giving could not be saved. Please, try again.');
            }

//            echo "<pre>";print_r($customer_awards);die();
//            $this->Flash->success('The customer award giving has been deleted.');
        } else {
            $this->Flash->error('The customer award giving could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function adjustment()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $connection = ConnectionManager::get('default');
            $user = $this->Auth->user();
            $time = time();

            $customer_awards = TableRegistry::get('customer_awards')->get($data['id']);
       //   pr($customer_awards);die();

            if($data['amount'] > $customer_awards->remaining_amount){
                $this->Flash->error('Given amount is large then remaining amount. Please, try again.');
                return $this->redirect(['action' => 'index']);
            }


            $remaining_amount=$customer_awards->remaining_amount - $data['amount'];
            if ($remaining_amount > 0) {
                $status = Configure::read('customer_award_status.partially-adjusted');
            } else {
                $status = Configure::read('customer_award_status.fully_adjusted');
            }
            $connection->transactional(function ($connection)
            use ($time, $user,$data,$remaining_amount,$status,$customer_awards) {
                $this->Common->pay_invoice_due($customer_awards->customer_id,$data['amount'],$customer_awards->award_account_code);

                $query = TableRegistry::get('customer_awards')->query();
                $err = $query->update()
                    ->set(['action_status' => $status, 'remaining_amount' =>$remaining_amount, 'updated_by' => $user['id'], 'updated_date' => $time])
                    ->where(['id' => $data['id']])
                    ->execute();

                if ($err) {
                    // $this->CustomerAwardGivings($customer_awards->customer_id,$data['amount'],$customer_awards->award_account_code);
                    $customer_award_givings = $this->CustomerAwardGivings->newEntity();
                    $val = [];
                    $val['customer_award_id'] = $customer_awards->id;
                    $val['customer_id'] = $customer_awards->customer_id;
                    $val['parent_global_id'] = $customer_awards->parent_global_id;
                    $val['award_account_code'] = $customer_awards->award_account_code;
                    $val['award_id'] = $customer_awards->award_id;
                    $val['amount'] = $data['amount'];
                    $val['giving_mode'] = Configure::read('customer_award_giving_status.adjustment');
                    $val['award_giving_date'] = $time;
                    $val['status'] = 1;
                    $val['created_by'] = $user['id'];
                    $val['created_date'] = $time;
                    $customer_award_givings = $this->CustomerAwardGivings->patchEntity($customer_award_givings, $val);
                    //  echo "<pre>";print_r($customer_award_givings);die();
                    if ($this->CustomerAwardGivings->save($customer_award_givings)) {
                        $this->Flash->success('The customer award adjustment has been saved.');
                        return $this->redirect(['action' => 'index']);
                    } else {
                        $this->Flash->error('The customer award adjustment could not be saved. Please, try again.');
                    }

//            echo "<pre>";print_r($customer_awards);die();
//            $this->Flash->success('The customer award giving has been deleted.');
                } else {
                    $this->Flash->error('The customer award adjustment could not be deleted. Please, try again.');
                }
            });

            return $this->redirect(['action' => 'index']);
        }
    }
}
