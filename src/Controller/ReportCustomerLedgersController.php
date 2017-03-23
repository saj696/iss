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
class ReportCustomerLedgersController extends AppController
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
        foreach ($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;
        $exploreLevels[Configure::read('max_level_no') + 1] = 'Customer';

        $this->set(compact('exploreLevels'));
        $this->set('_serialize', ['exploreLevels']);
    }

    public function loadReport($param)
    {
        if ($this->request->is(['post', 'get'])) {
            $data = $this->request->data;
            $start_date = strtotime($data['start_date']);
            $end_date = strtotime($data['end_date']);
            $explore_level = $data['explore_level'];
            $unit_id = $data['unit_id'];
            $customer_id = $data['customer_id'];

            $finalDue = $this->Common->getCustomerDue($customer_id, $start_date);

            // invoices between start and end dates
            $finalArray = [];

            $invoices = TableRegistry::get('invoices')->find()->hydrate(false);
            $invoices->where(['invoice_date >='=>$start_date]);
            $invoices->where(['invoice_date <='=>$end_date]);
            $invoices->where(['customer_id'=>$customer_id]);
            if($invoices->toArray()){
                $invoiceArray = $invoices->toArray();
                foreach($invoiceArray as $isl=>$invAr){
                    $finalArray[$invAr['invoice_date']]['inv'][$isl]['id'] = $invAr['id'];
                    $finalArray[$invAr['invoice_date']]['inv'][$isl]['net_total'] = $invAr['net_total'];
                    $finalArray[$invAr['invoice_date']]['inv'][$isl]['type'] = $invAr['invoice_type'];
                }
            }

            // payments between start and end dates
            $payments = TableRegistry::get('invoice_payments')->find()->hydrate(false);
            $payments->contain(['Invoices', 'Payments']);
            $payments->where(['invoice_payments.payment_collection_date >='=>$start_date]);
            $payments->where(['invoice_payments.payment_collection_date <='=>$end_date]);
            $payments->where(['invoice_payments.customer_id'=>$customer_id]);
            if($payments->toArray()){
                $paymentArray = $payments->toArray();
                foreach($paymentArray as $psl=>$payAr){
                    $finalArray[$payAr['payment_collection_date']]['pay'][$psl]['id'] = $payAr['id'];
                    $finalArray[$payAr['payment_collection_date']]['pay'][$psl]['net_total'] = $payAr['invoice_wise_payment_amount'];
                    $finalArray[$payAr['payment_collection_date']]['pay'][$psl]['type'] = $payAr['invoice']['invoice_type'];
                    $finalArray[$payAr['payment_collection_date']]['pay'][$psl]['sl_no'] = $payAr['payment']['collection_serial_no'];
                }
            }

            if ($param == 'report') {
                $this->viewBuilder()->layout('report');
                $this->set(compact('finalArray', 'finalDue', 'data'));
                $this->set('_serialize', ['finalArray']);
            } elseif ($param == 'pdf') {
                $view = new View();
                $btnHide = 1;
                $view->layout=false;
                $view->set(compact('finalArray', 'finalDue', 'data', 'btnHide'));
                $view->viewPath = 'ReportCustomerLedgers';
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
