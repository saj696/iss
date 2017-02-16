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
class ReportExploreOffersController extends AppController
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
//        $limitStart = pow(2,(Configure::read('max_level_no')- 1-1)*5);
//        $limitEnd = pow(2,(Configure::read('max_level_no')- 1)*5);
//
//        $administrativeUnits =  TableRegistry::get('administrative_units')->query()->hydrate(false);
//        $administrativeUnits->where('global_id -'. 1245184 .'>= '.$limitStart);
//        $administrativeUnits->where('global_id -'. 1245184 .'< '.$limitEnd)->orWhere(['global_id'=>1245184]);
//        $administrativeUnits->where(['level_no'=>2]);
//        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);
//
//        echo '<pre>';
//        print_r($administrativeUnits->toArray());
//        echo '</pre>';
//        exit;

//        App::import('Helper', 'FunctionHelper');
//        $FunctionHelper = new FunctionHelper(new View());
//        $arr = $FunctionHelper->total_collection(1048576, 1451606400, 1513036800, 0, 5);
//        $arr = $FunctionHelper->opening_due(1048576, 1513036800, 5);
//        $arr = $FunctionHelper->sales_budget(1048576, 1485907200, 1488240000, 3);
//
//        echo '<pre>';
//        print_r($arr);
//        echo '</pre>';
//        exit;

//        $arr[0]['global'] = 1111;
//        $arr[0]['net'] = 10;
//        $arr[1]['global'] = 1111;
//        $arr[1]['net'] = 20;
//        $arr[2]['global'] = 1111;
//        $arr[2]['net'] = 35;
//        $arr[3]['global'] = 3333;
//        $arr[3]['net'] = 45;
//        $arr[4]['global'] = 4444;
//        $arr[4]['net'] = 55;
//
//        $newInvoiceArray = [];
//        foreach($arr as $invoice){
//            if(isset($newInvoiceArray[$invoice['global']])){
//                $newInvoiceArray[$invoice['global']] += $invoice['net'];
//            }else{
//                $newInvoiceArray[$invoice['global']] = $invoice['net'];
//            }
//        }
//
//        $yyy = ['1111','3333'];
//        foreach($yyy as $yy){
//
//        }
//
//        echo '<pre>';
//        print_r($newInvoiceArray);
//        echo '</pre>';
//        exit;

//        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['id'=>1048576])->first();
//
//        $limitStart = pow(2,(Configure::read('max_level_no')- $searchUnitInfo['level_no']-1)*5);
//        $limitEnd = pow(2,(Configure::read('max_level_no')- $searchUnitInfo['level_no'])*5);
//
//        $administrativeUnits =  TableRegistry::get('administrative_units')->query()->hydrate(false);
//        $administrativeUnits->where('global_id -'. 1048576 .'>= '.$limitStart);
//        $administrativeUnits->where('global_id -'. 1048576 .'< '.$limitEnd);
//        $administrativeUnits->where(['level_no'=>2]);
//        $administrativeUnits->select(['global_id']);
//
//        if($administrativeUnits->toArray()){
//            $mainArray = $administrativeUnits->toArray();
//            $simple = [];
//            foreach($mainArray as $arr){
//                $simple[] = $arr['global_id'];
//            }
//        }
//
//        echo '<pre>';
//        print_r($simple);
//        echo '</pre>';
//        exit;

//        $arr = [];
//        $arr['cid']=10;
//        $arr['cid']=10;
//
//        if(count($arr) == count($arr, COUNT_RECURSIVE))
//        {
//            echo 'array is not multidimensional';
//        }
//        else
//        {
//            echo 'array is multidimensional';
//        }
//
//        exit;


        $this->loadModel('SalesBudgets');
        $this->loadModel('AdministrativeLevels');
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('SalesBudgetConfigurations');
        $this->loadModel('Offers');

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $exploreLevels = [];
        foreach ($administrativeLevelsData as $administrativeLevelsDatum):
            $exploreLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        endforeach;
        $exploreLevels[Configure::read('max_level_no') + 1] = 'Customer';

        $configData = $this->SalesBudgetConfigurations->find('all', ['conditions' => ['status' => 1]])->first();
        $configLevel = $configData['level_no'];
        for ($i = $configLevel; $i <= 7; $i++) {
            unset($exploreLevels[$i + 1]);
        }

        $offers = $this->Offers->find('list', ['conditions'=>['status'=>1]]);

        $this->set(compact('exploreLevels', 'offers'));
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

            // upto date due calculate
            $closestUptoDateDue = TableRegistry::get('personal_accounts')->find()->hydrate(false);
            $closestUptoDateDue->where(['account_code'=>Configure::read('account_receivable_code')]);
            $closestUptoDateDue->where(['upto_date <'=>$start_date]);
            $closestUptoDateDue->order(['upto_date'=> 'DESC']);
            $closestUptoDateDue->first();

            if($closestUptoDateDue->toArray()){
                $uptoDateDue = $closestUptoDateDue['due'];
                $uptoDate = $closestUptoDateDue['upto_date'];
            }else{
                $uptoDateDue = 0;
                $uptoDate = 0;
            }

            if($uptoDate>0){
                $betweenDateInvoices = TableRegistry::get('invoices')->find()->hydrate(false);
                $betweenDateInvoices->where(['invoice_date >='=>$uptoDate]);
                $betweenDateInvoices->where(['invoice_date <='=>$start_date]);
                $betweenDateInvoices->where(['customer_id'=>$customer_id]);
                $betweenDateInvoices->select(['SUM(net_total)']);
                $betweenDateInvoices->first();
                if($betweenDateInvoices->toArray()){
                    $betweenDateInvoicesNetTotal = $betweenDateInvoices->toArray()['net_total'];
                }else{
                    $betweenDateInvoicesNetTotal = 0;
                }
                $betweenDatePayments = TableRegistry::get('invoice_payments')->find()->hydrate(false);
                $betweenDatePayments->where(['payment_collection_date >='=>$uptoDate]);
                $betweenDatePayments->where(['payment_collection_date <='=>$start_date]);
                $betweenDatePayments->where(['customer_id'=>$customer_id]);
                $betweenDatePayments->select(['SUM(invoice_wise_payment_amount)']);
                $betweenDatePayments->first();
                if($betweenDatePayments->toArray()){
                    $betweenDatePaymentsNetTotal = $betweenDatePayments->toArray()['invoice_wise_payment_amount'];
                }else{
                    $betweenDatePaymentsNetTotal = 0;
                }
            }else{
                $betweenDateInvoicesNetTotal = 0;
                $betweenDatePaymentsNetTotal = 0;
            }

            $finalDue = $uptoDateDue + $betweenDateInvoicesNetTotal - $betweenDatePaymentsNetTotal;

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
            $offer_id = $data['offer_id'];

            $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields' => ['id', 'name']])->hydrate(false)->toArray();

            $dropArray = [];
            $maxCalDateArr = [];
            foreach ($customers as $customer):
                $dropArray[$customer['id']] = $customer['name'];
                $maxCalDateArr[$customer['id']] = $this->getCalMaxDate($offer_id, $customer['id']);
            endforeach;
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray', 'param', 'maxCalDateArr'));
    }

    public function calculation(){
        $data = $this->request->data;
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());
        $this->loadComponent('Common');
        $start_date = strtotime($data['start_date']);
        $end_date = strtotime($data['end_date']);
        $offer_id = $data['offer_id'];
        $unit_id = $data['unit_id'];

        $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit_id], 'fields' => ['id', 'name']])->hydrate(false)->toArray();

        $this->loadModel('Offers');
        $offer = $this->Offers->get($offer_id, ['contain'=>['OfferItems']]);
        $invoicing = $offer['invoicing'];
        $conditions = json_decode($offer['conditions'], true);
        $condition_postfix = json_decode($offer['condition_postfix'], true);
        $wonOffers = [];

        foreach($customers as $customer){
            $max_calculated_date = $this->getCalMaxDate($offer_id, $customer['id']);

            if($start_date>$max_calculated_date){
                if($invoicing==array_flip(Configure::read('special_offer_invoicing'))['Single']){
                    $applicablePostfix = $condition_postfix[0];
                    if($conditions[0]['time_level']==array_flip(Configure::read('offer_time_level'))['Instant'] && $conditions[0]['context']==array_flip(Configure::read('offer_contexts'))['Invoice']){
                        $customerInvoices = TableRegistry::get('invoices')->find()->hydrate(false);
                        $customerInvoices->contain(['InvoicedProducts']);
                        $customerInvoices->where(['customer_id'=>$customer['id']]);
                        $customerInvoices->where(['invoice_date >='=>$start_date]);
                        $customerInvoices->where(['invoice_date <='=>$end_date]);
                        $invoiceArrayMultiple = $customerInvoices->toArray()?$customerInvoices->toArray():[];
                        $achieved_total_cash_discounts[$customer['id']] = $this->get_achieved_total_cash_discounts($invoiceArrayMultiple);
                        foreach($invoiceArrayMultiple as $serial=>$invoiceArray){
                            $allWonOffers[$customer['id']][$serial] = $this->Common->getWonOffer($applicablePostfix, $invoiceArray, $offer_id);
                        }

                        if(sizeof($allWonOffers)>0){
                            foreach($allWonOffers as $customer_id=>$offers){
                                foreach($offers as $offer){
                                    $wonOffers[$customer_id] = $offer;
                                }
                            }
                        }
                    }
                }elseif($invoicing != array_flip(Configure::read('special_offer_invoicing'))['Single']){
                    if($conditions[0]['time_level']==array_flip(Configure::read('offer_time_level'))['Instant'] && $conditions[0]['context']==array_flip(Configure::read('offer_contexts'))['Invoice']){
                        $customerInvoices = TableRegistry::get('invoices')->find()->hydrate(false);
                        $customerInvoices->contain(['InvoicedProducts']);
                        $customerInvoices->where(['customer_id'=>$customer['id']]);
                        $customerInvoices->where(['invoice_date >='=>$start_date]);
                        $customerInvoices->where(['invoice_date <='=>$end_date]);
                        $invoiceArrayMultiple = $customerInvoices->toArray()?$customerInvoices->toArray():[];
                        $achieved_total_cash_discounts[$customer['id']] = $this->get_achieved_total_cash_discounts($invoiceArrayMultiple);
                        $wonOffers[$customer['id']] = $this->Common->getWonCumulativeOffer($condition_postfix, $invoiceArrayMultiple, $offer_id);
                    }
                }
            }
        }

        $finalArray = [];
        $wonOffers = array_filter($wonOffers);

        if(sizeof($wonOffers)>0){
            foreach($wonOffers as $customer_id=>$wonOffer){
                foreach($wonOffer as $k=>$offer){
                    if($offer['offer_type']==310000){
                        $finalArray[$customer_id]['type'] = array_flip(Configure::read('explore_offer_types'))['cash_discount'];
                        if(isset($finalArray[$customer_id]['cash_discount'])){
                            $finalArray[$customer_id]['cash_discount'] += $offer['value'];
                        }else{
                            $finalArray[$customer_id]['cash_discount'] = 0;
                            $finalArray[$customer_id]['cash_discount'] += $offer['value'];
                        }
                    }elseif($offer['offer_type']==312000){
                        $finalArray[$customer_id]['type'] = array_flip(Configure::read('explore_offer_types'))['trip'];
                        $finalArray[$customer_id]['awards'][$k]['cash_equivalent'] = $offer['value'];
                        $finalArray[$customer_id]['awards'][$k]['name'] = $offer['offer_name'];
                    }elseif($offer['offer_type']==313000){
                        $finalArray[$customer_id]['type'] = array_flip(Configure::read('explore_offer_types'))['gift'];
                        $finalArray[$customer_id]['awards'][$k]['cash_equivalent'] = $offer['value'];
                        $finalArray[$customer_id]['awards'][$k]['name'] = $offer['offer_name'];
                    }elseif($offer['offer_type']==317000){
                        $finalArray[$customer_id]['type'] = array_flip(Configure::read('explore_offer_types'))['product_bonus'];
                        if(isset($finalArray[$customer_id]['product_bonus'])){
                            $finalArray[$customer_id]['product_bonus'] += $offer['value'];
                        }else{
                            $finalArray[$customer_id]['product_bonus'] = 0;
                            $finalArray[$customer_id]['product_bonus'] += $offer['value'];
                        }
                    }
                }
            }
        }

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('finalArray', 'achieved_total_cash_discounts', 'customers'));
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
                $data = $this->request->data;
                $offer_id = $data['offer_id'];
                $start_date = strtotime($data['start_date']);
                $end_date = strtotime($data['end_date']);
                $unit_id = $data['unit_id'];
                $offerArray = $data['offer'];

                foreach($offerArray as $customer_id=>$offers){
                    $CustomerAwards = $this->CustomerAwards->newEntity();
                    $data['customer_id'] = $customer_id;
                    $customerAdministrativeUnitInfo = $this->AdministrativeUnits->get($unit_id);
                    $data['parent_global_id'] = $customerAdministrativeUnitInfo['global_id'];

                    $mark = 0;

                    if($offers['type']==array_flip(Configure::read('explore_offer_types'))['cash_discount']){
                        $data['award_account_code'] = 310000;
                        $data['amount'] = $offers['cash_discount'];
                        $data['remaining_amount'] = $offers['due'];
                        if($offers['cash_discount']>0){
                            $mark=1;
                        }
                    }elseif($offers['type']==array_flip(Configure::read('explore_offer_types'))['product_bonus']){
                        $data['award_account_code'] = 317000;
                        $data['amount'] = $offers['product_bonus'];
                        // work to do both in calculation.ctp and here
                    }elseif($offers['type']==array_flip(Configure::read('explore_offer_types'))['trip']){
                        $data['award_account_code'] = 312000;
                        // work to do both in calculation.ctp and here
                    }elseif($offers['type']==array_flip(Configure::read('explore_offer_types'))['gift']){
                        $data['award_account_code'] = 313000;
                        // work to do both in calculation.ctp and here
                    }

                    $data['customer_offer_id'] = $offer_id;
                    $data['offer_period_start'] = $start_date;
                    $data['offer_period_end'] = $end_date;
                    $data['action_status'] = Configure::read('customer_award_status')['pending'];
                    $data['created_by'] = $user['id'];
                    $data['created_date'] = time();

                    if($mark==1){
                        $CustomerAwards = $this->CustomerAwards->patchEntity($CustomerAwards, $data);
                        $this->CustomerAwards->save($CustomerAwards);
                    }
                }
            });

            echo 'Action taken successfully. Thank you!';
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getCalMaxDate($offer_id, $customer_id){
        $this->loadModel('CustomerAwards');
        $customerAwards = $this->CustomerAwards->find('all');
        $customerAwards->where(['customer_offer_id'=>$offer_id, 'customer_id'=>$customer_id]);
        $customerAwards->order('offer_period_end', 'DESC');
        $customerAwards->select(['offer_period_end']);
        $customerAwards->first();

        if($customerAwards->toArray()){
            $customerAwards = $customerAwards->toArray()[0];
            return $customerAwards->offer_period_end?$customerAwards->offer_period_end:0;
        }else{
            return 0;
        }
    }

    public function get_achieved_total_cash_discounts($invoiceArray){
        $total = 0;
        foreach($invoiceArray as $invoice){
            foreach($invoice['invoiced_products'] as $invoiced_product){
                if($invoiced_product['instant_discount']>0){
                    $total += $invoiced_product['instant_discount'];
                }
            }
        }
        return $total;
    }

}
