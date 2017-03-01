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
class ReportSalesCollectionsController extends AppController
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
        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;

            $start_time = strtotime($data['start_date']);
            $end_time = strtotime($data['end_date']);
            $unit_level = $data['explore_level'];
            $unit_global_id = $data['explore_unit'];
            $group_by_level = $data['display_unit'];

            $this->loadComponent('Common');
            App::import('Helper', 'SystemHelper');
            $FunctionHelper = new FunctionHelper(new View());

            $credit_sales = $FunctionHelper->credit_sales($unit_global_id, $start_time, $end_time, $group_by_level);
            $cash_sales = $FunctionHelper->cash_sales($unit_global_id, $start_time, $end_time, $group_by_level);
            $cash_collection = $FunctionHelper->cash_collection($unit_global_id, $start_time, $end_time, 0, $group_by_level);
            $credit_collection = $FunctionHelper->credit_collection($unit_global_id, $start_time, $end_time, 0, $group_by_level);
            $credit_notes = $this->Common->get_unit_credit_note_amount($unit_level, $unit_global_id, $start_time, $end_time, $group_by_level);
            $opening_due = $this->Common->get_unit_opening_due($unit_level, $unit_global_id, $group_by_level, $start_time);
            $adjustments = $this->Common->get_unit_adjustment_amount($unit_level, $unit_global_id, $start_time, $end_time, $group_by_level);
            $credit_limit_array = $this->Common->administrative_unit_wise_credit_limit($unit_global_id, $group_by_level);

            $credit_limit = [];
            foreach($credit_limit_array as $limit){
                $credit_limit[$limit['GLOBAL_ID']] = $limit['CREDIT_LIMIT'];
            }

            $credit_sales_array_keys = array_keys($credit_sales);
            $cash_sales_array_keys = array_keys($cash_sales);
            $cash_collection_array_keys = array_keys($cash_collection);
            $credit_collection_array_keys = array_keys($credit_collection);
            $credit_notes_array_keys = array_keys($credit_notes);
            $opening_due_array_keys = array_keys($opening_due);
            $adjustments_array_keys = array_keys($adjustments);
            $credit_limit_array_keys = array_keys($credit_limit);

            $merged_keys = array_unique(array_merge($credit_sales_array_keys, $cash_sales_array_keys, $cash_collection_array_keys, $credit_collection_array_keys, $credit_notes_array_keys, $opening_due_array_keys, $adjustments_array_keys, $credit_limit_array_keys));

            $finalArray = [];

            foreach($merged_keys as $key){
                $finalArray[$key]['credit_limit'] = isset($credit_limit[$key])?$credit_limit[$key]:0;
                $finalArray[$key]['credit_sales'] = isset($credit_sales[$key])?$credit_sales[$key]:0;
                $finalArray[$key]['opening_due'] = round(isset($opening_due[$key])?$opening_due[$key]:0, 2);
                $finalArray[$key]['credit_note'] = isset($credit_notes[$key])?$credit_notes[$key]:0;
                $finalArray[$key]['cash_sales'] = isset($cash_sales[$key])?$cash_sales[$key]:0;
                $finalArray[$key]['total_sales'] = $finalArray[$key]['cash_sales'] + $finalArray[$key]['credit_sales'] - $finalArray[$key]['credit_note'];
                $finalArray[$key]['credit_collection'] = isset($credit_collection[$key])?$credit_collection[$key]:0;
                $finalArray[$key]['cash_collection'] = isset($cash_collection[$key])?$cash_collection[$key]:0;
                $finalArray[$key]['adjustment'] = isset($adjustments[$key])?$adjustments[$key]:0;
                $finalArray[$key]['recovery'] = $finalArray[$key]['cash_collection']+$finalArray[$key]['credit_collection'];
                $finalArray[$key]['closing_due'] = $finalArray[$key]['opening_due']+$finalArray[$key]['total_sales']-$finalArray[$key]['recovery'];
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
                $view->viewPath = 'ReportSalesCollections';
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
