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

//        App::import('Helper', 'FunctionHelper');
//        $FunctionHelper = new FunctionHelper(new View());
//
//        $ar = "2<3<4".'$';
//
//        $res = $FunctionHelper->postfix_converter($ar);
//        $result = $FunctionHelper->postfix_evaluator($res);
//        echo $result;
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

        $conditions = json_decode($offer['conditions'], true);
        $condition_postfix = json_decode($offer['condition_postfix'], true);
        $wonOffers = [];

        foreach($customers as $customer){
            $max_calculated_date = $this->getCalMaxDate($offer_id, $customer['id']);

            if($start_date>$max_calculated_date){
                $invoicing = $offer['invoicing'];
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

        $this->autoRender = false;

        $final = [];
        $wonOffers = array_filter($wonOffers);

        if(sizeof($wonOffers)>0){
            foreach($wonOffers as $customer_id=>$wonOffer){
                foreach($wonOffer as $k=>$offer){
                    if($offer['offer_type']==310000){
                        if(isset($final[$customer_id]['cash_discount'])){
                            $final[$customer_id]['cash_discount'] += $offer['value'];
                        }else{
                            $final[$customer_id]['cash_discount'] = 0;
                            $final[$customer_id]['cash_discount'] += $offer['value'];
                        }
                    }elseif($offer['offer_type']==312000 || $offer['offer_type']==313000){
                        $final[$customer_id]['awards'][$k]['cash_equivalent'] = $offer['value'];
                        $final[$customer_id]['awards'][$k]['name'] = $offer['offer_name'];
                    }elseif($offer['offer_type']==317000){
                        if(isset($final[$customer_id]['product_bonus'])){
                            $final[$customer_id]['product_bonus'] += $offer['value'];
                        }else{
                            $final[$customer_id]['product_bonus'] = 0;
                            $final[$customer_id]['product_bonus'] += $offer['value'];
                        }
                    }
                }
            }
        }


        echo '<pre>';
        print_r($final);
        echo '</pre>';
        exit;
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
