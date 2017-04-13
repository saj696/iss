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

class ReportProductWiseSalesController extends AppController
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
            $start_date = strtotime($data['start_date']);
            $end_date = strtotime($data['end_date']);
            $global_id = $data['global_id'];
            $level = $data['parent_level'];
            //$item_id = $this->request->data['item_id'];

            $this->set('start_date', $data['start_date']);
            $this->set('end_date', $data['end_date']);
            $administrative_unit = TableRegistry::get('administrative_units')->findByGlobalId($global_id)->first();
            $this->set('administrative_unit', $administrative_unit['unit_name']);
            $limitStart = pow(2, (Configure::read('max_level_no') - $level - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $level) * 5);

            $conn = ConnectionManager::get('default');

            if($level == Configure::read('max_level_no')){
                $stmt = $conn->execute('
                    SELECT  RESULT_SET.item_id,RESULT_SET.manufacture_unit_id,RESULT_SET.CSH_BONUS_QUANT,
                    RESULT_SET.CSH_PROD_QUANT,RESULT_SET.cash_sales_price,
                    RESULT_SET.IIID,RESULT_SET.MUUID,
                    RESULT_SET.CR_BONUS_QUANT,
                    RESULT_SET.CR_PROD_QUANT,
                    RESULT_SET.credit_sales_price,
                    RESULT_SET.code,
                    RESULT_SET.ITEM_NAME,
                    RESULT_SET.UNIT_NAME,
                    RESULT_SET.UNIT_TYPE,
                    RESULT_SET.UNIT_SIZE,
                    RESULT_SET.CONVERTED_QUANTITY
                     FROM (SELECT * FROM (SELECT * from (select C.item_id, C.manufacture_unit_id, C.CSH_BONUS_QUANT, C.CSH_PROD_QUANT, D.cash_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CSH_PROD_QUANT,SUM(bonus_quantity) as CSH_BONUS_QUANT FROM `invoiced_products`
                      WHERE invoice_type=1
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id =' . $global_id . '
                      group by item_id,manufacture_unit_id) as C
                    INNER JOIN
                    (SELECT item_id as iid, manufacture_unit_id as muid, cash_sales_price from prices where status=1) as D
                    ON  C.item_id=D.iid and C.manufacture_unit_id=D.muid) as E

                    LEFT OUTER JOIN

                    (select A.item_id as IIID, A.manufacture_unit_id as MUUID, A.CR_BONUS_QUANT, A.CR_PROD_QUANT, B.credit_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CR_PROD_QUANT,SUM(bonus_quantity) as CR_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=2
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id =' . $global_id . '
                    group by item_id,manufacture_unit_id) as A

                    INNER JOIN
                    (SELECT item_id as iid, manufacture_unit_id as muid, credit_sales_price from prices where status=1) as B
                    ON  A.item_id=B.iid and A.manufacture_unit_id=B.muid) as F
                    ON E.item_id= F.IIID and E.manufacture_unit_id= F.MUUID

                    UNION

                    SELECT * from (select C.item_id, C.manufacture_unit_id, C.CSH_BONUS_QUANT, C.CSH_PROD_QUANT,
                    D.cash_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CSH_PROD_QUANT,SUM(bonus_quantity) as CSH_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=1
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id =' . $global_id . '
                    group by item_id,manufacture_unit_id) as C

                    INNER JOIN

                    (SELECT item_id as iid, manufacture_unit_id as muid, cash_sales_price from prices where status=1) as D
                    ON  C.item_id=D.iid and C.manufacture_unit_id=D.muid) as E

                    RIGHT OUTER JOIN


                    (select A.item_id as IIID, A.manufacture_unit_id as MUUID, A.CR_BONUS_QUANT, A.CR_PROD_QUANT, B.credit_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CR_PROD_QUANT,SUM(bonus_quantity) as CR_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=2
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id =' . $global_id . '
                    group by item_id,manufacture_unit_id) as A

                    INNER JOIN

                    (SELECT item_id as iid, manufacture_unit_id as muid, credit_sales_price from prices where status=1) as B
                    ON  A.item_id=B.iid and A.manufacture_unit_id=B.muid
                    )  as F
                    ON E.item_id= F.IIID and E.manufacture_unit_id= F.MUUID
                    ) AS FINAL

                     INNER JOIN
                     (SELECT CN.item_id as iu_item_id, CN.manufacture_unit_id as iu_manufacture_unit_id, CN.code, CN.ITEM_NAME,
                      UNIT_FINAL.unit_name, UNIT_FINAL.unit_type, UNIT_FINAL.unit_size, UNIT_FINAL.converted_quantity FROM
                     (SELECT * FROM (SELECT item_id,code,manufacture_unit_id from item_units where status=1)  as IU

                      INNER JOIN
                      (SELECT id as IT_ID ,name as ITEM_NAME FROM items) as ITEM_FINAL
                       ON IU.item_id = ITEM_FINAL.IT_ID) as CN


                      INNER JOIN
                      (SELECT id,unit_name,unit_type, unit_size, converted_quantity from units) as UNIT_FINAL
                       ON
                      CN.manufacture_unit_id= UNIT_FINAL.id

                    ) AS IU
                      ON
                      (IU.iu_item_id = FINAL.item_id
                      AND IU.iu_manufacture_unit_id = FINAL.manufacture_unit_id)
                      OR
                       (IU.iu_item_id = FINAL.IIID
                      AND IU.iu_manufacture_unit_id = FINAL.MUUID)

                             ) as RESULT_SET');
            }else{
                $stmt = $conn->execute('
                    SELECT  RESULT_SET.item_id,RESULT_SET.manufacture_unit_id,RESULT_SET.CSH_BONUS_QUANT,
                    RESULT_SET.CSH_PROD_QUANT,RESULT_SET.cash_sales_price,
                    RESULT_SET.IIID,RESULT_SET.MUUID,
                    RESULT_SET.CR_BONUS_QUANT,
                    RESULT_SET.CR_PROD_QUANT,
                    RESULT_SET.credit_sales_price,
                    RESULT_SET.code,
                    RESULT_SET.ITEM_NAME,
                    RESULT_SET.UNIT_NAME,
                    RESULT_SET.UNIT_TYPE,
                    RESULT_SET.UNIT_SIZE,
                    RESULT_SET.CONVERTED_QUANTITY
                     FROM (SELECT * FROM (SELECT * from (select C.item_id, C.manufacture_unit_id, C.CSH_BONUS_QUANT, C.CSH_PROD_QUANT, D.cash_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CSH_PROD_QUANT,SUM(bonus_quantity) as CSH_BONUS_QUANT FROM `invoiced_products`
                      WHERE invoice_type=1
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id - ' . $global_id . ' >= ' . $limitStart . '
                      AND customer_unit_global_id - ' . $global_id . ' < ' . $limitEnd . '
                      group by item_id,manufacture_unit_id) as C
                    INNER JOIN
                    (SELECT item_id as iid, manufacture_unit_id as muid, cash_sales_price from prices where status=1) as D
                    ON  C.item_id=D.iid and C.manufacture_unit_id=D.muid) as E

                    LEFT OUTER JOIN

                    (select A.item_id as IIID, A.manufacture_unit_id as MUUID, A.CR_BONUS_QUANT, A.CR_PROD_QUANT, B.credit_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CR_PROD_QUANT,SUM(bonus_quantity) as CR_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=2
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id - ' . $global_id . ' >= ' . $limitStart . '
                      AND customer_unit_global_id - ' . $global_id . ' < ' . $limitEnd . '
                    group by item_id,manufacture_unit_id) as A

                    INNER JOIN
                    (SELECT item_id as iid, manufacture_unit_id as muid, credit_sales_price from prices where status=1) as B
                    ON  A.item_id=B.iid and A.manufacture_unit_id=B.muid) as F
                    ON E.item_id= F.IIID and E.manufacture_unit_id= F.MUUID

                    UNION

                    SELECT * from (select C.item_id, C.manufacture_unit_id, C.CSH_BONUS_QUANT, C.CSH_PROD_QUANT,
                    D.cash_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CSH_PROD_QUANT,SUM(bonus_quantity) as CSH_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=1
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id - ' . $global_id . ' >= ' . $limitStart . '
                      AND customer_unit_global_id - ' . $global_id . ' < ' . $limitEnd . '
                    group by item_id,manufacture_unit_id) as C

                    INNER JOIN

                    (SELECT item_id as iid, manufacture_unit_id as muid, cash_sales_price from prices where status=1) as D
                    ON  C.item_id=D.iid and C.manufacture_unit_id=D.muid) as E

                    RIGHT OUTER JOIN


                    (select A.item_id as IIID, A.manufacture_unit_id as MUUID, A.CR_BONUS_QUANT, A.CR_PROD_QUANT, B.credit_sales_price from (SELECT item_id,manufacture_unit_id, SUM(product_quantity) as CR_PROD_QUANT,SUM(bonus_quantity) as CR_BONUS_QUANT FROM `invoiced_products`
                    where invoice_type=2
                      AND invoice_date <=' . $end_date . '
                      AND invoice_date >= ' . $start_date . '
                      AND customer_unit_global_id - ' . $global_id . ' >= ' . $limitStart . '
                      AND customer_unit_global_id - ' . $global_id . ' < ' . $limitEnd . '
                    group by item_id,manufacture_unit_id) as A

                    INNER JOIN

                    (SELECT item_id as iid, manufacture_unit_id as muid, credit_sales_price from prices where status=1) as B
                    ON  A.item_id=B.iid and A.manufacture_unit_id=B.muid
                    )  as F
                    ON E.item_id= F.IIID and E.manufacture_unit_id= F.MUUID
                    ) AS FINAL

                     INNER JOIN
                     (SELECT CN.item_id as iu_item_id, CN.manufacture_unit_id as iu_manufacture_unit_id, CN.code, CN.ITEM_NAME,
                      UNIT_FINAL.unit_name, UNIT_FINAL.unit_type, UNIT_FINAL.unit_size, UNIT_FINAL.converted_quantity FROM
                     (SELECT * FROM (SELECT item_id,code,manufacture_unit_id from item_units where status=1)  as IU

                      INNER JOIN
                      (SELECT id as IT_ID ,name as ITEM_NAME FROM items) as ITEM_FINAL
                       ON IU.item_id = ITEM_FINAL.IT_ID) as CN


                      INNER JOIN
                      (SELECT id,unit_name,unit_type, unit_size, converted_quantity from units) as UNIT_FINAL
                       ON
                      CN.manufacture_unit_id= UNIT_FINAL.id

                    ) AS IU
                      ON
                      (IU.iu_item_id = FINAL.item_id
                      AND IU.iu_manufacture_unit_id = FINAL.manufacture_unit_id)
                      OR
                       (IU.iu_item_id = FINAL.IIID
                      AND IU.iu_manufacture_unit_id = FINAL.MUUID)

                             ) as RESULT_SET');
            }



            $sales = $stmt->fetchAll('assoc');
            $this->set('sales', $sales);
        }
        if ($param == 'report') {
            $this->viewBuilder()->layout('report');
            $this->set(compact('mainArr', 'sales'));
            $this->set('data', $data);
        } elseif ($param == 'pdf') {
            $view = new View();
            $btnHide = 1;
            $view->layout = false;
            $view->set(compact('sales', 'data', 'btnHide'));
            $view->viewPath = 'ReportProductWiseSales';
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