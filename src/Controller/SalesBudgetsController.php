<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * SalesBudgets Controller
 *
 * @property \App\Model\Table\SalesBudgetsTable $SalesBudgets
 */
class SalesBudgetsController extends AppController
{
    public $paginate = [
        'limit' => 15,
        'order' => [
            'SalesBudgets.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $salesBudgets = $this->SalesBudgets->find('all', [
            'conditions' => ['SalesBudgets.status !=' => 99],
            'contain' => ['SalesBudgetConfigurations', 'AdministrativeUnits', 'Items']
        ]);
        $this->set('salesBudgets', $this->paginate($salesBudgets));
        $this->set('_serialize', ['salesBudgets']);
    }

    /**
     * View method
     *
     * @param string|null $id Sales Budget id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $salesBudget = $this->SalesBudgets->get($id, [
            'contain' => ['SalesBudgetConfigurations', 'AdministrativeUnits', 'Items']
        ]);
        $this->set('salesBudget', $salesBudget);
        $this->set('_serialize', ['salesBudget']);
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
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('ItemUnits');
        $this->loadModel('SalesBudgetConfigurations');
        $salesBudgetConfigurations = $this->SalesBudgetConfigurations->find('all', ['conditions' => ['status' => 1]])->first();
        $parents = $this->AdministrativeUnits->find('list', ['conditions'=>['level_no'=>$salesBudgetConfigurations['level_no']-1]])->toArray();

        $this->loadModel('Items');
        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $items = [];
        if($salesBudgetConfigurations['product_scope']==1){
            $items = $SystemHelper->get_item_array();
        }elseif($salesBudgetConfigurations['product_scope']==2){
            $items = [];
        }elseif($salesBudgetConfigurations['product_scope']==3){
            $items = $SystemHelper->get_item_unit_array();
        }

        $salesBudget = $this->SalesBudgets->newEntity();

        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($salesBudgetConfigurations, $salesBudget, $user, $time, &$saveStatus) {
                    $data = $this->request->data;
                    if (sizeof($data['details']) > 0) {
                        foreach ($data['details'] as $detail) {
                            $salesBudget = $this->SalesBudgets->newEntity();
                            $insert['sales_budget_configuration_id'] = $salesBudgetConfigurations['id'];
                            $insert['budget_period_start'] = strtotime($data['budget_period_start']);
                            $insert['budget_period_end'] = strtotime($data['budget_period_end']);
                            $insert['level_no'] = $salesBudgetConfigurations['level_no'];
                            $insert['administrative_unit_id'] = $detail['administrative_unit_id'];

                            if ($salesBudgetConfigurations['level_no'] == Configure::read('max_level_no') + 1):
                                $administrativeUnitInfo = $this->AdministrativeUnits->get($detail['parent_id']);
                                $insert['administrative_unit_global_id'] = $administrativeUnitInfo['global_id'];
                            else:
                                $administrativeUnitInfo = $this->AdministrativeUnits->get($detail['administrative_unit_id']);
                                $insert['administrative_unit_global_id'] = $administrativeUnitInfo['global_id'];
                            endif;

                            $insert['product_scope'] = $salesBudgetConfigurations['product_scope'];

                            if ($salesBudgetConfigurations['product_scope'] == 1) {
                                $insert['item_id'] = $detail['item_id'];
                            } elseif ($salesBudgetConfigurations['product_scope'] == 3) {
                                $insert['item_unit_id'] = $detail['item_id'];
                                $itemUnitInfo = $this->ItemUnits->get($detail['item_id']);
                                $insert['item_id'] = $itemUnitInfo['item_id'];
                                $insert['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                            }

                            $insert['budget_amount'] = $detail['budget_amount'];
                            $insert['created_by'] = $user['id'];
                            $insert['created_date'] = $time;

                            if($salesBudgetConfigurations){
                                $salesBudget = $this->SalesBudgets->patchEntity($salesBudget, $insert);
                                $this->SalesBudgets->save($salesBudget);
                            }else{
                                $this->Flash->error('Sales Budget Configuration not done; Please try again!');
                                return $this->redirect(['action' => 'add']);
                            }

                        }
                    } else {
                        $this->Flash->error('Sales Budget not done; no entry. Please try again!');
                        return $this->redirect(['action' => 'add']);
                    }
                });

                $this->Flash->success('Sales Budget made. Thank you!');
                return $this->redirect(['action' => 'add']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Sales Budget not been made. Please try again!');
                return $this->redirect([ 'action' => 'add']);
            }
        }

        $this->set(compact('salesBudget', 'salesBudgetConfigurations', 'parents', 'items'));
        $this->set('_serialize', ['salesBudget']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Sales Budget id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $salesBudget = $this->SalesBudgets->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $salesBudget = $this->SalesBudgets->patchEntity($salesBudget, $data);
            if ($this->SalesBudgets->save($salesBudget)) {
                $this->Flash->success('The sales budget has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The sales budget could not be saved. Please, try again.');
                return $this->redirect(['action' => 'index']);
            }
        }
        $salesBudgetConfigurations = $this->SalesBudgets->SalesBudgetConfigurations->find('list', ['conditions' => ['status' => 1]]);
        $administrativeUnits = $this->SalesBudgets->AdministrativeUnits->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('salesBudget', 'salesBudgetConfigurations', 'administrativeUnits'));
        $this->set('_serialize', ['salesBudget']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Sales Budget id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $salesBudget = $this->SalesBudgets->get($id);
        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $salesBudget = $this->SalesBudgets->patchEntity($salesBudget, $data);
        if ($this->SalesBudgets->save($salesBudget)) {
            $this->Flash->success('The sales budget has been deleted.');
        } else {
            $this->Flash->error('The sales budget could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function ajax()
    {
        $this->loadModel('SalesBudgetConfigurations');
        $user = $this->Auth->user();
        $data = $this->request->data;
        $parent_unit = $data['parent_unit'];
        $salesBudgetConfigurations = $this->SalesBudgetConfigurations->find('all', ['conditions' => ['status' => 1]])->first();

        if($salesBudgetConfigurations['level_no']==Configure::read('max_level_no')+1){
            $units = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $parent_unit], 'fields'=>['id', 'name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach($units as $unit):
                $dropArray[$unit['id']] = $unit['name'];
            endforeach;
        }else{
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['parent' => $parent_unit], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();
            $dropArray = [];
            foreach($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }
}
