<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\SystemHelper;
use Cake\Collection\Collection;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;
use Cake\Http\Client\Request;
use CakePdf\Pdf\CakePdf;
use mPDF;
use Cake\I18n\Time;
use Symfony\Component\Console\Helper\Helper;

class ReportInvoiceAgeController extends AppController
{

    public function index()
    {
        $user = $this->Auth->user();
        $user_level = $user['level_no'];
        $parentLevels = [];
        $this->loadModel('AdministrativeLevels');
        $parent_info = $this->AdministrativeLevels->
        find('all', ['fields' => ['level_name', 'level_no'], 'conditions' => ['level_no >=' => $user_level, 'status' => 1]])->toArray();

        foreach ($parent_info as $parent_info) {
            $parentLevels[$parent_info['level_no']] = $parent_info['level_name'];
        }
        $this->set('parentLevels', $parentLevels);
    }


    public function loadReport($param)
    {
        //$this->autoRender = false;
        $this->loadModel('Stocks');
        $report = "";
        $this->loadModel('Categories');
        $this->loadModel('Warehouses');
        $user = $this->Auth->user();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            //pr($data);die;
            $conn = ConnectionManager::get('default');
            $limitStart = pow(2, (Configure::read('max_level_no') - $data['parent_level'] - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $data['parent_level']) * 5);
            $conn = ConnectionManager::get('default');

            if ($data['level'] < 5 && $data['parent_level'] < 5) {
                if ($data['parent_level'] != 4) {
                    $expression = (pow(2, (1 + 5 * $data['level'])) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $data['level'])));
                    //here it is :: 2^(1+5*level) -1  * 2^(5*(max_level-level))
                    $stmt = $conn->execute('
                    SELECT invoice_date,customer_unit_global_id  & ' . $expression . ' as GLOBAL_ID,due as DUE from invoices
                    WHERE  customer_unit_global_id-' . $data['global_id'] . ' >= ' . $limitStart . '
                    AND  customer_unit_global_id-' . $data['global_id'] . ' < ' . $limitEnd . '
                    OR customer_unit_global_id=' . $data['global_id'] . '
                    AND status =1
                    AND due>0
                    ');

                    $result = $stmt->fetchAll('assoc');
                } else {
                    $stmt = $conn->execute('
                    SELECT invoice_date,customer_unit_global_id as GLOBAL_ID ,due as DUE from invoices
                    WHERE   customer_unit_global_id = ' . $data['global_id'] . '
                    AND status =1
                    AND due>0
                    ');
                    $result = $stmt->fetchAll('assoc');
                }
            } else {
                $stmt = $conn->execute('
                    SELECT invoice_date,customer_id as GLOBAL_ID,due as DUE from invoices
                    WHERE  customer_unit_global_id-' . $data['global_id'] . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $data['global_id'] . ' < ' . $limitEnd . '
                    AND status=1
                    AND due>0
                    ');
                $result = $stmt->fetchAll('assoc');
            }

            //$collectionA = new Collection ($result);

            $today = Time::now();
            $timestamp_today = strtotime($today);

            $due_n_global_id_array = [];
            foreach ($result as $key => $invoice) {

                $temp = $timestamp_today - $invoice['invoice_date'];
                $date_difference_in_day = ($temp / (3600 * 24));

                if ($date_difference_in_day <= 30) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 1;
                }
                if ($date_difference_in_day > 30 && $date_difference_in_day <= 60) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 2;
                } else if ($date_difference_in_day > 60 && $date_difference_in_day <= 90) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 3;
                } else if ($date_difference_in_day > 90 && $date_difference_in_day <= 120) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 4;
                } else if ($date_difference_in_day > 120 && $date_difference_in_day <= 150) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 5;
                } else if ($date_difference_in_day > 150 && $date_difference_in_day <= 180) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 6;
                } else if ($date_difference_in_day > 180 && $date_difference_in_day <= 360) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 7;
                } else if ($date_difference_in_day > 360 && $date_difference_in_day <= 720) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 8;
                } else if ($date_difference_in_day > 720) {

                    unset($result[$key]['invoice_date']);
                    $result[$key]['invoice_date'] = 9;
                }

            }
            $CollectionA = new Collection($result);
            $CollectionB = $CollectionA->groupBy('GLOBAL_ID');
            $groupByGlobalID = $CollectionB->toArray();

            $groupByDaySpan = [];
            foreach ($groupByGlobalID as $invoice) {
                foreach ($invoice as $key => $final) {
                    $groupByDaySpan[$final['GLOBAL_ID']][$final['invoice_date']][$key] = $final;
                }
            }

            $credit_limit_customer = $this->Common->administrative_unit_wise_credit_limit($data['global_id'], $data['level']);

            $this->set('credit_limit', $credit_limit_customer);


            //die;
            //ksort($groupByDaySpan);
            //pr($groupByDaySpan);

            if ($data['level'] < 5) {
                $location_name = $this->Common->get_child_global_id_n_name($data['parent_level'], $data['global_id']);
                $this->set('name', $location_name);
            } elseif ($data['level'] == 5) {
                $customer_name = $this->Common->get_child_global_id_n_name($data['parent_level'], $data['global_id'], $group_by = 5);
                ///pr($customer_name);die;
                $this->set('name', $customer_name);
            }

            $this->set('invoice_age_report', $groupByDaySpan);

        }
        if ($param == 'report') {
            $this->viewBuilder()->layout('report');
            $this->set(compact('mainArr', 'invoice_age_report'));
            $this->set('data', $data);
        } elseif ($param == 'pdf') {
            $view = new View();
            $btnHide = 1;
            $view->layout = false;
            $view->set(compact('invoice_age_report', 'data', 'btnHide'));
            $view->viewPath = 'ReportInvoiceAge';
            $html = $view->render('load_report');
            $this->loadComponent('Common');
            $this->Common->getPdf($html);
        }
        $this->set('report', $report);
    }

    public function ajax($param)
    {
        $this->autoRender = false;
        if ($param == 'units'):
            $data = $this->request->data;
            $level = $data['level'];
            $user = $this->Auth->user();
            $result = $this->Common->get_administrative_units($level);
            $this->response->body(json_encode($result));
            return $this->response;
        endif;
        if ($param == 'child'):
            $data = $this->request->data;
            $level = $data['level'];
            $global_id = $data['global_id'];
            $user = $this->Auth->user();
            $this->loadModel('AdministrativeLevels');

            $result = $this->AdministrativeLevels->
            find('all', ['fields' => ['level_name', 'level_no'], 'conditions' => ['level_no >=' => $level, 'status' => 1]])->toArray();

            $this->response->body(json_encode($result));
            return $this->response;
        endif;

    }
}