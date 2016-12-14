<?php
namespace App\Controller;

use  App\Controller\AppController;

use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query;

/**
 * CreditNotes Controller
 *
 * @property \App\Model\Table\CreditNotesTable $CreditNotes
 */
class CreditNotesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'CreditNotes.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        // $this->Common->pay_invoice_due(44, 490, 1, 1);
        $user = $this->Auth->user();
        $creditNotes = $this->CreditNotes->find('all', [
            'contain' => ['Customers', 'CreditNoteEvents.Senders',
                'CreditNoteEvents.Recipients' =>
                    function (Query $query) use ($user) {
                        return $query->where(['Recipients.id' => $user['id']]);
                    },
                'CreditNoteItems.Items', 'CreditNoteEvents', 'CreditNoteItems.Units'],
            'conditions' => ['CreditNotes.status !=' => 99]

        ]);


        //debug($creditNotes->toArray());die;

        //$this->set('recipient_id', $user['id']);
        $this->set('creditNotes', $this->paginate($creditNotes));
        $this->set('_serialize', ['creditNotes']);
    }

    /**
     * View method
     *
     * @param string|null $id Credit Note id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $creditNote = $this->CreditNotes->get($id, [
            'contain' => ['Customers', 'CreditNoteItems.Items', 'CreditNoteItems.Units', 'CreditNoteEvents']
        ]);
        $this->set('creditNote', $creditNote);
        $this->set('_serialize', ['creditNote']);
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
        $creditNote = $this->CreditNotes->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $creditNote = $this->CreditNotes->patchEntity($creditNote, $data);
            if ($this->CreditNotes->save($creditNote)) {
                $this->Flash->success('The credit note has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The credit note could not be saved. Please, try again.');
            }
        }
        //$parentGlobals = $this->CreditNotes->ParentGlobals->find('list', ['conditions'=>['status'=>1]]);
        $this->set(compact('creditNote', 'parentGlobals'));
        $this->set('_serialize', ['creditNote']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Credit Note id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $creditNote = $this->CreditNotes->get($id, [
            'contain' => ['Customers', 'CreditNoteItems.Items', 'CreditNoteItems.Units', 'CreditNoteEvents']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $conn = ConnectionManager::get('default');
            $data = $this->request->data;
            $data['date'] = strtotime($data['date']);
            $conn->transactional(function () use ($user, $creditNote, $time, $data) {
                unset($data['customer_id']);
                $data['updated_by'] = $user['id'];
                $data['updated_date'] = $time;
                if ($data['approval_status'] == 3) {
                    $credit_events = TableRegistry::get('CreditNoteEvents');
                    $query = $credit_events->query();
                    $query->update()
                        ->set(['is_action_taken' => 1, 'updated_date' => $time, 'updated_by' => $user['id']])
                        ->where(['credit_note_id' => $creditNote['id']])
                        ->execute();
                }
                $creditNote = $this->CreditNotes->patchEntity($creditNote, $data);
                if ($this->CreditNotes->save($creditNote)) {
                    //$customer_id
                    //$amount
                    //$payment_account code from account_heads tablehere it's Credit Note =>130000
                    $this->Common->pay_invoice_due($creditNote->customer_id, $creditNote->total_after_demurrage,130000);
                    $this->Flash->success('The credit note has been approved.');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('The credit note could not be approved. Please, try again.');
                }
            });
        }
        $this->set(compact('creditNote', 'parentGlobals'));
        $this->set('_serialize', ['creditNote']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Credit Note id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $creditNote = $this->CreditNotes->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $creditNote = $this->CreditNotes->patchEntity($creditNote, $data);
        if ($this->CreditNotes->save($creditNote)) {
            $this->Flash->success('The credit note has been deleted.');
        } else {
            $this->Flash->error('The credit note could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
