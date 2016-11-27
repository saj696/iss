<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
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
        $time = time();
        $this->loadModel('Customers');
        $customer = $this->Customers->get($id, [
            'contain' => []
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            unset($data['user_group']);
            $data['credit_approval_date'] = strtotime($data['credit_approval_date']);
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;

            $approves = $this->Customers->patchEntity($customer, $data);
            if ($this->Customers->save($approves)) {
                $this->Flash->success(__('The customer credit approval has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The customer approval could not be saved. Please, try again.'));
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
