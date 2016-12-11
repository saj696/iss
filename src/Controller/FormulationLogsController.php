<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\View\View;

/**
 * FormulationLogs Controller
 *
 * @property \App\Model\Table\FormulationLogsTable $FormulationLogs
 */
class FormulationLogsController extends AppController
{

	public $paginate = [
        'limit' => 15,
        'order' => [
            'FormulationLogs.id' => 'desc'
        ]
    ];

/**
* Index method
*
* @return void
*/
public function index()
{
			$formulationLogs = $this->FormulationLogs->find('all', [
	'conditions' =>['FormulationLogs.status !=' => 99]
	]);
		$this->set('formulationLogs', $this->paginate($formulationLogs) );
	$this->set('_serialize', ['formulationLogs']);
	}

    /**
     * View method
     *
     * @param string|null $id Formulation Log id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user=$this->Auth->user();
        $formulationLog = $this->FormulationLogs->get($id, [
            'contain' => []
        ]);
        $this->set('formulationLog', $formulationLog);
        $this->set('_serialize', ['formulationLog']);
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
        $formulationLog = $this->FormulationLogs->newEntity();
        if ($this->request->is('post'))
        {

            $data=$this->request->data;
            $data['create_by']=$user['id'];
            $data['create_date']=$time;
            $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $data);
            if ($this->FormulationLogs->save($formulationLog))
            {
                $this->Flash->success('The formulation log has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The formulation log could not be saved. Please, try again.');
            }
        }
//        Show Warehouse
        $this->loadModel('Warehouses');
        $warehouseNames = $this->Warehouses->find('list',['conditions'=> ['id' => $user['warehouse_id']], 'fields'=>['id','name'] ])->hydrate(false)->toArray();

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemUnit = $SystemHelper->get_item_unit_array();
        $this->set(compact('formulationLog','warehouseNames','itemUnit'));
        $this->set('_serialize', ['formulationLog']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Formulation Log id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user=$this->Auth->user();
        $time=time();
        $formulationLog = $this->FormulationLogs->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $data=$this->request->data;
            $data['update_by']=$user['id'];
            $data['update_date']=$time;
            $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $data);
            if ($this->FormulationLogs->save($formulationLog))
            {
                $this->Flash->success('The formulation log has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The formulation log could not be saved. Please, try again.');
            }
        }
        $this->set(compact('formulationLog'));
        $this->set('_serialize', ['formulationLog']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Formulation Log id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $formulationLog = $this->FormulationLogs->get($id);

        $user=$this->Auth->user();
        $data=$this->request->data;
        $data['updated_by']=$user['id'];
        $data['updated_date']=time();
        $data['status']=99;
        $formulationLog = $this->FormulationLogs->patchEntity($formulationLog, $data);
        if ($this->FormulationLogs->save($formulationLog))
        {
            $this->Flash->success('The formulation log has been deleted.');
        }
        else
        {
            $this->Flash->error('The formulation log could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function stock()
    {
        $data = $this->request->data;
        $itemUnit = $data['itemUnit'];

//        Load Item Unit Model
        $this->loadModel('ItemUnits');
        $itemUnitData = $this->ItemUnits->get($itemUnit);

        $this->loadModel('Stocks');
        $quantity = $this->Stocks->find('all',['conditions' => ['item_id' => $itemUnitData['item_id'], 'manufacture_unit_id' => $itemUnitData['manufacture_unit_id']], 'fields' => 'quantity'])->hydrate(false)->first();

        $this->loadModel('Units');
        $unitType = $this->Units->find('all',['conditions' => ['id' => $itemUnitData['manufacture_unit_id']], 'fields' => ['unit_type','converted_quantity']])->hydrate(false)->first();
        $compact = [
            'unitType' => $unitType['unit_type'],
            'convertedQuantity' => $unitType['converted_quantity'],
            'quantity' => $quantity['quantity']
        ];
        $this->response->body(json_encode($compact));
        return $this->response;

    }
}
