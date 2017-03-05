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
class ReportDailySalesController extends AppController
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
        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;

            $cumulative_start_time = strtotime($data['start_date']);
            $this_day = time();
            $unit_level = $data['explore_level'];
            $unit_global_id = $data['explore_unit'];
            $group_by_level = $data['display_unit'];

            $this->loadComponent('Common');
            App::import('Helper', 'SystemHelper');
            $FunctionHelper = new FunctionHelper(new View());

            $start_date_of_this_month = strtotime('01-'.date('m').'-'.date('Y'));
            $end_date_of_this_month = strtotime(Configure::read('month_end')[str_replace('0', "", date('m'))].'-'.date('m').'-'.date('Y'));

            $this_day_credit_sales = $FunctionHelper->credit_sales($unit_global_id, $this_day, $this_day, $group_by_level);
            $this_day_cash_sales = $FunctionHelper->cash_sales($unit_global_id, $this_day, $this_day, $group_by_level);
            $this_day_credit_note = $this->Common->get_unit_credit_note_amount($unit_level, $unit_global_id, $this_day, $this_day, $group_by_level);

            $this_month_sales_target = $FunctionHelper->sales_budget($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);
            $this_month_credit_sales = $FunctionHelper->credit_sales($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);
            $this_month_cash_sales = $FunctionHelper->cash_sales($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);
            $this_month_credit_note = $this->Common->get_unit_credit_note_amount($unit_level, $unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);

            $this_month_collection_target = [];
            $this_day_cash_collection = $FunctionHelper->cash_collection($unit_global_id, $this_day, $this_day, 0, $group_by_level);
            $this_day_credit_collection = $FunctionHelper->credit_collection($unit_global_id, $this_day, $this_day, 0, $group_by_level);
            $this_month_cash_collection = $FunctionHelper->cash_collection($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, 0, $group_by_level);
            $this_month_credit_collection = $FunctionHelper->credit_collection($unit_global_id, $start_date_of_this_month, $end_date_of_this_month, 0, $group_by_level);
            $this_month_adjustment = $this->Common->get_unit_adjustment_amount($unit_level, $unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);

            $cumulative_sales_target = $FunctionHelper->sales_budget($unit_global_id, $cumulative_start_time, $this_day, $group_by_level);
            $cumulative_cash_sales = $FunctionHelper->credit_sales($unit_global_id, $cumulative_start_time, time(), $group_by_level);
            $cumulative_credit_sales = $FunctionHelper->cash_sales($unit_global_id, $cumulative_start_time, time(), $group_by_level);
            $cumulative_credit_note = $this->Common->get_unit_credit_note_amount($unit_level, $unit_global_id, $cumulative_start_time, time(), $group_by_level);
            $cumulative_collection_target = [];
            $cumulative_credit_collection = $FunctionHelper->credit_collection($unit_global_id, $cumulative_start_time, $this_day, 0, $group_by_level);
            $cumulative_cash_collection = $FunctionHelper->cash_collection($unit_global_id, $cumulative_start_time, $this_day, 0, $group_by_level);
            $cumulative_adjustment = $this->Common->get_unit_adjustment_amount($unit_level, $unit_global_id, $start_date_of_this_month, $end_date_of_this_month, $group_by_level);

            $this_day_credit_sales_array_keys = array_keys($this_day_credit_sales);
            $this_day_cash_sales_array_keys = array_keys($this_day_cash_sales);
            $this_day_credit_note_array_keys = array_keys($this_day_credit_note);
            $this_month_sales_target_array_keys = array_keys($this_month_sales_target);
            $this_month_credit_sales_array_keys = array_keys($this_month_credit_sales);
            $this_month_cash_sales_array_keys = array_keys($this_month_cash_sales);
            $this_month_credit_note_array_keys = array_keys($this_month_credit_note);
            $this_month_collection_target_array_keys = array_keys($this_month_collection_target);
            $this_day_cash_collection_array_keys = array_keys($this_day_cash_collection);
            $this_day_credit_collection_array_keys = array_keys($this_day_credit_collection);
            $this_month_cash_collection_array_keys = array_keys($this_month_cash_collection);
            $this_month_credit_collection_array_keys = array_keys($this_month_credit_collection);
            $this_month_adjustment_array_keys = array_keys($this_month_adjustment);
            $cumulative_sales_target_array_keys = array_keys($cumulative_sales_target);
            $cumulative_cash_sales_array_keys = array_keys($cumulative_cash_sales);
            $cumulative_credit_sales_array_keys = array_keys($cumulative_credit_sales);
            $cumulative_credit_note_array_keys = array_keys($cumulative_credit_note);
            $cumulative_collection_target_array_keys = array_keys($cumulative_collection_target);
            $cumulative_credit_collection_array_keys = array_keys($cumulative_credit_collection);
            $cumulative_cash_collection_array_keys = array_keys($cumulative_cash_collection);
            $cumulative_adjustment_array_keys = array_keys($cumulative_adjustment);

            $merged_keys = array_unique(array_merge(
                $this_day_credit_sales_array_keys,
                $this_day_cash_sales_array_keys,
                $this_day_credit_note_array_keys,
                $this_month_sales_target_array_keys,
                $this_month_credit_sales_array_keys,
                $this_month_cash_sales_array_keys,
                $this_month_credit_note_array_keys,
                $this_month_collection_target_array_keys,
                $this_day_cash_collection_array_keys,
                $this_day_credit_collection_array_keys,
                $this_month_cash_collection_array_keys,
                $this_month_credit_collection_array_keys,
                $this_month_adjustment_array_keys,
                $cumulative_sales_target_array_keys,
                $cumulative_cash_sales_array_keys,
                $cumulative_credit_sales_array_keys,
                $cumulative_credit_note_array_keys,
                $cumulative_collection_target_array_keys,
                $cumulative_credit_collection_array_keys,
                $cumulative_cash_collection_array_keys,
                $cumulative_adjustment_array_keys
            ));

            $finalArray = [];

            foreach($merged_keys as $key){
                $finalArray[$key]['this_day_credit_sales'] = isset($this_day_credit_sales[$key])?$this_day_credit_sales[$key]:0;
                $finalArray[$key]['this_day_cash_sales'] = isset($this_day_cash_sales[$key])?$this_day_cash_sales[$key]:0;
                $finalArray[$key]['this_day_credit_note'] = isset($this_day_credit_note[$key])?$this_day_credit_note[$key]:0;
                $finalArray[$key]['this_month_sales_target'] = isset($this_month_sales_target[$key])?$this_month_sales_target[$key]:0;
                $finalArray[$key]['this_month_credit_sales'] = isset($this_month_credit_sales[$key])?$this_month_credit_sales[$key]:0;
                $finalArray[$key]['this_month_cash_sales'] = isset($this_month_cash_sales[$key])?$this_month_cash_sales[$key]:0;
                $finalArray[$key]['this_month_credit_note'] = isset($this_month_credit_note[$key])?$this_month_credit_note[$key]:0;
                $finalArray[$key]['this_month_collection_target'] = isset($this_month_collection_target[$key])?$this_month_collection_target[$key]:0;
                $finalArray[$key]['this_day_cash_collection'] = isset($this_day_cash_collection[$key])?$this_day_cash_collection[$key]:0;
                $finalArray[$key]['this_day_credit_collection'] = isset($this_day_credit_collection[$key])?$this_day_credit_collection[$key]:0;
                $finalArray[$key]['this_month_cash_collection'] = isset($this_month_cash_collection[$key])?$this_month_cash_collection[$key]:0;
                $finalArray[$key]['this_month_credit_collection'] = isset($this_month_credit_collection[$key])?$this_month_credit_collection[$key]:0;
                $finalArray[$key]['this_month_adjustment'] = isset($this_month_adjustment[$key])?$this_month_adjustment[$key]:0;
                $finalArray[$key]['cumulative_sales_target'] = isset($cumulative_sales_target[$key])?$cumulative_sales_target[$key]:0;
                $finalArray[$key]['cumulative_cash_sales'] = isset($cumulative_cash_sales[$key])?$cumulative_cash_sales[$key]:0;
                $finalArray[$key]['cumulative_credit_sales'] = isset($cumulative_credit_sales[$key])?$cumulative_credit_sales[$key]:0;
                $finalArray[$key]['cumulative_credit_note'] = isset($cumulative_credit_note[$key])?$cumulative_credit_note[$key]:0;
                $finalArray[$key]['cumulative_collection_target'] = isset($cumulative_collection_target[$key])?$cumulative_collection_target[$key]:0;
                $finalArray[$key]['cumulative_credit_collection'] = isset($cumulative_credit_collection[$key])?$cumulative_credit_collection[$key]:0;
                $finalArray[$key]['cumulative_cash_collection'] = isset($cumulative_cash_collection[$key])?$cumulative_cash_collection[$key]:0;
                $finalArray[$key]['cumulative_adjustment'] = isset($cumulative_adjustment[$key])?$cumulative_adjustment[$key]:0;
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
                $this->set(compact('finalArray', 'nameArray', 'data'));
                $this->set('_serialize', ['finalArray']);
            } elseif ($param == 'pdf') {
                $view = new View();
                $btnHide = 1;
                $view->layout=false;
                $view->set(compact('finalArray', 'nameArray', 'data', 'btnHide'));
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
