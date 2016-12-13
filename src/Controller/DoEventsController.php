<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * DoEvents Controller
 *
 * @property \App\Model\Table\DoEventsTable $DoEvents
 */
class DoEventsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'DoEvents.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $doEvents = $this->DoEvents->find('all', [
         //   'conditions' => ['DoEvents.status !=' => 99],
            'contain' => ['Senders', 'DoObjects']
        ]);
      //  echo "<pre>";print_r($doEvents->toArray());die();
        $this->set('doEvents', $this->paginate($doEvents));
        $this->set('_serialize', ['doEvents']);
    }

    /**
     * View method
     *
     * @param string|null $id Do Event id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $doEvent = $this->DoEvents->get($id, [
            'contain' => ['Senders', 'Recipients', 'DoObjects']
        ]);
        $this->set('doEvent', $doEvent);
        $this->set('_serialize', ['doEvent']);
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
        $doEvent = $this->DoEvents->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
            if ($this->DoEvents->save($doEvent)) {
                $this->Flash->success('The do event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do event could not be saved. Please, try again.');
            }
        }
        $senders = $this->DoEvents->Senders->find('list', ['conditions' => ['status' => 1]]);
        $recipients = $this->DoEvents->Recipients->find('list', ['conditions' => ['status' => 1]]);
        $doObjects = $this->DoEvents->DoObjects->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('doEvent', 'senders', 'recipients', 'doObjects'));
        $this->set('_serialize', ['doEvent']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Do Event id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $doEvent = $this->DoEvents->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
            if ($this->DoEvents->save($doEvent)) {
                $this->Flash->success('The do event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do event could not be saved. Please, try again.');
            }
        }
        $senders = $this->DoEvents->Senders->find('list', ['conditions' => ['status' => 1]]);
        $recipients = $this->DoEvents->Recipients->find('list', ['conditions' => ['status' => 1]]);
        $doObjects = $this->DoEvents->DoObjects->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('doEvent', 'senders', 'recipients', 'doObjects'));
        $this->set('_serialize', ['doEvent']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Do Event id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $doEvent = $this->DoEvents->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $doEvent = $this->DoEvents->patchEntity($doEvent, $data);
        if ($this->DoEvents->save($doEvent)) {
            $this->Flash->success('The do event has been deleted.');
        } else {
            $this->Flash->error('The do event could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
