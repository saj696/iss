<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
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
class ReportOlderPaymentBonusController extends AppController
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
        $this->loadModel('Offers');

        $user = $this->Auth->user();

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1, 'level_no >='=>$user['level_no']]]);
        $exploreLevels = [];
        foreach ($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;
        $exploreLevels[Configure::read('max_level_no') + 1] = 'Customer';

        $this->set(compact('exploreLevels'));
        $this->set('_serialize', ['exploreLevels']);
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

    public function calculation(){
        $data = $this->request->data;
        $this->loadComponent('common');
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());
        $this->loadComponent('Common');
        $upto_date = strtotime($data['dues_upto_date']);
        $payment_date = strtotime($data['payment_date']);
        $bonus_percentage = $data['bonus_percentage'];
        $explore_level = $data['explore_level'];
        $unit_id = $data['unit_id'];
        $this->loadModel('AdministrativeUnits');

        $customers =  TableRegistry::get('customers')->query();
        $customers->contain('AdministrativeUnits');
        if($explore_level == Configure::read('max_level_no') + 1){
            $customers->where(['customers.id'=>$unit_id]);
        }elseif($explore_level == Configure::read('max_level_no')){
            $adminGlobal = $this->AdministrativeUnits->get($unit_id);
            $customers->where(['unit_global_id'=>$adminGlobal['global_id']]);
        }else{
            $adminGlobal = $this->AdministrativeUnits->get($unit_id);
            $limitStart = pow(2,(Configure::read('max_level_no')- $explore_level-1)*5);
            $limitEnd = pow(2,(Configure::read('max_level_no')- $explore_level)*5);
            $customers->where('unit_global_id -'. $adminGlobal['global_id'] .'>= '.$limitStart);
            $customers->where('unit_global_id -'. $adminGlobal['global_id'] .'< '.$limitEnd);
        }

        $customers->where(['customers.status !='=>99]);
        $customers = $customers->toArray();

        $customerDetailArray = [];
        foreach($customers as $customer){
            $customerDetailArray[$customer['id']]['name'] = $customer['name'];
            $customerDetailArray[$customer['id']]['code'] = $customer['code'];
            $customerDetailArray[$customer['id']]['address'] = $customer['address'];
        }

        $customerArray = [];
        foreach($customers as $customer){
            $customerArray[] = $customer['id'];
        }

        $returnArray = [];
        foreach($customerArray as $customer){
            $dues_upto_date = $this->common->getCustomerDue($customer, $upto_date);

            $invoicePayments = TableRegistry::get('invoice_payments')->query()->hydrate(false);
            $invoicePayments->contain(['Invoices', 'Payments']);
            $invoicePayments->where(['payment_collection_date <='=>$payment_date]);
            $invoicePayments->where(['payment_collection_date >'=>$upto_date]);
            $invoicePayments->where(['Invoices.invoice_date <='=>$upto_date]);
            $invoicePayments->where(['invoice_payments.customer_id'=>$customer]);
            $invoicePayments->group('invoice_payments.customer_id');
            $invoicePayments->select(['total_payment'=>$invoicePayments->func()->sum('invoice_wise_payment_amount')]);
            $invoicePayments->first();

            if($invoicePayments->toArray()){
                $payment_total = $invoicePayments->toArray()[0]['total_payment'];
                if($dues_upto_date == $payment_total){
                    $bonus = ($payment_total*$bonus_percentage)/100;
                }else{
                    $bonus = 0;
                }
                $returnArray[$customer]['bonus'] = $bonus;
                $returnArray[$customer]['total_payment'] = $payment_total;
            }else{
                $bonus = 0;
                $returnArray[$customer]['bonus'] = $bonus;
                $returnArray[$customer]['total_payment'] = 0;
            }
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('returnArray', 'customerDetailArray', 'data'));
    }

    public function save(){
        $user = $this->Auth->user();
        $this->loadModel('CustomerAwards');
        $this->loadModel('AdministrativeUnits');

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($user, &$saveStatus)
            {
                $mark = 0;
                $postData = $this->request->data;
                $unit_id = $postData['unit_id'];
                $start_date = strtotime($postData['start_date']);
                $end_date = strtotime($postData['end_date']);
                $customer_id = $postData['customer_id'];
                $amount = $postData['amount'];

                $customerAwardsInfo = TableRegistry::get('customer_awards')->query();
                $customerAwardsInfo->where(['customer_id'=>$customer_id]);
                $customerAwardsInfo->where(['award_account_code'=>330000]); // AccHead: 330000 Payment Bonus special
                $customerAwardsInfo->order(['offer_period_end'=>'DESC']);
                $customerAwardsInfo->limit(1);

                if($customerAwardsInfo->toArray()){
                    $latest_offer_period_end = $customerAwardsInfo->toArray()[0]['offer_period_end'];
                    if($start_date > $latest_offer_period_end){
                        $mark = 1;
                    }else{
                        $mark = 0;
                    }
                }else{
                    $mark = 1;
                }

                if($mark == 1){
                    $CustomerAwards = $this->CustomerAwards->newEntity();
                    $data['customer_id'] = $customer_id;
                    $customerAdministrativeUnitInfo = $this->AdministrativeUnits->get($unit_id);
                    $data['parent_global_id'] = $customerAdministrativeUnitInfo['global_id'];

                    $data['award_account_code'] = 330000; // AccHead: 330000 Payment Bonus special
                    $data['amount'] = $amount;
                    $data['remaining_amount'] = $amount;
                    $data['offer_period_start'] = $start_date;
                    $data['offer_period_end'] = $end_date;
                    $data['action_status'] = Configure::read('customer_award_status')['pending'];
                    $data['created_by'] = $user['id'];
                    $data['created_date'] = time();

                    $CustomerAwards = $this->CustomerAwards->patchEntity($CustomerAwards, $data);
                    $this->CustomerAwards->save($CustomerAwards);

                    $saveStatus = true;
                    $this->response->body($saveStatus);
                    return $this->response;
                }else{
                    $saveStatus = false;
                    $this->response->body($saveStatus);
                    return $this->response;
                }
            });
        } catch (\Exception $e) {
            $saveStatus = false;
            $this->response->body($saveStatus);
            return $this->response;
        }
        $this->autoRender = false;
    }

}
