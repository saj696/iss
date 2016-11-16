<?php
namespace App\Controller;

use App\Controller\AppController;

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
            'InvoiceChalans.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $invoiceChalans = $this->InvoiceChalans->find('all', [
            'conditions' => ['InvoiceChalans.status !=' => 99]
        ]);
        $this->set('invoiceChalans', $this->paginate($invoiceChalans));
        $this->set('_serialize', ['invoiceChalans']);
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
        $invoiceChalan = $this->InvoiceChalans->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
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
}
