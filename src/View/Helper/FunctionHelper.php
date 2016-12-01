<?php
namespace App\View\Helper;

use App\Model\Table\AdministrativeUnitsTable;
use App\Model\Table\ItemUnitsTable;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Function helper
 */
class FunctionHelper extends Helper
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function credit_closing_percentage($period_start, $period_end, $payment_start, $payment_end, $level, $unit=null){
        //working with sales (invoice)
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}
        if(!is_int($payment_start)){$payment_start = strtotime($payment_start);}
        if(!is_int($payment_end)){$payment_end = strtotime($payment_end);}

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoice_date >='=>$period_start,
            'invoice_date <='=>$period_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $sales->where(['customer_unit_global_id'=>$unit]);
            }
        }

        $sales->select(['sales_total'=>'SUM(net_total)']);
        $sales_total = $sales->first()['sales_total'];

        //working with payment
        $payments = TableRegistry::get('payments')->find('all', ['conditions'=>[
            'collection_date >='=>$payment_start,
            'collection_date <='=>$payment_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $payments->where(['customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $payments->where(['parent_global_id'=>$unit]);
            }
        }

        $payments->select(['payment_total'=>'SUM(amount)']);
        $payment_total = $payments->first()['payment_total'];
        $percentage = $sales_total>0?round(($payment_total/$sales_total)*100, 2):0;
        return $percentage;
    }

    public function sales_quantity($period_start, $period_end, $item, $item_unit, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if($item>0){
            $sales->where(['invoiced_products.item_id', $item]);
        }
        if($item_unit>0){
            $sales->where(['invoiced_products.manufacture_unit_id', $item_unit]);
        }
        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $sales->where(['invoices.customer_unit_global_id'=>$unit]);
            }
        }

        $sales->select(['sales_quantity'=>'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity'];
        return $sales_quantity;
    }

    public function sales_value($period_start, $period_end, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $sales->where(['invoices.customer_unit_global_id'=>$unit]);
            }
        }

        $sales->select(['sales_value'=>'SUM(invoices.net_total)']);
        $sales_value = $sales->first()['sales_value'];
        return $sales_value;
    }

    public function sales_target_achievement($period_start, $period_end, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $budget = TableRegistry::get('sales_budgets')->find('all', ['conditions'=>[
            'sales_budgets.budget_period_start >='=>$period_start,
            'sales_budgets.budget_period_end <='=>$period_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $budget->where(['sales_budgets.administrative_unit_id'=>$unit]);
            }
        }else{
            if($unit){
                $budget->where(['sales_budgets.administrative_unit_global_id'=>$unit]);
            }
        }

        $budget->select(['total_budget'=>'SUM(sales_budgets.sales_amount)']);
        $total_budget = $budget->first()['total_budget'];

        $salesBudgetConfiguration = TableRegistry::get('sales_budget_configurations')->find('all')->where(['status'=>1])->first();
        $sales_measure = $salesBudgetConfiguration['sales_measure'];
        if($sales_measure==1){
            $sales_quantity = self::sales_quantity($period_start, $period_end, null, null, $level, $unit=null);
            $achievement = $total_budget>0?round(($sales_quantity/$total_budget)*100, 2):0;
        }else{
            $sales_value = self::sales_value($period_start, $period_end, $level, $unit=null);
            $achievement = $total_budget>0?round(($sales_value/$total_budget)*100, 2):0;
        }
        return $achievement;
    }
}
