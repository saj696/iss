<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * SalesBudgets Controller
 *
 * @property \App\Model\Table\SalesBudgetsTable $SalesBudgets
 */
class ExploreBudgetsController extends AppController
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
        $this->loadModel('SalesBudgets');
        $this->loadModel('AdministrativeLevels');
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('SalesBudgetConfigurations');

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $exploreLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;
        $exploreLevels[Configure::read('max_level_no')+1] = 'Customer';

        $configData = $this->SalesBudgetConfigurations->find('all', ['conditions'=>['status'=>1]])->first();
        $configLevel = $configData['level_no'];
        for($i=$configLevel; $i<=7; $i++){
            unset($exploreLevels[$i+1]);
        }

        $this->set(compact('exploreLevels'));
        $this->set('_serialize', ['exploreLevels']);

        if($this->request->is('post'))
        {
            $data = $this->request->data;
            $start_date = strtotime($data['start_date']);
            $end_date = strtotime($data['end_date']);
            $explore_level = $data['explore_level'];
            //$parent_unit = $data['parent_unit'];
            $unit_id = $data['unit_id'];

            if($explore_level==Configure::read('max_level_no')+1){

            }else{
                $unitAdminUnitInfo = $this->AdministrativeUnits->get($unit_id);
                $childs = $this->AdministrativeUnits->find('all', ['conditions'=>['parent'=>$unitAdminUnitInfo['id']]]);

                if(sizeof($childs)>0){
                    foreach($childs as $child){

                        $unitGlobalId = $unitAdminUnitInfo['global_id'];
                        $limitStart = pow(2,(Configure::read('max_level_no')- $explore_level-1)*5);
                        $limitEnd = pow(2,(Configure::read('max_level_no')- $explore_level)*5);

                        $budgets = TableRegistry::get('sales_budgets')->find()->hydrate(false);
                        $budgets->where('administrative_unit_global_id -'. $unitGlobalId .'>= '.$limitStart);
                        $budgets->where('administrative_unit_global_id -'. $unitGlobalId .'<= '.$limitEnd);
                        if($start_date){
                            $budgets->where(['budget_period_start >='=>$start_date]);
                        }
                        if($end_date){
                            $budgets->where(['budget_period_end <='=>$end_date]);
                        }
                        $budgets->where(['status'=>1]);
                    }

                }else{

                }

            }
        }
    }

    public function ajax($param)
    {
        if($param == 'parent_units') {
            $data = $this->request->data;
            $explore_level = $data['explore_level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $explore_level-1], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();

            $dropArray = [];
            foreach($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;

            $this->viewBuilder()->layout('ajax');
            $this->set(compact('dropArray', 'param'));
        } elseif($param=='units') {
            $data = $this->request->data;
            $explore_level = $data['explore_level'];
            $paren_unit = $data['parent_unit'];

            if($explore_level==Configure::read('max_level_no')+1){
                $units = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $paren_unit], 'fields'=>['id', 'name']])->hydrate(false)->toArray();
                $dropArray = [];
                foreach($units as $unit):
                    $dropArray[$unit['id']] = $unit['name'];
                endforeach;
            }else{
                $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['parent' => $paren_unit], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();
                $dropArray = [];
                foreach($units as $unit):
                    $dropArray[$unit['id']] = $unit['unit_name'];
                endforeach;
            }

            $this->viewBuilder()->layout('ajax');
            $this->set(compact('dropArray', 'param'));
        }
    }
}
