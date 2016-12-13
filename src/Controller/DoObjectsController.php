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
			$doObjects = $this->DoObjects->find('all', [
	'conditions' =>['DoObjects.action_status !=' => 99]
//	'contain' => ['Targets']
	]);
		$this->set('doObjects', $this->paginate($doObjects) );
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
        $user=$this->Auth->user();
        $doObject = $this->DoObjects->get($id, [
         //   'contain' => ['Targets']
        ]);
        $this->set('doObject', $doObject);
        $this->set('_serialize', ['doObject']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user=$this->Auth->user();
        $time=time();
        $doObject = $this->DoObjects->newEntity();
        if ($this->request->is('post'))
        {

            $data=$this->request->data;
            echo "<pre>";print_r($data);die();
            $data['create_by']=$user['id'];
            $data['create_date']=$time;



            // Serials Table Insert/ update
            $this->loadModel('Serials');
            $serial_for = array_flip(Configure::read('serial_types'))['transfer_request'];
            $year = date('Y');

            if($user['user_group_id']==Configure::read('depot_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['depot'];
                $trigger_id = $user['depot_id'];
            elseif($user['user_group_id']==Configure::read('warehouse_in_charge_ug')):
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['warehouse'];
                $trigger_id = $user['warehouse_id'];
            else:
                $trigger_type = array_flip(Configure::read('serial_trigger_types'))['others'];
                $trigger_id = $user['administrative_unit_id'];
            endif;

            $existence = $this->Serials->find('all', ['conditions'=>['serial_for'=>$serial_for, 'year'=>$year, 'trigger_type'=>$trigger_type, 'trigger_id'=>$trigger_id]])->first();

            if ($existence) {
                $serial = TableRegistry::get('serials');
                $query = $serial->query();
                $query->update()->set(['serial_no' => $existence['serial_no']+1])->where(['id' => $existence['id']])->execute();
                // Update resource serial_no
                $resource = TableRegistry::get('transfer_resources');
                $query = $resource->query();
                $query->update()->set(['serial_no' => $existence['serial_no']+1])->where(['id' => $existence['id']])->execute();
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
                // Update resource serial_no
                $resource = TableRegistry::get('transfer_resources');
                $query = $resource->query();
                $query->update()->set(['serial_no' => 1])->where(['id' => $existence['id']])->execute();
            }
            $doObject = $this->DoObjects->patchEntity($doObject, $data, ['associated' => ['DoObjectItems']]);
            if ($this->DoObjects->save($doObject))
            {
                $this->Flash->success('The do object has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The do object could not be saved. Please, try again.');
            }
        }

        if($user['user_group_id']==12){
            $depots=TableRegistry::get('depots')->get($user['depot_id']);
            $warehouses=json_decode($depots->warehouses);
            $items=$this->Common->item_name_resolver($warehouses[0]);

        }else{
            $items=$this->Common->item_name_resolver($user['warehouse_id']);

        }
     //   $targets = $this->DoObjects->Targets->find('list', ['conditions'=>['status'=>1]]);
        $item_units = TableRegistry::get('item_units')->find('list');
            //echo "<pre>";print_r($items->toArray());die();
        $this->set(compact('doObject', 'targets','items'));
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
        $user=$this->Auth->user();
        $time=time();
        $doObject = $this->DoObjects->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $data=$this->request->data;
            $data['update_by']=$user['id'];
            $data['update_date']=$time;
            $doObject = $this->DoObjects->patchEntity($doObject, $data);
            if ($this->DoObjects->save($doObject))
            {
                $this->Flash->success('The do object has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The do object could not be saved. Please, try again.');
            }
        }
        $targets = $this->DoObjects->Targets->find('list', ['conditions'=>['status'=>1]]);
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

        $user=$this->Auth->user();
        $data=$this->request->data;
        $data['updated_by']=$user['id'];
        $data['updated_date']=time();
        $data['status']=99;
        $doObject = $this->DoObjects->patchEntity($doObject, $data);
        if ($this->DoObjects->save($doObject))
        {
            $this->Flash->success('The do object has been deleted.');
        }
        else
        {
            $this->Flash->error('The do object could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getItemUnits(){
        if ($this->request->is(['patch', 'post', 'put'])) {

            $data = $this->request->data;
            //echo "<pre>";print_r($data);die();
            $item_unit = TableRegistry::get('item_units')->find('all', ['conditions' => ['item_id' => $data['item_id']], 'fields' => ['id', 'unit_display_name']])->hydrate(false)->toArray();
            $item_units = [];
            foreach ($item_unit as $unit):
                $item_units[$unit['id']] = $unit['unit_display_name'];
            endforeach;
          //  echo "<pre>";print_r($item_units);die();
            $this->response->body(json_encode($item_units));
            return $this->response;


        }
    }
}
