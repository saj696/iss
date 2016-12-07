<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * SalesForces Controller
 *
 * @property \App\Model\Table\SalesForcesTable $SalesForces
 */
class SalesForcesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'SalesForces.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $salesForces = $this->SalesForces->find('all', [
            'conditions' => ['SalesForces.status !=' => 99]
        ]);

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }

        $salesForces = $this->paginate($salesForces);

        $this->set(compact('salesForces', 'administrativeLevels'));
        $this->set('_serialize', ['salesForces']);
    }

    /**
     * View method
     *
     * @param string|null $id Task Force id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $salesForce = $this->SalesForces->get($id, [
            'contain' => []
        ]);
        $this->set('salesForce', $salesForce);
        $this->set('_serialize', ['salesForce']);
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
        $salesForce = $this->SalesForces->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $salesForce = $this->SalesForces->patchEntity($salesForce, $data);
            if ($this->SalesForces->save($salesForce)) {
                $this->Flash->success('The task force has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The task force could not be saved. Please, try again.');
            }
        }

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }

        $this->set(compact('salesForce', 'administrativeLevels'));
        $this->set('_serialize', ['salesForce']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Task Force id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $salesForce = $this->SalesForces->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $salesForce = $this->SalesForces->patchEntity($salesForce, $data);
            if ($this->SalesForces->save($salesForce)) {
                $this->Flash->success('The task force has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The task force could not be saved. Please, try again.');
            }
        }

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }

        $this->set(compact('salesForce', 'administrativeLevels'));
        $this->set('_serialize', ['salesForce']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Task Force id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $salesForce = $this->SalesForces->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $salesForce = $this->SalesForces->patchEntity($salesForce, $data);
        if ($this->SalesForces->save($salesForce)) {
            $this->Flash->success('The task force has been deleted.');
        } else {
            $this->Flash->error('The task force could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
