<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Client\Request;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use CakePdf\Pdf\CakePdf;
use mPDF;

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
        $user = $this->Auth->user();
        $user_level = $user['level_no'];

        $this->loadModel('SalesBudgets');
        $this->loadModel('AdministrativeLevels');
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('SalesBudgetConfigurations');

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1, 'level_no >='=>$user_level]]);
        $exploreLevels = [];
        foreach ($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;

        $configData = $this->SalesBudgetConfigurations->find('all', ['conditions' => ['status' => 1]])->first();
        $configLevel = $configData['level_no'];
        for ($i = $configLevel; $i <= 7; $i++) {
            unset($exploreLevels[$i + 1]);
        }

        $this->set(compact('exploreLevels', 'reportTypes'));
        $this->set('_serialize', ['exploreLevels']);
    }

    public function loadReport($param)
    {
        $this->loadComponent('common');

        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;
            $start_date = strtotime($data['start_date']);
            $end_date = strtotime($data['end_date']);
            $space_level = $data['explore_level'];
            $space_global_id = $data['explore_unit'];
            $group_by_level = $data['display_unit'];

            $mainArr = $this->Common->get_unit_sales_budget($space_level, $space_global_id, $group_by_level, $start_date, $end_date);

            if($group_by_level == Configure::read('max_level_no')+1){
                $customers = TableRegistry::get('customers')->find();
                $nameArray = [];
                foreach($customers->toArray() as $customer){
                    $nameArray[$customer['id']] = $customer['name'];
                }
            }else{
                $adminUnits = TableRegistry::get('administrative_units')->find();
                $nameArray = [];
                foreach($adminUnits->toArray() as $adminUnit){
                    $nameArray[$adminUnit['global_id']] = $adminUnit['unit_name'];
                }
            }

            if ($param == 'report') {
                $this->viewBuilder()->layout('report');
                $this->set(compact('mainArr', 'data', 'nameArray'));
                $this->set('_serialize', ['mainArr', 'explore_level', 'unit_id', 'nameArray']);
            } elseif ($param == 'pdf') {
                $view = new View();
                $btnHide = 1;
                $view->layout=false;
                $view->set(compact('mainArr', 'data', 'btnHide', 'nameArray'));
                $view->viewPath = 'ExploreBudgets';
                $html = $view->render('load_report');
                $this->loadComponent('Common');
                $this->Common->getPdf($html);
            }
        }
    }

    public function ajax($param)
    {
        $data = $this->request->data;
        if ($param == 'explore_units') {
            $explore_level = $data['explore_level'];
            $user = $this->Auth->user();
            $userAdministrativeUnit = $user['administrative_unit_id'];
            $this->loadModel('AdministrativeUnits');

            $userAdministrativeUnitInfo = $this->AdministrativeUnits->get($userAdministrativeUnit);
            $limitStart = pow(2,(Configure::read('max_level_no')- $user['level_no']-1)*5);
            $limitEnd = pow(2,(Configure::read('max_level_no')- $user['level_no'])*5);
            $units = TableRegistry::get('administrative_units')->find('all');
            $units->select(['global_id', 'unit_name']);
            $units->where(['level_no'=>$explore_level]);
            $units->where('global_id -'. $userAdministrativeUnitInfo['global_id'] .'>= '.$limitStart);
            $units->where('global_id -'. $userAdministrativeUnitInfo['global_id'] .'< '.$limitEnd);
            $units->toArray();

            $dropArray = [];
            foreach($units as $unit):
                $dropArray[$unit['global_id']] = $unit['unit_name'];
            endforeach;
        } elseif ($param == 'display_units') {
            $explore_level = $data['explore_level'];

            $administrativeLevelsData = TableRegistry::get('administrative_levels')->find('all', ['conditions' => ['status' => 1, 'level_no >='=>$explore_level]]);
            $dropArray = [];
            foreach ($administrativeLevelsData as $administrativeLevelsDatum):
                $dropArray[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
            endforeach;
            $dropArray[Configure::read('max_level_no') + 1] = 'Customer';
        } elseif ($param == 'customers'){
            $unit = $data['unit'];
            $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields' => ['id', 'name']])->hydrate(false)->toArray();

            $dropArray = [];
            foreach ($customers as $customer):
                $dropArray[$customer['id']] = $customer['name'];
            endforeach;
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray', 'param'));
    }
}
