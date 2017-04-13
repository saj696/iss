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
class ReportPosController extends AppController
{
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = $this->Auth->user();
        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1, 'level_no >='=>$user['level_no']]]);
        $exploreLevels = [];
        foreach ($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;

        $this->set(compact('exploreLevels'));
        $this->set('_serialize', ['exploreLevels']);
    }

    public function loadReport($param)
    {
        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;
            $start_date = strtotime($data['start_date']);
            $end_date = strtotime($data['end_date']);
            $unit_id = $data['unit_id'];
            $customer_id = $data['customer_id'];
            $po_status = $data['po_status'];

            $this->loadModel('AdministrativeUnits');
            $adminUnitInfo = $this->AdministrativeUnits->get($unit_id);

            $pos = TableRegistry::get('pos')->find()->hydrate(false);
            $pos->contain(['Customers', 'PoProducts']);
            $pos->where(['po_date >='=>$start_date]);
            $pos->where(['po_date <='=>$end_date]);
            if($customer_id>0){
                $pos->where(['customer_id'=>$customer_id]);
            }
            $pos->where(['customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            $pos->where(['po_status'=>$po_status]);

            if ($param == 'report') {
                $this->viewBuilder()->layout('report');
                $this->set(compact('pos', 'data'));
                $this->set('_serialize', ['finalArray']);
            } elseif ($param == 'pdf') {
                $view = new View();
                $btnHide = 1;
                $view->layout=false;
                $view->set(compact('pos', 'data', 'btnHide'));
                $view->viewPath = 'ReportPos';
                $html = $view->render('load_report');
                $this->loadComponent('Common');
                $this->Common->getPdf($html);
            }
        }
    }

    public function ajax($param)
    {
        $data = $this->request->data;
        if ($param == 'parent_units') {
            $explore_level = $data['explore_level'];
            $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $explore_level - 1], 'fields' => ['id', 'unit_name']])->hydrate(false)->toArray();

            $dropArray = [];
            foreach ($units as $unit):
                $dropArray[$unit['id']] = $unit['unit_name'];
            endforeach;
        } elseif ($param == 'units') {
            $explore_level = $data['explore_level'];
            $paren_unit = $data['parent_unit'];

            if ($explore_level == Configure::read('max_level_no') + 1) {
                $units = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $paren_unit], 'fields' => ['id', 'name']])->hydrate(false)->toArray();
                $dropArray = [];
                foreach ($units as $unit):
                    $dropArray[$unit['id']] = $unit['name'];
                endforeach;
            } else {
                $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['parent' => $paren_unit], 'fields' => ['id', 'unit_name']])->hydrate(false)->toArray();
                $dropArray = [];
                foreach ($units as $unit):
                    $dropArray[$unit['id']] = $unit['unit_name'];
                endforeach;
            }
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
