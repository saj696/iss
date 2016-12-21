<?php
namespace App\View\Helper;

use App\Model\Table\AdministrativeUnitsTable;
use App\Model\Table\ItemUnitsTable;
use Cake\Core\App;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use App\View\Helper\SystemHelper;

/**
 * Function helper
 */
class FunctionHelper extends Helper
{

    /**
     * Default configuration.
     *
     * @var array
     *
     */

    protected $_defaultConfig = [];

    public function postfix_converter($array){
        $ca = str_split($array); // condition array

        $o2n = [
            '+'=>0,
            '-'=>1,
            '*'=>2,
            '/'=>3,
            '>'=>4,
            '>='=>5,
            '<'=>6,
            '<='=>7,
            '=='=>8,
            '&&'=>9,
            '||'=>10,
            '='=>11,
            '&'=>12,
        ];

        $precedence = [
            0=>2,
            1=>2,
            2=>3,
            3=>3,
            4=>1,
            5=>1,
            6=>1,
            7=>1,
            8=>1,
            9=>1,
            10=>1,
            11=>1,
            12=>1,
        ];

        $fn = []; // function name
        $fa = []; // function array
        $cn=[];
        $stack = [];
        $stack[0] = '$';
        $indexOfStackTop = 0;
        $postfix = [];
        $postfixCurrentIndex=0;
        $functionSerial = 0;
        $operators = ['+', '-', '*', '/', '&', '|', '>', '<', '='];

        for($i=0; $i<sizeof($ca); $i++){
            if($ca[$i]=='(') {
                $indexOfStackTop++;
                $stack[$indexOfStackTop] = $ca[$i];
            } elseif(preg_match('/[a-z\s_]/i',$ca[$i])){
                do{
                    @$fn[$functionSerial] .= $ca[$i];
                    $i++;
                }while($ca[$i] != '[');
                $i++;

                do{
                    if($ca[$i] == ']'){
                        break;
                    }
                    @$fa[$functionSerial] .= $ca[$i];

                    $i++;
                }while(1);

                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['function'];
                $postfix[$postfixCurrentIndex]['name'] = $fn[$functionSerial];
                $postfix[$postfixCurrentIndex]['arg'] = $fa[$functionSerial];
                $postfixCurrentIndex++;
                $functionSerial++;
            } elseif(preg_match('/[0-9\s.]/i',$ca[$i])){
                unset($cn[0]);
                do{
                    @$cn[0] .= $ca[$i];
                    $i++;
                }while(preg_match('/[0-9\s.]/i',$ca[$i]));

                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['number'];
                $postfix[$postfixCurrentIndex]['number'] = $cn[0];
                $postfixCurrentIndex++;
                $i--;
            } elseif(in_array($ca[$i], $operators)){
                if(in_array($ca[$i+1], $operators)){
                    $currentOperator = $o2n[$ca[$i].$ca[$i+1]];
                    $i++;
                }else{
                    $currentOperator = $o2n[$ca[$i]];
                }
                if($stack[$indexOfStackTop]=='$'){
                    $indexOfStackTop++;
                    $stack[$indexOfStackTop] = $currentOperator;
                }elseif(preg_match('/[0-9\s.]/i',$stack[$indexOfStackTop])){
                    do{
                        if($precedence[$currentOperator]>$precedence[$stack[$indexOfStackTop]]){
                            $indexOfStackTop++;
                            $stack[$indexOfStackTop] = $currentOperator;
                            break;
                        }else{
                            $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                            $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];

                            $postfixCurrentIndex++;
                            $indexOfStackTop--;
                            if(!in_array($stack[$indexOfStackTop], $o2n)) {
                                $indexOfStackTop++;
                                $stack[$indexOfStackTop] = $currentOperator;
                                break;
                            }
                        }
                    }while(1);

                }elseif($stack[$indexOfStackTop] == '('){
                    $indexOfStackTop++;
                    $stack[$indexOfStackTop] = $ca[$i];
                }
            } elseif($ca[$i]==')'){
                do{
                    $stop=$stack[$indexOfStackTop];
                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                    $postfix[$postfixCurrentIndex]['operator'] = $o2n[$stop];

                    $postfixCurrentIndex++;
                    $indexOfStackTop--;
                    $stop=$stack[$indexOfStackTop];
                }while($stop != '(');

                $indexOfStackTop--;
            } elseif($ca[$i]=='$'){
                while($indexOfStackTop>0){
                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                    $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];

                    $indexOfStackTop--;
                    $postfixCurrentIndex++;
                }
            }
        }
        return $postfix;
    }

    public function postfix_evaluator($postfixArray){
        $indexOfTopStack = -1;
        $eStack = [];

        for($i=0; $i<sizeof($postfixArray); $i++){
            if($postfixArray[$i]['type']=='number'){
                $indexOfTopStack++;
                $eStack[$indexOfTopStack] = $postfixArray[$i]['number'];
            }else{
                $operand2 = $eStack[$indexOfTopStack];
                $indexOfTopStack--;
                $operand1 = $eStack[$indexOfTopStack];

                $result = $this->execute($operand1, $operand2, $postfixArray[$i]['operator']);

                $eStack[$indexOfTopStack] = $result;
            }
        }
        return $result;
    }

    public function execute($operand1, $operand2, $operator){
        $o2n = [
            '+'=>0,
            '-'=>1,
            '*'=>2,
            '/'=>3,
            '>'=>4,
            '>='=>5,
            '<'=>6,
            '<='=>7,
            '=='=>8,
            '&&'=>9,
            '||'=>10,
            '='=>11,
            '&'=>12,
        ];

        $char = array_flip($o2n)[$operator];
            switch($char){
                case "+":
                    $result = $operand1 + $operand2;
                    break;
                case "-";
                    $result = $operand1 - $operand2;
                    break;
                case "*";
                    $result = $operand1 * $operand2;
                    break;
                case "/";
                    $result = $operand1 / $operand2;
                    break;
                case ">";
                    if($operand1 > $operand2){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    break;
                case ">=";
                    if($operand1 >= $operand2){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    break;
                case "<";
                    if($operand1 < $operand2){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    break;
                case "<=";
                    if($operand1 <= $operand2){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    break;
                case "==";
                    if($operand1 == $operand2){
                        $result = 1;
                    }else{
                        $result = 0;
                    }
                    break;
                case "&&";
                    $result = $operand1 && $operand2;
                    break;
                case "||";
                    $result = $operand1 || $operand2;
                    break;
                case "&";
                    $result = $operand1 & $operand2;
                    break;
            }

        return $result;
    }

    public function credit_closing_percentage($period_start, $period_end, $payment_start, $payment_end, $level, $unit=null, $contextArray = []){
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
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_total'=>'SUM(net_total)']);
        $sales_total = $sales->first()['sales_total']?$sales->first()['sales_total']:0;

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
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $payments->where(['parent_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $payments->select(['payment_total'=>'SUM(amount)']);
        $payment_total = $payments->first()['payment_total']?$payments->first()['payment_total']:0;
        $percentage = $sales_total>0?round(($payment_total/$sales_total)*100, 2):0;
        return $percentage?round($percentage, 2):0;
    }

    public function sales_quantity($period_start, $period_end, $itemArray, $level, $unit=null, $contextArray = []){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $item_unit_ids = [];
        if(sizeof($itemArray)>0){
            foreach($itemArray as $item){
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$item['item_name']]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$item['unit_name']]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id'=>$item_id, 'unit_id'=>$unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if(sizeof($item_unit_ids)>0){
            foreach($item_unit_ids as $key=>$item_unit){
                if($key==0){
                    if($item_unit['item_id']>0){
                        $sales->where(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }
                    if($item_unit['unit_id']>0){
                        $sales->where(['invoiced_products.manufacture_unit_id'=> $item_unit['unit_id']]);
                    }
                }else{
                    if($item_unit['item_id']>0 && !$item_unit['unit_id']){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }elseif($item_unit['item_id']>0 && $item_unit['unit_id']>0){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id'], 'invoiced_products.manufacture_unit_id'=>$item_unit['unit_id']]);
                    }
                }
            }
        }

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['invoices.customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_quantity'=>'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity']?$sales->first()['sales_quantity']:0;
        return $sales_quantity;
    }

    public function sales_value($period_start, $period_end, $itemArray, $level, $unit=null, $contextArray = []){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $item_unit_ids = [];
        if(sizeof($itemArray)>0){
            foreach($itemArray as $item){
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$item['item_name']]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$item['unit_name']]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id'=>$item_id, 'unit_id'=>$unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if(sizeof($item_unit_ids)>0){
            foreach($item_unit_ids as $key=>$item_unit){
                if($key==0){
                    if($item_unit['item_id']>0){
                        $sales->where(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }
                    if($item_unit['unit_id']>0){
                        $sales->where(['invoiced_products.manufacture_unit_id'=> $item_unit['unit_id']]);
                    }
                }else{
                    if($item_unit['item_id']>0 && !$item_unit['unit_id']){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }elseif($item_unit['item_id']>0 && $item_unit['unit_id']>0){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id'], 'invoiced_products.manufacture_unit_id'=>$item_unit['unit_id']]);
                    }
                }
            }
        }

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['invoices.customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_value'=>'SUM(invoices.net_total)']);
        $sales_value = $sales->first()['sales_value']?$sales->first()['sales_value']:0;
        return $sales_value;
    }

    public function sales_target_achievement($period_start, $period_end, $itemArray, $level, $unit=null){
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
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $budget->where(['sales_budgets.administrative_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $budget->select(['total_budget'=>'SUM(sales_budgets.sales_amount)']);
        $total_budget = $budget->first()['total_budget']?$budget->first()['total_budget']:0;

        $salesBudgetConfiguration = TableRegistry::get('sales_budget_configurations')->find('all')->where(['status'=>1])->first();
        $sales_measure = $salesBudgetConfiguration['sales_measure'];
        if($sales_measure==1){
            $sales_quantity = self::sales_quantity($period_start, $period_end, $itemArray, $level, $unit=null);
            $achievement = $total_budget>0?round(($sales_quantity/$total_budget)*100, 2):0;
        }else{
            $sales_value = self::sales_value($period_start, $period_end, $itemArray, $level, $unit=null);
            $achievement = $total_budget>0?round(($sales_value/$total_budget)*100, 2):0;
        }
        return $achievement;
    }

    public function credit_note_value($period_start, $period_end, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $credit = TableRegistry::get('credit_notes')->find('all', ['conditions'=>[
            'credit_notes.date >='=>$period_start,
            'credit_notes.date <='=>$period_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $credit->where(['credit_notes.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $credit->where(['credit_notes.parent_global_id'=>$adminUnitInfo['global_id']]);
            }
        }
        $credit->select(['total_after_demurrage'=>'SUM(credit_notes.total_after_demurrage)']);
        $total_after_demurrage = $credit->first()['total_after_demurrage']?$credit->first()['total_after_demurrage']:0;
        return round($total_after_demurrage, 2);
    }

    public function cash_sales_quantity($period_start, $period_end, $itemArray, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $item_unit_ids = [];
        if(sizeof($itemArray)>0){
            foreach($itemArray as $item){
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$item['item_name']]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$item['unit_name']]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id'=>$item_id, 'unit_id'=>$unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
            'invoices.invoice_type'=>1
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if(sizeof($item_unit_ids)>0){
            foreach($item_unit_ids as $key=>$item_unit){
                if($key==0){
                    if($item_unit['item_id']>0){
                        $sales->where(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }
                    if($item_unit['unit_id']>0){
                        $sales->where(['invoiced_products.manufacture_unit_id'=> $item_unit['unit_id']]);
                    }
                }else{
                    if($item_unit['item_id']>0 && !$item_unit['unit_id']){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }elseif($item_unit['item_id']>0 && $item_unit['unit_id']>0){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id'], 'invoiced_products.manufacture_unit_id'=>$item_unit['unit_id']]);
                    }
                }
            }
        }

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['invoices.customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_quantity'=>'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity']?$sales->first()['sales_quantity']:0;
        return $sales_quantity;
    }

    public function cash_sales_value($period_start, $period_end, $itemArray, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $item_unit_ids = [];
        if(sizeof($itemArray)>0){
            foreach($itemArray as $item){
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$item['item_name']]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$item['unit_name']]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id'=>$item_id, 'unit_id'=>$unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end,
            'invoices.invoice_type'=>2
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if(sizeof($item_unit_ids)>0){
            foreach($item_unit_ids as $key=>$item_unit){
                if($key==0){
                    if($item_unit['item_id']>0){
                        $sales->where(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }
                    if($item_unit['unit_id']>0){
                        $sales->where(['invoiced_products.manufacture_unit_id'=> $item_unit['unit_id']]);
                    }
                }else{
                    if($item_unit['item_id']>0 && !$item_unit['unit_id']){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }elseif($item_unit['item_id']>0 && $item_unit['unit_id']>0){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id'], 'invoiced_products.manufacture_unit_id'=>$item_unit['unit_id']]);
                    }
                }
            }
        }

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['invoices.customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_value'=>'SUM(invoices.net_total)']);
        $sales_value = $sales->first()['sales_value']?$sales->first()['sales_value']:0;
        return $sales_value;
    }

    public function credit_dues($period_start, $period_end, $level, $unit=null){
        if(!is_int($period_start)){$period_start = strtotime($period_start);}
        if(!is_int($period_end)){$period_end = strtotime($period_end);}

        $sales = TableRegistry::get('invoices')->find('all', ['conditions'=>[
            'invoices.invoice_date >='=>$period_start,
            'invoices.invoice_date <='=>$period_end
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $sales->where(['invoices.customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $sales->where(['invoices.customer_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['total_due'=>'SUM(invoices.due)']);
        $due = $sales->first()['total_due']?$sales->first()['total_due']:0;
        return $due;
    }

    public function collection($period_start, $period_end, $level, $unit){
        $payments = TableRegistry::get('payments')->find('all', ['conditions'=>[
            'collection_date >='=>$period_start,
            'collection_date <='=>$period_end,
        ]]);

        if($level==Configure::read('max_level_no')+1){
            if($unit){
                $payments->where(['customer_id'=>$unit]);
            }
        }else{
            if($unit){
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $payments->where(['parent_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $payments->select(['payment_total'=>'SUM(amount)']);
        $payment_total = $payments->first()['payment_total']?$payments->first()['payment_total']:0;
        return $payment_total;
    }

    public function sales_budget($period_start, $period_end, $level, $unit){
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
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
                $budget->where(['sales_budgets.administrative_unit_global_id'=>$adminUnitInfo['global_id']]);
            }
        }

        $budget->select(['total_budget'=>'SUM(sales_budgets.sales_amount)']);
        $total_budget = $budget->first()['total_budget']?$budget->first()['total_budget']:0;
        return $total_budget;
    }

    public function location_age($unit){
        $location_info = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['global_id'=>$unit]])->first();
        return $location_info['created_date'];
    }

    public function max_due_invoice_age($level, $unit=null){
        return 'work to do';
//        $invoice = TableRegistry::get('invoices')->find('all', ['conditions'=>['id'=>$invoice]])->first();
//        return $invoice['invoice_date'];
    }

    public function payment_date($contextArray = []){
        if($contextArray['due']>0){
            return strtotime('01-01-2020');
        }else{
            return $contextArray['updated_date'];
        }
    }

    public function invoice_quantity($itemArray){
        $item_unit_ids = [];
        if(sizeof($itemArray)>0){
            foreach($itemArray as $item){
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$item['item_name']]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$item['unit_name']]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id'=>$item_id, 'unit_id'=>$unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all');
        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if(sizeof($item_unit_ids)>0){
            foreach($item_unit_ids as $key=>$item_unit){
                if($key==0){
                    if($item_unit['item_id']>0){
                        $sales->where(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }
                    if($item_unit['unit_id']>0){
                        $sales->where(['invoiced_products.manufacture_unit_id'=> $item_unit['unit_id']]);
                    }
                }else{
                    if($item_unit['item_id']>0 && !$item_unit['unit_id']){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id']]);
                    }elseif($item_unit['item_id']>0 && $item_unit['unit_id']>0){
                        $sales->orWhere(['invoiced_products.item_id'=> $item_unit['item_id'], 'invoiced_products.manufacture_unit_id'=>$item_unit['unit_id']]);
                    }
                }
            }
        }
        $sales->select(['sales_quantity'=>'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity']?$sales->first()['sales_quantity']:0;
        return $sales_quantity;
    }

    public function no_of_immediate_child($unit){
        $location_info = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['global_id'=>$unit]])->first();
        return $location_info['no_of_direct_successors'];
    }

    public function invoice_item_payment_age($itemName, $unitName, $invoice){
        $item_info = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$itemName]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$unitName]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];

        if(sizeof($invoice)>0){
            foreach($invoice['invoiced_products'] as $invoiced_product){
                if($invoiced_product['item_id']==$item_id && $invoiced_product['manufacture_unit_id']==$unit_id){
                    if($invoice['invoice_type']==1){
                        if($invoiced_product['due']==0){
                            return ($invoiced_product['updated_date']-$invoiced_product['delivery_date'])/3600*24;
                        }else{
                            return 999999;
                        }
                    }elseif($invoice['invoice_type']==2){
                        if($invoiced_product['due']==0){
                            return ($invoiced_product['updated_date']-$invoiced_product['delivery_date'])/3600*24;
                        }else{
                            return 999999;
                        }
                    }
                }
            }
        }
    }

    public function is_mango_customer($contextArray = []){
        $customer_info = TableRegistry::get('customers')->find('all', ['conditions'=>['id'=>$contextArray['customer_id']]])->first();

        if($customer_info['is_mango']==1){
            return $customer_info['is_mango'];
        }else{
            return 0;
        }
    }

    public function is_current_item($itemName, $unitName = null, $contextArray = []){
        return 'not needed';
    }

    public function item_quantity($itemName, $unitName = null, $contextArray = []){
        $item_info = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>$itemName]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>$unitName]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];
        $sum = 0;

        if(sizeof($contextArray)>0){
            if($item_id>0 && $unitName==null){
                if (false !== $key = array_search($item_id, $contextArray['item_id'])) {
                    $sum += $contextArray['product_quantity'][$key];
                }
            }elseif($item_id>0 && $unit_id>0){
                if ((false !== $key = array_search($item_id, $contextArray['item_id'])) && (false !== $key = array_search($unit_id, $contextArray['manufacture_unit_id']))) {
                    $sum += $contextArray['product_quantity'][$key];
                }
            }
        }
        return $sum?$sum:0;
    }

    public function is_cash_invoice($contextArray = []){
        if($contextArray['invoice_type']==1){
            return 1;
        }else{
            return 0;
        }
    }

    public function execution_time($contextArray = []){

    }
}
