<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

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
        $pos = $this->Pos->find('all', [
            'conditions' => ['Pos.status !=' => 99],
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

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $po = $this->Pos->patchEntity($po, $data);
            if ($this->Pos->save($po)) {
                $this->Flash->success('The po has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The po could not be saved. Please, try again.');
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
        $this->set(compact('po', 'customers', 'administrativeLevels'));
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
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
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
}
