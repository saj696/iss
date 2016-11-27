<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * SalesBudgetConfigurations Controller
 *
 * @property \App\Model\Table\SalesBudgetConfigurationsTable $SalesBudgetConfigurations
 */
class SalesBudgetConfigurationsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'SalesBudgetConfigurations.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $salesBudgetConfigurations = $this->SalesBudgetConfigurations->find('all', [
            'conditions' => ['SalesBudgetConfigurations.status' => 1]
        ]);

        $this->loadModel('AdministrativeLevels');
        $levels = $this->AdministrativeLevels->find('all', ['conditions'=>['status !='=>99]]);
        $levelArr = [];
        foreach($levels as $level){
            $levelArr[$level->level_no] = $level->level_name;
        }
        $levelArr[Configure::read('max_level_no')+1] = 'Customer';

        $salesBudgetConfigurations = $this->paginate($salesBudgetConfigurations);
        $this->set(compact('salesBudgetConfigurations', 'levelArr'));
        $this->set('_serialize', ['salesBudgetConfigurations']);
    }

    /**
     * View method
     *
     * @param string|null $id Sales Budget Configuration id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $salesBudgetConfiguration = $this->SalesBudgetConfigurations->get($id, [
            'contain' => []
        ]);
        $this->set('salesBudgetConfiguration', $salesBudgetConfiguration);
        $this->set('_serialize', ['salesBudgetConfiguration']);
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
        $salesBudgetConfiguration = $this->SalesBudgetConfigurations->newEntity();
        if ($this->request->is('post')) {

            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $salesBudgetConfiguration = $this->SalesBudgetConfigurations->patchEntity($salesBudgetConfiguration, $data);

            // Inactive current status 1
            $sales_budget_configurations = TableRegistry::get('sales_budget_configurations');
            $query = $sales_budget_configurations->query();
            $query->update()->set(['status' => 0])->where(['status' => 1])->execute();

            if ($this->SalesBudgetConfigurations->save($salesBudgetConfiguration)) {
                $this->Flash->success('The sales budget configuration has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The sales budget configuration could not be saved. Please, try again.');
            }
        }

        $this->loadModel('AdministrativeLevels');
        $levels = $this->AdministrativeLevels->find('all', ['conditions'=>['status !='=>99]]);
        $levelArr = [];
        foreach($levels as $level){
            $levelArr[$level->level_no] = $level->level_name;
        }
        $levelArr[Configure::read('max_level_no')+1] = 'Customer';

        $this->set(compact('salesBudgetConfiguration', 'levelArr'));
        $this->set('_serialize', ['salesBudgetConfiguration']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Sales Budget Configuration id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $salesBudgetConfiguration = $this->SalesBudgetConfigurations->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $salesBudgetConfiguration = $this->SalesBudgetConfigurations->patchEntity($salesBudgetConfiguration, $data);
            if ($this->SalesBudgetConfigurations->save($salesBudgetConfiguration)) {
                $this->Flash->success('The sales budget configuration has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The sales budget configuration could not be saved. Please, try again.');
            }
        }
        $this->loadModel('AdministrativeLevels');
        $levels = $this->AdministrativeLevels->find('all', ['conditions'=>['status !='=>99]]);
        $levelArr = [];
        foreach($levels as $level){
            $levelArr[$level->level_no] = $level->level_name;
        }
        $levelArr[Configure::read('max_level_no')+1] = 'Customer';

        $this->set(compact('salesBudgetConfiguration', 'levelArr'));
        $this->set('_serialize', ['salesBudgetConfiguration']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Sales Budget Configuration id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $salesBudgetConfiguration = $this->SalesBudgetConfigurations->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $salesBudgetConfiguration = $this->SalesBudgetConfigurations->patchEntity($salesBudgetConfiguration, $data);
        if ($this->SalesBudgetConfigurations->save($salesBudgetConfiguration)) {
            $this->Flash->success('The sales budget configuration has been deleted.');
        } else {
            $this->Flash->error('The sales budget configuration could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
