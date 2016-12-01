<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * TaskForces Controller
 *
 * @property \App\Model\Table\TaskForcesTable $TaskForces
 */
class TaskForcesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'TaskForces.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $taskForces = $this->TaskForces->find('all', [
            'conditions' => ['TaskForces.status !=' => 99]
        ]);

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }

        $taskForces = $this->paginate($taskForces);

        $this->set(compact('taskForces', 'administrativeLevels'));
        $this->set('_serialize', ['taskForces']);
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
        $taskForce = $this->TaskForces->get($id, [
            'contain' => []
        ]);
        $this->set('taskForce', $taskForce);
        $this->set('_serialize', ['taskForce']);
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
        $taskForce = $this->TaskForces->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $taskForce = $this->TaskForces->patchEntity($taskForce, $data);
            if ($this->TaskForces->save($taskForce)) {
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

        $this->set(compact('taskForce', 'administrativeLevels'));
        $this->set('_serialize', ['taskForce']);
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
        $taskForce = $this->TaskForces->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $taskForce = $this->TaskForces->patchEntity($taskForce, $data);
            if ($this->TaskForces->save($taskForce)) {
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

        $this->set(compact('taskForce', 'administrativeLevels'));
        $this->set('_serialize', ['taskForce']);
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

        $taskForce = $this->TaskForces->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $taskForce = $this->TaskForces->patchEntity($taskForce, $data);
        if ($this->TaskForces->save($taskForce)) {
            $this->Flash->success('The task force has been deleted.');
        } else {
            $this->Flash->error('The task force could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
