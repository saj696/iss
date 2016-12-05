<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * CreditNoteEvents Controller
 *
 * @property \App\Model\Table\CreditNoteEventsTable $CreditNoteEvents
 */
class CreditNoteEventsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'CreditNoteEvents.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $creditNoteEvents = $this->CreditNoteEvents->find('all', [
            'conditions' => ['CreditNoteEvents.status !=' => 99],
            'contain' => ['CreditNotes','Recipients','Senders']
        ]);
        $this->set('creditNoteEvents', $this->paginate($creditNoteEvents));
        $this->set('_serialize', ['creditNoteEvents']);
    }

    /**
     * View method
     *
     * @param string|null $id Credit Note Event id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $creditNoteEvent = $this->CreditNoteEvents->get($id, [
            'contain' => ['CreditNotes']
        ]);
        $this->set('creditNoteEvent', $creditNoteEvent);
        $this->set('_serialize', ['creditNoteEvent']);
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
        $creditNoteEvent = $this->CreditNoteEvents->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $creditNoteEvent = $this->CreditNoteEvents->patchEntity($creditNoteEvent, $data);
            if ($this->CreditNoteEvents->save($creditNoteEvent)) {
                $this->Flash->success('The credit note event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The credit note event could not be saved. Please, try again.');
            }
        }
        $creditNotes = $this->CreditNoteEvents->CreditNotes->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('creditNoteEvent', 'creditNotes'));
        $this->set('_serialize', ['creditNoteEvent']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Credit Note Event id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $creditNoteEvent = $this->CreditNoteEvents->get($id, [
            'contain' => ['CreditNotes.Customers']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $creditNoteEvent = $this->CreditNoteEvents->patchEntity($creditNoteEvent, $data);
            if ($this->CreditNoteEvents->save($creditNoteEvent)) {
                $this->Flash->success('The credit note event has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The credit note event could not be saved. Please, try again.');
            }
        }
        $creditNotes = $this->CreditNoteEvents->CreditNotes->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('creditNoteEvent', 'creditNotes'));
        $this->set('_serialize', ['creditNoteEvent']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Credit Note Event id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $creditNoteEvent = $this->CreditNoteEvents->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $creditNoteEvent = $this->CreditNoteEvents->patchEntity($creditNoteEvent, $data);
        if ($this->CreditNoteEvents->save($creditNoteEvent)) {
            $this->Flash->success('The credit note event has been deleted.');
        } else {
            $this->Flash->error('The credit note event could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
