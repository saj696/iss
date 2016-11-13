<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * InvoiceCycleConfigurations Controller
 *
 * @property \App\Model\Table\InvoiceCycleConfigurationsTable $InvoiceCycleConfigurations
 */
class InvoiceCycleConfigurationsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'InvoiceCycleConfigurations.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $invoiceCycleConfigurations = $this->InvoiceCycleConfigurations->find('all', [
            'conditions' => ['InvoiceCycleConfigurations.status !=' => 99]
        ]);

        $invoiceCycleConfigurations = $this->paginate($invoiceCycleConfigurations);
        $this->loadModel('UserGroups');
        $userGroups = $this->UserGroups->find('list', ['conditions'=>['status !='=>99]])->toArray();
        $this->set(compact('invoiceCycleConfigurations', 'userGroups'));
        $this->set('_serialize', ['invoiceCycleConfigurations']);
    }

    /**
     * View method
     *
     * @param string|null $id Invoice Cycle Configuration id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->get($id, [
            'contain' => []
        ]);
        $this->set('invoiceCycleConfiguration', $invoiceCycleConfiguration);
        $this->set('_serialize', ['invoiceCycleConfiguration']);
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
        $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->patchEntity($invoiceCycleConfiguration, $data);
            if ($this->InvoiceCycleConfigurations->save($invoiceCycleConfiguration)) {
                $this->Flash->success('The invoice cycle configuration has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The invoice cycle configuration could not be saved. Please, try again.');
            }
        }

        $this->loadModel('UserGroups');
        $userGroups = $this->UserGroups->find('list', ['conditions'=>['status !='=>99]]);
        $this->set(compact('invoiceCycleConfiguration', 'userGroups'));
        $this->set('_serialize', ['invoiceCycleConfiguration']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Invoice Cycle Configuration id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->patchEntity($invoiceCycleConfiguration, $data);
            if ($this->InvoiceCycleConfigurations->save($invoiceCycleConfiguration)) {
                $this->Flash->success('The invoice cycle configuration has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The invoice cycle configuration could not be saved. Please, try again.');
            }
        }

        $this->loadModel('UserGroups');
        $userGroups = $this->UserGroups->find('list', ['conditions'=>['status !='=>99]]);
        $this->set(compact('invoiceCycleConfiguration', 'userGroups'));
        $this->set('_serialize', ['invoiceCycleConfiguration']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Invoice Cycle Configuration id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $invoiceCycleConfiguration = $this->InvoiceCycleConfigurations->patchEntity($invoiceCycleConfiguration, $data);
        if ($this->InvoiceCycleConfigurations->save($invoiceCycleConfiguration)) {
            $this->Flash->success('The invoice cycle configuration has been deleted.');
        } else {
            $this->Flash->error('The invoice cycle configuration could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
