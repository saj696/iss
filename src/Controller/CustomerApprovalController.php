<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;


class CustomerApprovalController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $user = $this->Auth->user();
        $this->loadModel('Customers');

        $customers = $this->Customers->find('all',[
            'conditions' => ['Customers.status !=' => 99],
            'contain'=>['AdministrativeUnits']
        ]);

        $customers = $this->paginate($customers);
        $this->set(compact('customers'));
        $this->set('_serialize', ['customers']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Approval id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $this->loadModel('BankInformations');
        $this->loadModel('CustomerCreditLimits');
        $time = time();
        $this->loadModel('Customers');
        $customer = $this->Customers->get($id, [
            'contain' => ['BankInformations','CustomerCreditLimits']
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {

            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($customer, $id, $user, $time, &$saveStatus) {
                    $data = $this->request->data;
                    unset($data['user_group']);
                    $data['credit_approval_date'] = strtotime($data['credit_approval_date']);
                    $data['updated_by'] = $user['id'];
                    $data['updated_date'] = $time;

                    $creditLimits = $this->CustomerCreditLimits->newEntity();
                    $creditLimitsData['customer_id'] = $id;
                    $creditLimitsData['credit_limit'] = $data['credit_limit'];
                    $creditLimitsData['increase_credit_limit'] = $data['increase_credit_limit'];
                    $creditLimitsData['status'] = 1;
                    $creditLimitsData['created_by'] = $user['id'];
                    $creditLimitsData['created_date'] = $time;
                    $creditLimits = $this->CustomerCreditLimits->patchEntity($creditLimits, $creditLimitsData);
                    $this->CustomerCreditLimits->save($creditLimits);

                    foreach ($data['bank_informations'] as &$information):
                        $information['status'] = 1;
                        $information['updated_by'] = $user['id'];
                        $information['updated_date'] = $time;
                    endforeach;

                    $approves = $this->Customers->patchEntity($customer, $data, ['associated' => ['BankInformations', 'CustomerCreditLimits']]);
                    $this->Customers->save($approves);
                });

                $this->Flash->success('Customer Approve Successful');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Customer Approve Unsuccessful');
                return $this->redirect(['action' => 'index']);
            }
        }
        $this->loadModel('UserGroups');
        $userGroups = $this->UserGroups->find('list', ['conditions'=>['status'=>1]]);
        $this->set(compact('customer', 'userGroups'));
        $this->set('_serialize', ['customer']);
    }

    public function approve($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $this->loadModel('Customers');
        $approves = $this->Customers->get($id, [
            'contain' => []
        ]);

        $approves = $this->Customers->patchEntity($approves, $this->request->data);
        $approves['business_type'] = array_flip(Configure::read('customer_business_types'))['cash'];
        $approves['status'] = 1;
        $approves['opening_balance'] = 0;
        $approves['cash_approved_by'] = $user['id'];
        $approves['cash_approval_date'] = $time;

        if ($this->Customers->save($approves)) {
            $this->Flash->success(__('The customer approved.'));
        } else {
            $this->Flash->error(__('The customer not approved, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function ajax()
    {
        $data = $this->request->data;
        $user_group = $data['user_group'];
        $dropArray = TableRegistry::get('users')->find('list', ['conditions' => ['user_group_id' => $user_group]])->hydrate(false)->toArray();

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

}
