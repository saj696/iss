<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * DoObjects Controller
 *
 * @property \App\Model\Table\DoObjectsTable $DoObjects
 */
class DoObjectsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'DoObjects.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = $this->Auth->user();

        $doObjects = $this->DoObjects->find('all', [
            'conditions' => ['DoObjects.action_status !=' => 99,'DoObjects.created_by'=>$user['id'] ]
//	'contain' => ['Targets']
        ]);
        $this->set('doObjects', $this->paginate($doObjects));
        $this->set('_serialize', ['doObjects']);
    }

    /**
     * View method
     *
     * @param string|null $id Do Object id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $this->loadModel('do_events');

            $do_objects = TableRegistry::get('do_objects');
            $query = $do_objects->query();
            $query->update()->set(['action_status' =>  Configure::read('do_object_action_status')['Save_and_Send']])->where(['id' => $id])->execute();


            $do_events = $this->do_events->newEntity();
            $event_data = [];
            $event_data['sender_id'] = $user['id'];
            $event_data['recipient_id'] = $data['recipient_id'];
            $event_data['do_object_id'] = $id;
            $event_data['events_tepe'] = Configure::read('object_type')['PI'];
            $event_data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_approval'];
            $event_data['created_by'] = $user['id'];
            $event_data['created_date'] = time();
            $do_events = $this->do_events->patchEntity($do_events, $event_data);

            if ($this->do_events->save($do_events)) {

                $this->Flash->success('The do object has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do object could not be saved. Please, try again.');
            }
          //  echo "<pre>";print_r($data);die();
        }
        $doObject = $this->DoObjects->get($id);

        $recipients = TableRegistry::get('users')->find('list', ['conditions' => ['user_group_id' => 11]]);

        $this->set('id', $id);
        $this->set('doObject', $doObject);
        $this->set('recipients', $recipients);
        $this->set('_serialize', ['doObject']);
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
        $doObject = $this->DoObjects->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;


            $this->loadModel('do_events');
            // Serials Table Insert/ update
            $this->loadModel('Serials');
            $serial_for = array_flip(Configure::read('serial_types'))['do_object'];
            $year = date('Y');

            if ($user['user_group_id'] == Configure::read('depot_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['depot'];
                $trigger_id = $user['depot_id'];
            elseif ($user['user_group_id'] == Configure::read('warehouse_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['warehouse'];
                $trigger_id = $user['warehouse_id'];
            else:
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['others'];
                $trigger_id = $user['administrative_unit_id'];
            endif;

            $existence = $this->Serials->find('all', ['conditions' => ['serial_for' => $serial_for, 'year' => $year, 'trigger_type' => $trigger_type, 'trigger_id' => $trigger_id]])->first();
            $serial_no = null;
            if ($existence) {
                $serial = TableRegistry::get('serials');
                $query = $serial->query();
                $query->update()->set(['serial_no' => $existence['serial_no'] + 1])->where(['id' => $existence['id']])->execute();
                $serial_no = $existence['serial_no'] + 1;
            } else {
                $serial = $this->Serials->newEntity();
                $serialData['trigger_type'] = $trigger_type;
                $serialData['trigger_id'] = $trigger_id;
                $serialData['serial_for'] = $serial_for;
                $serialData['year'] = $year;
                $serialData['serial_no'] = 1;
                $serialData['created_by'] = $user['id'];
                $serialData['created_date'] = $time;
                $serial = $this->Serials->patchEntity($serial, $serialData);
                $this->Serials->save($serial);
                $serial_no = 1;

            }
            $data['serial_no'] = $serial_no;
            $data['date'] = strtotime($data['date']);
            $data['object_type'] = Configure::read('object_type')['PI'];

            if ($user['user_group_id'] == Configure::read('user_group')['warehouse_in_charge_ug']) {
                $data['target_type'] = Configure::read('target_type')['warehouse'];
                $data['target_id'] = $user['warehouse_id'];
            } else {
                $data['target_type'] = Configure::read('target_type')['sales_point_(depot)'];
                $data['target_id'] = $user['depot_id'];
            }
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            if ($data['do_events'][0]['recipient_id']) {
                $data['action_status'] = Configure::read('do_object_action_status')['Save_and_Send'];
            } else {
                $data['action_status'] = Configure::read('do_object_action_status')['Save'];
            }

            // echo "<pre>";print_r($data );die();

            $doObject = $this->DoObjects->patchEntity($doObject, $data, ['associated' => ['DoObjectItems']]);
            //echo "<pre>";print_r($doObject);die();
            if ($result = $this->DoObjects->save($doObject)) {
                if ($data['do_events'][0]['recipient_id']) {
                    $do_events = $this->do_events->newEntity();
                    $event_data = [];
                    $event_data['sender_id'] = $user['id'];
                    $event_data['recipient_id'] = $data['do_events'][0]['recipient_id'];
                    $event_data['do_object_id'] = $result['id'];
                    $event_data['events_tepe'] = Configure::read('object_type')['PI'];
                    $event_data['action_status'] = Configure::read('do_object_event_action_status')['awaiting_approval'];
                    $event_data['created_by'] = $user['id'];
                    $event_data['created_date'] = $time;
                    $do_events = $this->do_events->patchEntity($do_events, $event_data);

                    if ($this->do_events->save($do_events)) {

                        $this->Flash->success('The do object has been saved.');
                        return $this->redirect(['action' => 'index']);
                    } else {
                        $this->Flash->error('The do object could not be saved. Please, try again.');
                    }
                }


                $this->Flash->success('The do object has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do object could not be saved. Please, try again.');
            }
        }

        if ($user['user_group_id'] == 12) {
            $depots = TableRegistry::get('depots')->get($user['depot_id']);
            $warehouses = json_decode($depots->warehouses);
            $items = $this->Common->item_name_resolver($warehouses[0]);

        } else {
            $items = $this->Common->item_name_resolver($user['warehouse_id']);

        }
        //   $targets = $this->DoObjects->Targets->find('list', ['conditions'=>['status'=>1]]);
        $recipients = TableRegistry::get('users')->find('list', ['conditions' => ['user_group_id' => 11]]);
        //echo "<pre>";print_r($items->toArray());die();
        $this->set(compact('doObject', 'recipients', 'items'));
        $this->set('_serialize', ['doObject']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Do Object id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $doObject = $this->DoObjects->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $doObject = $this->DoObjects->patchEntity($doObject, $data);
            if ($this->DoObjects->save($doObject)) {
                $this->Flash->success('The do object has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The do object could not be saved. Please, try again.');
            }
        }
        $targets = $this->DoObjects->Targets->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('doObject', 'targets'));
        $this->set('_serialize', ['doObject']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Do Object id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $doObject = $this->DoObjects->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $doObject = $this->DoObjects->patchEntity($doObject, $data);
        if ($this->DoObjects->save($doObject)) {
            $this->Flash->success('The do object has been deleted.');
        } else {
            $this->Flash->error('The do object could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getItemUnits()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {

            $data = $this->request->data;
            //echo "<pre>";print_r($data);die();
//            $item_unit = TableRegistry::get('item_units')->find('all',
//                ['conditions' => ['item_id' => $data['item_id']],
//                    'fields' => ['id', 'unit_display_name']])
//
//                ->hydrate(false)->toArray();

            $item_unit = TableRegistry::get('item_units')->find('all',
                ['conditions' => ['item_id' => $data['item_id']]])
                ->contain(['Units'])

                ->hydrate(false)->toArray();
         //   echo "<pre>";print_r($item_unit);die();
            $item_units = [];
            foreach ($item_unit as $unit):
                $item_units[$unit['unit']['id']] = $unit['unit']['unit_display_name'];
            endforeach;
           //   echo "<pre>";print_r($item_units);die();
            $this->response->body(json_encode($item_units));
            return $this->response;


        }
    }
}
