<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
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
class ReportTargetVsAchievementsController extends AppController
{
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

        $this->set(compact('exploreLevels', 'reportTypes'));
        $this->set('_serialize', ['exploreLevels']);
    }

    public function loadReport($param)
    {
        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;

            $report_type = $data['report_type'];
            $unit_level = $data['explore_level'];
            $unit_global_id = $data['explore_unit'];
            $group_by_level = $data['display_unit'];

            $this->loadComponent('Common');
            App::import('Helper', 'FunctionHelper');
            $FunctionHelper = new FunctionHelper(new View());

            $start_date_of_this_month = strtotime('01-'.date('m').'-'.date('Y'));
            $end_date_of_this_month = strtotime(Configure::read('month_end')[str_replace('0', "", date('m'))].'-'.date('m').'-'.date('Y'));
            $this_month = date('m');

            if($this_month>=7){
                $start_date_cumulative = strtotime('01-07-'.date('Y'));
                $end_date_cumulative = time();
            }else{
                $start_date_cumulative = strtotime('01-07-'.(date('Y')-1));
                $end_date_cumulative = time();
            }

            if($report_type == 1){
                $this_month_target = $FunctionHelper->sales_budget($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);
                $this_month_achievement = $FunctionHelper->total_sales($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);

                $cumulative_target = $FunctionHelper->sales_budget($unit_global_id, $start_date_cumulative, $end_date_cumulative, $group_by_level);
                $cumulative_achievement = $FunctionHelper->total_sales($unit_global_id, $start_date_cumulative, $end_date_cumulative, $group_by_level);
            }else{
                $this_month_target = $FunctionHelper->collection_target_90_days_old($unit_level, $unit_global_id, $group_by_level, $start_date_of_this_month, $end_date_of_this_month);
                $this_month_achievement = $FunctionHelper->total_collection($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, 0, $group_by_level);

                $cumulative_target = $FunctionHelper->collection_target_90_days_old($unit_level, $unit_global_id, $group_by_level, $start_date_cumulative, $end_date_cumulative);
                $cumulative_achievement = $FunctionHelper->total_collection($unit_global_id, $start_date_cumulative, $end_date_cumulative, 0, $group_by_level);
            }

            if($report_type == 1){
                $cumulative_target_revised = $cumulative_target;
                $this_month_target_revised = $this_month_target;
            }else{
                $cumulative_target_revised = [];
                foreach($cumulative_target as $ct){
                    $cumulative_target_revised[$ct['global_id']] = $ct['total_amount'];
                }

                $this_month_target_revised = [];
                foreach($this_month_target as $ct){
                    $this_month_target_revised[$ct['global_id']] = $ct['total_amount'];
                }
            }

            $this_month_target_revised_array_keys = array_keys($this_month_target_revised);
            $this_month_achievement_array_keys = array_keys($this_month_achievement);
            $cumulative_target_revised_array_keys = array_keys($cumulative_target_revised);
            $cumulative_achievement_array_keys = array_keys($cumulative_achievement);

            $merged_keys = array_unique(array_merge(
                $this_month_target_revised_array_keys,
                $this_month_achievement_array_keys,
                $cumulative_target_revised_array_keys,
                $cumulative_achievement_array_keys
            ));

            $finalArray = [];

            foreach($merged_keys as $key){
                $finalArray[$key]['this_month_target'] = isset($this_month_target_revised[$key])?$this_month_target_revised[$key]:0;
                $finalArray[$key]['this_month_achievement'] = isset($this_month_achievement[$key])?$this_month_achievement[$key]:0;
                $finalArray[$key]['cumulative_target'] = isset($cumulative_target_revised[$key])?$cumulative_target_revised[$key]:0;
                $finalArray[$key]['cumulative_achievement'] = isset($cumulative_achievement[$key])?$cumulative_achievement[$key]:0;
            }

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
                $this->set(compact('finalArray', 'nameArray', 'data', 'report_type'));
                $this->set('_serialize', ['finalArray']);
            } elseif ($param == 'pdf') {
                $view = new View();
                $btnHide = 1;
                $view->layout=false;
                $view->set(compact('finalArray', 'nameArray', 'data', 'btnHide', 'report_type'));
                $view->viewPath = 'ReportDailySales';
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
