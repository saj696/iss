<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;
use Cake\Http\Client\Request;
use CakePdf\Pdf\CakePdf;
use mPDF;

class ReportInvoiceWiseAgingController extends AppController
{

    public function index()
    {
        $this->loadModel('Categories');
        $user = $this->Auth->user();
        $user_level = $user['level_no'];
        $parentLevels = [];
        $this->loadModel('AdministrativeLevels');
        $parents = $this->AdministrativeLevels->find('all', ['fields' => ['level_name', 'level_no'], 'conditions' => ['level_no >=' => $user_level, 'status' => 1]])->toArray();

        foreach ($parents as $parent_info) {
            $parentLevels[$parent_info['level_no']] = $parent_info['level_name'];
        }

        $this->set(compact('parentLevels'));
    }

    public function loadReport($param)
    {
        $this->loadModel('Stocks');
        $report = "";
        $this->loadModel('Categories');
        $this->loadModel('Warehouses');
        $user = $this->Auth->user();
        $data = $this->request->data;

        if ($this->request->is('post')) {
            $global_id = $data['global_id'];
            $level = $data['parent_level'];

            $administrative_unit = TableRegistry::get('administrative_units')->findByGlobalId($global_id)->first();
            $administrative_unit =  $administrative_unit['unit_name'];

            $limitStart = pow(2,(Configure::read('max_level_no')- $level-1)*5);
            $limitEnd = pow(2,(Configure::read('max_level_no')- $level)*5);

            $invoices = TableRegistry::get('invoices')->find();
            //$invoices->contain(['Customers']);
            if($level == Configure::read('max_level_no')){
                $invoices->where('customer_unit_global_id= '.$global_id);
            }else{
                $invoices->where('customer_unit_global_id -'. $global_id .'>= '.$limitStart);
                $invoices->where('customer_unit_global_id -'. $global_id .'< '.$limitEnd);
            }

            $invoices = $invoices->toArray();
            $mainArr = [];
            foreach($invoices as $invoice){
                $mainArr[$invoice['customer_id']][] = $invoice;
            }
        }

        if ($param == 'report') {
            $this->viewBuilder()->layout('report');
            $this->set(compact('mainArr', 'administrative_unit'));
            $this->set('data', $data);
        } elseif ($param == 'pdf') {
            $view = new View();
            $btnHide = 1;
            $view->layout = false;
            $view->set(compact('mainArr', 'data', 'btnHide', 'administrative_unit'));
            $view->viewPath = 'ReportInvoiceWiseAging';
            $html = $view->render('load_report');
            $this->loadComponent('Common');
            $this->Common->getPdf($html);
        }
        $this->set('report', $report);
    }

    public function ajax($param)
    {
        $this->autoRender = false;
        $data = $this->request->data;
        $level = $data['level'];
        $user = $this->Auth->user();
        $data = $this->Common->get_administrative_units($level);
        $this->response->body(json_encode($data));
        return $this->response;

    }
}