<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Chronos\Traits\ComparisonTrait;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;
use Cake\I18n\Date;

/**
 * CreditNoteItems Controller
 *
 * @property \App\Model\Table\CreditNoteItemsTable $CreditNoteItems
 */
class CreditNoteItemsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'CreditNoteItems.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index($credit_note_id)
    {
        $user = $this->Auth->user();
        $creditNoteItems = $this->CreditNoteItems->find('all', [
            'contain' => ['Invoices', 'Items', 'Units', 'CreditNoteCreators', 'CreditNotes', 'CreditNotes.Customers'],
            'conditions' => ['CreditNoteItems.status !=' => 99, 'CreditNoteItems.created_by' => $user['id'],
                'CreditNoteItems.credit_note_id' => $credit_note_id
            ]
        ]);
        $this->set('creditNoteItems', $this->paginate($creditNoteItems));
        $this->set('_serialize', ['creditNoteItems']);
    }


    public function createdCreditNotes()
    {
        $user = $this->Auth->user();
        $time = time();
        $send_for_approval = "";
        $this->loadModel('CreditNotes');
        $this->loadModel('CreditNoteEvents');
        $this->loadModel('CreditNoteItems');
        $created_credit_notes = $this->CreditNotes->find('all', [
            'contain' => ['CreditNoteCreators', 'Customers'],
            'conditions' => ['CreditNoteCreators.id' => $user['id']]
        ]);
        // pr($created_credit_notes->toArray());die;
        $this->set('created_credit_notes', $this->paginate($created_credit_notes));
        $this->set('_serialize', ['created_credit_notes']);
        $conn = ConnectionManager::get('default');

        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data['user'] = $user['id'];
            $data['created_time'] = $time;
            echo $data['credit_note_id'];
            echo $data['recipient_id'];
            $conn->transactional(function ($conn) use ($data, $time, $user) {
                $event = $this->CreditNoteEvents->newEntity();
                $event_data['credit_note_id'] = $data['credit_note_id'];
                $event_data['recipient_id'] = $data['recipient_id'];
                $event_data['is_action_taken'] = 1;
                $event_data['sender_id'] = $user['id'];
                $event_data['created_by'] = $user['id'];
                $event_data['created_date'] = $time;
                $event = $this->CreditNoteEvents->patchEntity($event, $event_data);
                $this->CreditNoteEvents->save($event);

                $credit_notes = TableRegistry::get('CreditNotes');
                $query = $credit_notes->query();
                $query->update()
                    ->set(['approval_status' => 2, 'updated_date' => $time, 'updated_by' => $user['id']])
                    ->where(['id' => $data['credit_note_id']])
                    ->execute();

            });

            return $this->redirect(['controller' => 'CreditNoteItems', 'action' => 'created_credit_notes']);
        }
        $recipient_list = [];
        $recipients = TableRegistry::get('users')->find('all')->where(['user_group_id' => 11, 'status' => 1]);
        foreach ($recipients as $recipient):
            $recipient_list[$recipient['id']] = $recipient['full_name_en'];
        endforeach;
        $this->set('recipient_list', $recipient_list);
        $this->set('send_for_approval', $send_for_approval);
    }

    // public functio

    /**
     * View method
     *
     * @param string|null $id Credit Note Item id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $creditNoteItem = $this->CreditNoteItems->get($id, [
            'contain' => ['Invoices', 'Items', 'Units']
        ]);
        $this->set('creditNoteItem', $creditNoteItem);
        $this->set('_serialize', ['creditNoteItem']);
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
        $creditNoteItem = "";
        $connection = ConnectionManager::get('default');

        if ($this->request->is('post')) {
            $data = $this->request->data;
            $this->loadModel('AdministrativeUnits');
            $this->loadModel('InvoicedProducts');
            $this->loadModel('CreditNoteEvents');
            $this->loadModel('CreditNotes');
            $this->loadModel('CreditNoteItems');

            $get_parent_global_id = $this->AdministrativeUnits->find('all', ['fields' => ['global_id', 'id'],
                'conditions' => ['id' => $data['parent_unit']]])->hydrate(false)->first();


            $credit_notes = $this->CreditNotes->newEntity();
            $credit_notes_data['customer_id'] = $data['customer'];
            $credit_notes_data['parent_global_id'] = $get_parent_global_id['global_id'];
            $credit_notes_data['date'] = strtotime(Date::now());
            $credit_notes_data['demurrage_percentage'] = Configure::read('credit_note_demurrage');

            $total_before_demurrage = 0;
            foreach ($data['CreditNoteItems'] as $key => $value):
                $total_before_demurrage += $value['net_total'];
            endforeach;
            $total_i = ($total_before_demurrage * $credit_notes_data['demurrage_percentage']) / 100;
            $total_after_demurrage = $total_i + $total_before_demurrage;
            $credit_notes_data['total_after_demurrage'] = $total_after_demurrage;

            if (isset($data['for_approval'])) {
                $credit_notes_data['approval_status'] = 2;
            } elseif (isset($data['for_save'])) {
                $credit_notes_data['approval_status'] = 1;
            }
            $credit_notes_data['created_date'] = $time;
            $credit_notes_data['created_by'] = $user['id'];
            $credit_notes = $this->CreditNotes->patchEntity($credit_notes, $credit_notes_data);

            if ($this->CreditNotes->save($credit_notes)) {
                $this->Flash->success('Credit Notes Saved');
            } else {
                $errors = $credit_notes->errors();
                foreach ($errors as $error):
                    foreach ($error as $er):
                        $this->Flash->error($er);
                    endforeach;
                endforeach;
                return true;
            }
            $connection->transactional(function ($connection) use ($data, $time, $user, $get_parent_global_id, $credit_notes) {
                $this->loadModel('CreditNotes');
                foreach ($data['CreditNoteItems'] as $key => $CreditNoteItems):
                    if (isset($data['for_approval'])) {
                        $credit_note_events = $this->CreditNoteEvents->newEntity();
                        $event_data['credit_note_id'] = $credit_notes->id;
                        $event_data['recipient_id'] = $data['recipient_id'];
                        $event_data['is_action_taken'] = 0;
                        $event_data['sender_id'] = $user['id'];
                        $event_data['created_date'] = $time;
                        $event_data['created_by'] = $user['id'];
                        $credit_note_events = $this->CreditNoteEvents->patchEntity($credit_note_events, $event_data);
                        if ($this->CreditNoteEvents->save($credit_note_events)) {
                            $this->Flash->success('Credit note event initiated');
                        } else {
                            $errors = $credit_note_events->errors();
                            foreach ($errors as $error):
                                foreach ($error as $er):
                                    $this->Flash->error($er);
                                endforeach;
                            endforeach;
                            return true;
                        }
                    }
                    $creditNoteItem = $this->CreditNoteItems->newEntity();
                    $credit_note_items_data['invoice_id'] = $CreditNoteItems['invoice_id'];
                    $credit_note_items_data['credit_note_id'] = $credit_notes->id;
                    $credit_note_items_data['item_id'] = $CreditNoteItems['item_id'];

                    $get_unit_id = $this->InvoicedProducts->find('all', ['contain' => [], 'conditions' => ['item_id' => $CreditNoteItems['item_id']],
                        'fields' => ['manufacture_unit_id',
                            'item_id'
                        ]])->hydrate(false)->first();
                    if (empty($get_unit_id)) {
                        return true;
                    }
                    $credit_note_items_data['manufacture_unit_id'] = $get_unit_id['manufacture_unit_id'];
                    $credit_note_items_data['quantity'] = $CreditNoteItems['quantity'];
                    $credit_note_items_data['net_total'] = $CreditNoteItems['net_total'];
                    $credit_note_items_data['created_date'] = $time;
                    $credit_note_items_data['created_by'] = $user['id'];
                    $creditNoteItem = $this->CreditNoteItems->patchEntity($creditNoteItem, $credit_note_items_data);
                    if ($this->CreditNoteItems->save($creditNoteItem)) {
                        $this->Flash->success('Credit Note Items Saved');
                    } else {
                        $errors = $credit_notes->errors();
                        foreach ($errors as $error):
                            foreach ($error as $er):
                                $this->Flash->error($er);
                            endforeach;
                        endforeach;
                        return true;
                    }
                endforeach;

            });
            return $this->redirect(['controller' => 'CreditNoteItems', 'action' => 'created_credit_notes']);
        }
        $parantsLevels = [];
        $this->loadModel('AdministrativeLevels');
        $parent_info = $this->AdministrativeLevels->find('all', ['fields' => ['level_name', 'level_no'], 'conditions' => ['status' => 1]])->toArray();
        foreach ($parent_info as $parent_info) {
            $parantsLevels[$parent_info['level_no']] = $parent_info['level_name'];
        }
        $this->set(compact('creditNoteItem', 'invoices', 'items', 'units', 'parantsLevels'));
        $this->set('_serialize', ['creditNoteItem']);
        $recipients = TableRegistry::get('users')->find('all')->where(['user_group_id' => 11, 'status' => 1]);
        $recipient_list = [];
        foreach ($recipients as $recipient):
            $recipient_list[$recipient['id']] = $recipient['full_name_en'];
        endforeach;
        $this->set('recipient_list', $recipient_list);
    }

    /**
     * Edit method
     *
     * @param string|null $id Credit Note Item id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $creditNoteItem = $this->CreditNoteItems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $creditNoteItem = $this->CreditNoteItems->patchEntity($creditNoteItem, $data);
            if ($this->CreditNoteItems->save($creditNoteItem)) {
                $this->Flash->success('The credit note item has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The credit note item could not be saved. Please, try again.');
            }
        }
        $invoices = $this->CreditNoteItems->Invoices->find('list', ['conditions' => ['status' => 1]]);
        $items = $this->CreditNoteItems->Items->find('list', ['conditions' => ['status' => 1]]);
        //$manufactureUnits = $this->CreditNoteItems->ManufactureUnits->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('creditNoteItem', 'invoices', 'items', 'manufactureUnits'));
        $this->set('_serialize', ['creditNoteItem']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Credit Note Item id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $creditNoteItem = $this->CreditNoteItems->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $creditNoteItem = $this->CreditNoteItems->patchEntity($creditNoteItem, $data);
        if ($this->CreditNoteItems->save($creditNoteItem)) {
            $this->Flash->success('The credit note item has been deleted.');
        } else {
            $this->Flash->error('The credit note item could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function ajax($param)
    {
        $this->autoRender = false;
        if ($param == "units"):
            $data = $this->request->data;
            $level = $data['level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields' => ['id', 'unit_name', 'global_id']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach ($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;

        elseif ($param == "customers"):
            $data = $this->request->data;
            $unit = $data['unit'];
            $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields' => ['id', 'name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach ($customers as $customer):
                $dropArray[$customer['id']] = $customer['name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
            return $this->response;

        elseif ($param == "invoices"):
            $data = $this->request->data;
            $invoice_id = $data['invoice_id'];
            $invoice = TableRegistry::get('invoiced_products')->find('all', ['contain' => ['Units', 'Items'], 'conditions' => ['invoice_id' => $invoice_id, 'invoiced_products.status' => 1],
                'fields' => ['items.id',
                    'items.name',
                    'units.unit_display_name'
                ]])->hydrate(false)->toArray();

            $dropArray = [];
            foreach ($invoice as $item_unit):
                $dropArray[$item_unit['items']['id']] = $item_unit['items']['name'] . '--' . $item_unit['units']['unit_display_name'];
            endforeach;
            $this->response->body(json_encode($dropArray));
        elseif ($param == "net_total"):
            $data = $this->request->data;
            $item_id = $data['item_id'];
            $quantity = $data['quantity'];
            $result = TableRegistry::get('invoiced_products')->find('all', ['contain' => [], 'conditions' => ['item_id' => $item_id, 'invoiced_products.status' => 1],
                'fields' => ['product_quantity',
                    'bonus_quantity',
                    'net_total'
                ]])->hydrate(false)->first();
            $get_avg_quantity = ($result['product_quantity'] + $result['bonus_quantity']) / 2;
            $rate = $result['net_total'] / $get_avg_quantity;
            $final_net_rate_for_given_quantity = $rate * $data['quantity'];
            $this->response->body(json_encode($final_net_rate_for_given_quantity));

        endif;
    }
}
