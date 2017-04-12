<?php
namespace App\View\Helper;

use App\Model\Table\AdministrativeUnitsTable;
use App\Model\Table\ItemUnitsTable;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use App\View\Helper\SystemHelper;
use Cake\Collection\Collection;
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

    public function postfix_converter($array)
    {
        $ca = str_split($array); // condition array

        $o2n = [
            '+' => 0,
            '-' => 1,
            '*' => 2,
            '/' => 3,
            '>' => 4,
            '>=' => 5,
            '<' => 6,
            '<=' => 7,
            '==' => 8,
            '&&' => 9,
            '||' => 10,
            '!=' => 11,
        ];

        $precedence = [
            0 => 2,
            1 => 2,
            2 => 3,
            3 => 3,
            4 => 1,
            5 => 1,
            6 => 1,
            7 => 1,
            8 => 1,
            9 => 1,
            10 => 1,
            11 => 1,
        ];

        $fn = []; // function name
        $fa = []; // function array
        $cn = [];
        $stack = [];
        //$stack[0] = ;
        $indexOfStackTop = -1;
        $postfix = [];
        $postfixCurrentIndex = 0;
        $functionSerial = 0;
        $operators = ['+', '-', '*', '/', '&', '|', '>', '<', '=', '!'];
        $indexOfCurrentOperatorInStack = -1;
        $indexOfProbablyMarkedOperatorInStack = -1;
        $indexOfProbablyMarkedOperatorInPostfix = -1;
        $indexOfActuallyMarkedOperatorInStack = -1;
        $probableRangeStart = -1;
        $actualRangeStart = -1;
        $state = 0;
        $is_marked_counter = 0;
        // $elementType: [0=>'Number', 1=>'(', 2=>')', 3=>'item_unit_quantity', 4=>'compOp', 5=>'other']


        for ($i = 0; $i < sizeof($ca); $i++) {
            if ($ca[$i] == '(') {
                $elementType = 1;
                $indexOfStackTop++;
                $stack[$indexOfStackTop] = -2;
            } elseif (preg_match('/[a-z\s_]/i', $ca[$i])) {
                do {
                    @$fn[$functionSerial] .= $ca[$i];
                    $i++;
                } while ($ca[$i] != '[');
                $i++;

                do {
                    if ($ca[$i] == ']') {
                        break;
                    }
                    @$fa[$functionSerial] .= $ca[$i];

                    $i++;
                } while (1);

                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['function'];
                $postfix[$postfixCurrentIndex]['name'] = $fn[$functionSerial];

                if (strpos($fn[$functionSerial], 'item_unit_quantity') !== false) {
                    $elementType = 3;
                } else {
                    $elementType = 5;
                }

                $postfix[$postfixCurrentIndex]['arg'] = $fa[$functionSerial];
                $postfixCurrentIndex++;
                $functionSerial++;
            } elseif (preg_match('/[0-9\s.]/i', $ca[$i])) {
                unset($cn[0]);
                do {
                    @$cn[0] .= $ca[$i];
                    $i++;
                } while (preg_match('/[0-9\s.]/i', $ca[$i]));

                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['number'];
                $postfix[$postfixCurrentIndex]['number'] = $cn[0];
                $elementType = 0;
                $postfixCurrentIndex++;
                $i--;
            } elseif (in_array($ca[$i], $operators)) {
                if (in_array($ca[$i + 1], $operators)) {
                    $currentOperator = $o2n[$ca[$i] . $ca[$i + 1]];
                    $i++;
                } else {
                    $currentOperator = $o2n[$ca[$i]];
                }

                if (($currentOperator == $o2n['+']) && ($postfix[$postfixCurrentIndex - 1]['type'] == 'function') && (strpos($postfix[$postfixCurrentIndex - 1]['name'], 'item_unit_quantity') !== false)) {
                    $postfix[$postfixCurrentIndex - 1]['plus_found_after_item_unit_quantity'] = 1;
                }

                if (self::check_comparison_operator($currentOperator)) {
                    $elementType = 4;
                } else {
                    $elementType = 5;
                }

                if ($indexOfStackTop == -1) { //if($stack[$indexOfStackTop]=='$')
                    $indexOfStackTop++;
                    $stack[$indexOfStackTop] = $currentOperator;
                    $indexOfCurrentOperatorInStack = $indexOfStackTop;
                } elseif ($stack[$indexOfStackTop] > -1) {
                    if ($precedence[$currentOperator] <= $precedence[intval($stack[$indexOfStackTop])]) {
                        if ($indexOfActuallyMarkedOperatorInStack == $indexOfStackTop && $is_marked_counter == 0) {
                            $postfix[$postfixCurrentIndex]['is_marked'] = 1;
                            $is_marked_counter = 1;
                        } else {
                            $postfix[$postfixCurrentIndex]['is_marked'] = 0;
                        }
                        $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                        $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];

                        $postfixCurrentIndex++;
                        $indexOfStackTop--;
                    }

                    $indexOfStackTop++;
                    $stack[$indexOfStackTop] = $currentOperator;
                    $indexOfCurrentOperatorInStack = $indexOfStackTop;
                } elseif ($stack[$indexOfStackTop] == -2) {
                    $indexOfStackTop++;
                    $stack[$indexOfStackTop] = $currentOperator;
                    $indexOfCurrentOperatorInStack = $indexOfStackTop;
                }
            } elseif ($ca[$i] == ')') {
                do {
                    $stop = $stack[$indexOfStackTop];
                    if ($stop == -2) {
                        break;
                    }

                    if ($indexOfActuallyMarkedOperatorInStack == $indexOfStackTop && $is_marked_counter == 0) {
                        $postfix[$postfixCurrentIndex]['is_marked'] = 1;
                        $is_marked_counter = 1;
                    } else {
                        $postfix[$postfixCurrentIndex]['is_marked'] = 0;
                    }
                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                    $postfix[$postfixCurrentIndex]['operator'] = $stop;
                    $elementType = 2;

                    $postfixCurrentIndex++;
                    $indexOfStackTop--;
                    $stop = $stack[$indexOfStackTop];
                } while ($stop != -2);

                $indexOfStackTop--;
            } elseif ($ca[$i] == '$') {
                while ($indexOfStackTop >= 0) {
                    if ($indexOfActuallyMarkedOperatorInStack == $indexOfStackTop && $is_marked_counter == 0) {
                        $postfix[$postfixCurrentIndex]['is_marked'] = 1;
                        $is_marked_counter = 1;
                    } else {
                        $postfix[$postfixCurrentIndex]['is_marked'] = 0;
                    }

                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
                    $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];

                    $indexOfStackTop--;
                    $postfixCurrentIndex++;
                }
                break;
            }

            // new code start
            if ($state == 0) {
                switch ($elementType) {
                    case 0:
                        $state = 1;
                        $probableRangeStart = $cn[0];
                        break;
                    case 3:
                        $state = 4;
                        break;
                    default:
                        break;
                }
            } elseif ($state == 1) {
                switch ($elementType) {
                    case 4:
                        $state = 2;
                        $indexOfProbablyMarkedOperatorInStack = $indexOfCurrentOperatorInStack;
                        break;
                    default:
                        $state = 0;
                }
            } elseif ($state == 2) {
                switch ($elementType) {
                    case 1:
                        break;
                    case 3:
                        $state = 6;
                        $actualRangeStart = $probableRangeStart;
                        $indexOfActuallyMarkedOperatorInStack = $indexOfProbablyMarkedOperatorInStack;
                        break;
                    default:
                        $state = 0;
                }
            } elseif ($state == 4) {
                switch ($elementType) {
                    case 2:
                        $state = 4;
                        break;
                    case 4:
                        $state = 5;
                        $indexOfProbablyMarkedOperatorInStack = $indexOfCurrentOperatorInStack;
                        break;
                    default:
                        break;
                }
            } elseif ($state == 5) {
                switch ($elementType) {
                    case 0:
                        $state = 6;
                        $actualRangeStart = $cn[0];
                        $indexOfActuallyMarkedOperatorInStack = $indexOfProbablyMarkedOperatorInStack;
                        break;
                    default:
                        $state = 0;
                }
            } elseif ($state == 6) {

            }
        }

        $returnArray['postfix'] = $postfix;
        $returnArray['range_start'] = $actualRangeStart;
        return $returnArray;
    }

    public function check_comparison_operator($operator)
    {
        if ($operator >= 4 && $operator <= 8) {
            return true;
        } else {
            return false;
        }
    }

    public function postfix_evaluator($postfixArray, $rangeStart = null)
    {
        $operand1 = 0.0;
        $operand2 = 0.0;
        $rangeStart = floatval($rangeStart);
        $diff = 0.0;
        $indexOfTopStack = -1;
        $eStack = [];
        $function_found = 0;

        if (sizeof($postfixArray) > 1) {
            for ($i = 0; $i < sizeof($postfixArray); $i++) {
                if ($postfixArray[$i]['type'] == 'number') {
                    $indexOfTopStack++;
                    $eStack[$indexOfTopStack] = $postfixArray[$i]['number'];
                } else {
                    $operand2 = floatval($eStack[$indexOfTopStack]);
                    $indexOfTopStack--;
                    $operand1 = floatval($eStack[$indexOfTopStack]);

                    if ($postfixArray[$i]['is_marked'] == 1) {
                        if ($operand1 == $rangeStart) {
                            $diff = abs(round($operand2 - $rangeStart, 2));
//                            echo "op1: ".$operand1."op2: ".$operand2."diff in exec: ".$diff;
                        } else {
                            $diff = abs(round($operand1 - $rangeStart, 2));
//                            echo "op1: ".$operand1."op2: ".$operand2."  diff in exec: ".$diff;
                        }
                    }

                    $result = $this->execute($operand1, $operand2, $postfixArray[$i]['operator']);
                    $eStack[$indexOfTopStack] = $result;
                }
            }
        } else {
            $result = $postfixArray[0]['number'];
        }

        $returnArray['result'] = $result ? $result : 0;
        $returnArray['diff'] = $diff;
        $returnArray['function_found'] = $function_found;
        return $returnArray;
    }

    public function execute($operand1, $operand2, $operator)
    {
        $o2n = [
            '+' => 0,
            '-' => 1,
            '*' => 2,
            '/' => 3,
            '>' => 4,
            '>=' => 5,
            '<' => 6,
            '<=' => 7,
            '==' => 8,
            '&&' => 9,
            '||' => 10,
            '!=' => 11
        ];

        $char = array_flip($o2n)[$operator];
        switch ($char) {
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
                if ($operand1 > $operand2) {
                    $result = 1;
                } else {
                    $result = 0;
                }
                break;
            case ">=";
                if ($operand1 >= $operand2) {
                    $result = 1;
                } else {
                    $result = 0;
                }
                break;
            case "<";
                if ($operand1 < $operand2) {
                    $result = 1;
                } else {
                    $result = 0;
                }
                break;
            case "<=";
                if ($operand1 <= $operand2) {
                    $result = 1;
                } else {
                    $result = 0;
                }
                break;
            case "==";
                if ($operand1 == $operand2) {
                    $result = 1;
                } else {
                    $result = 0;
                }
                break;
            case "&&";
                $result = $operand1 && $operand2;
                break;
            case "||";
                $result = $operand1 || $operand2;
                break;
            case "!=";
                $result = $operand1 != $operand2;
                break;
            case "&";
                $result = $operand1 & $operand2;
                break;
        }

        return $result;
    }

    public function credit_closing_percentage($period_start, $period_end, $payment_start, $payment_end, $level, $unit = null, $contextArray = [])
    {
        //working with sales (invoice)
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }
        if (!is_int($payment_start)) {
            $payment_start = strtotime($payment_start);
        }
        if (!is_int($payment_end)) {
            $payment_end = strtotime($payment_end);
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoice_date >=' => $period_start,
            'invoice_date <=' => $period_end,
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_total' => 'SUM(net_total)']);
        $sales_total = $sales->first()['sales_total'] ? $sales->first()['sales_total'] : 0;

        //working with payment
        $payments = TableRegistry::get('payments')->find('all', ['conditions' => [
            'collection_date >=' => $payment_start,
            'collection_date <=' => $payment_end,
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $payments->where(['customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $payments->where(['parent_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $payments->select(['payment_total' => 'SUM(amount)']);
        $payment_total = $payments->first()['payment_total'] ? $payments->first()['payment_total'] : 0;
        $percentage = $sales_total > 0 ? round(($payment_total / $sales_total) * 100, 2) : 0;
        return $percentage ? round($percentage, 2) : 0;
    }

    public function sales_quantity($period_start, $period_end, $itemArray, $level, $unit = null, $contextArray = [])
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $item_unit_ids = [];
        if (sizeof($itemArray) > 0) {
            foreach ($itemArray as $item) {
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions' => ['name' => $item['item_name'], 'status' => 1]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => $item['unit_name'], 'status' => 1]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id' => $item_id, 'unit_id' => $unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoices.invoice_date >=' => $period_start,
            'invoices.invoice_date <=' => $period_end,
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if (sizeof($item_unit_ids) > 0) {
            foreach ($item_unit_ids as $key => $item_unit) {
                if ($key == 0) {
                    if ($item_unit['item_id'] > 0) {
                        $sales->where(['invoiced_products.item_id' => $item_unit['item_id']]);
                    }
                    if ($item_unit['unit_id'] > 0) {
                        $sales->where(['invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                } else {
                    if ($item_unit['item_id'] > 0 && !$item_unit['unit_id']) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id']]);
                    } elseif ($item_unit['item_id'] > 0 && $item_unit['unit_id'] > 0) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id'], 'invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                }
            }
        }

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['invoices.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['invoices.customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_quantity' => 'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity'] ? $sales->first()['sales_quantity'] : 0;
        return $sales_quantity;
    }

    public function sales_value($period_start, $period_end, $itemArray, $level, $unit = null, $contextArray = [])
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $item_unit_ids = [];
        if (sizeof($itemArray) > 0) {
            foreach ($itemArray as $item) {
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions' => ['name' => $item['item_name'], 'status' => 1]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => $item['unit_name'], 'status' => 1]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id' => $item_id, 'unit_id' => $unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoices.invoice_date >=' => $period_start,
            'invoices.invoice_date <=' => $period_end,
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if (sizeof($item_unit_ids) > 0) {
            foreach ($item_unit_ids as $key => $item_unit) {
                if ($key == 0) {
                    if ($item_unit['item_id'] > 0) {
                        $sales->where(['invoiced_products.item_id' => $item_unit['item_id']]);
                    }
                    if ($item_unit['unit_id'] > 0) {
                        $sales->where(['invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                } else {
                    if ($item_unit['item_id'] > 0 && !$item_unit['unit_id']) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id']]);
                    } elseif ($item_unit['item_id'] > 0 && $item_unit['unit_id'] > 0) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id'], 'invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                }
            }
        }

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['invoices.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['invoices.customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_value' => 'SUM(invoices.net_total)']);
        $sales_value = $sales->first()['sales_value'] ? $sales->first()['sales_value'] : 0;
        return $sales_value;
    }

    public function sales_target_achievement($period_start, $period_end, $itemArray, $level, $unit = null)
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $budget = TableRegistry::get('sales_budgets')->find('all', ['conditions' => [
            'sales_budgets.budget_period_start >=' => $period_start,
            'sales_budgets.budget_period_end <=' => $period_end,
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $budget->where(['sales_budgets.administrative_unit_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $budget->where(['sales_budgets.administrative_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $budget->select(['total_budget' => 'SUM(sales_budgets.budget_amount)']);
        $total_budget = $budget->first()['total_budget'] ? $budget->first()['total_budget'] : 0;

        $salesBudgetConfiguration = TableRegistry::get('sales_budget_configurations')->find('all')->where(['status' => 1])->first();
        $sales_measure = $salesBudgetConfiguration['sales_measure'];
        if ($sales_measure == 1) {
            $sales_quantity = self::sales_quantity($period_start, $period_end, $itemArray, $level, $unit = null);
            $achievement = $total_budget > 0 ? round(($sales_quantity / $total_budget) * 100, 2) : 0;
        } else {
            $sales_value = self::sales_value($period_start, $period_end, $itemArray, $level, $unit = null);
            $achievement = $total_budget > 0 ? round(($sales_value / $total_budget) * 100, 2) : 0;
        }
        return $achievement;
    }

    public function credit_note_value($period_start, $period_end, $level, $unit = null)
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $credit = TableRegistry::get('credit_notes')->find('all', ['conditions' => [
            'credit_notes.date >=' => $period_start,
            'credit_notes.date <=' => $period_end,
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $credit->where(['credit_notes.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $credit->where(['credit_notes.parent_global_id' => $adminUnitInfo['global_id']]);
            }
        }
        $credit->select(['total_after_demurrage' => 'SUM(credit_notes.total_after_demurrage)']);
        $total_after_demurrage = $credit->first()['total_after_demurrage'] ? $credit->first()['total_after_demurrage'] : 0;
        return round($total_after_demurrage, 2);
    }

    public function cash_sales_quantity($period_start, $period_end, $itemArray, $level, $unit = null)
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $item_unit_ids = [];
        if (sizeof($itemArray) > 0) {
            foreach ($itemArray as $item) {
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions' => ['name' => $item['item_name'], 'status' => 1]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => $item['unit_name'], 'status' => 1]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id' => $item_id, 'unit_id' => $unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoices.invoice_date >=' => $period_start,
            'invoices.invoice_date <=' => $period_end,
            'invoices.invoice_type' => 1
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if (sizeof($item_unit_ids) > 0) {
            foreach ($item_unit_ids as $key => $item_unit) {
                if ($key == 0) {
                    if ($item_unit['item_id'] > 0) {
                        $sales->where(['invoiced_products.item_id' => $item_unit['item_id']]);
                    }
                    if ($item_unit['unit_id'] > 0) {
                        $sales->where(['invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                } else {
                    if ($item_unit['item_id'] > 0 && !$item_unit['unit_id']) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id']]);
                    } elseif ($item_unit['item_id'] > 0 && $item_unit['unit_id'] > 0) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id'], 'invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                }
            }
        }

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['invoices.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['invoices.customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_quantity' => 'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity'] ? $sales->first()['sales_quantity'] : 0;
        return $sales_quantity;
    }

    public function cash_sales_value($period_start, $period_end, $itemArray, $level, $unit = null)
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $item_unit_ids = [];
        if (sizeof($itemArray) > 0) {
            foreach ($itemArray as $item) {
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions' => ['name' => $item['item_name'], 'status' => 1]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => $item['unit_name'], 'status' => 1]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id' => $item_id, 'unit_id' => $unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoices.invoice_date >=' => $period_start,
            'invoices.invoice_date <=' => $period_end,
            'invoices.invoice_type' => 2
        ]]);

        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if (sizeof($item_unit_ids) > 0) {
            foreach ($item_unit_ids as $key => $item_unit) {
                if ($key == 0) {
                    if ($item_unit['item_id'] > 0) {
                        $sales->where(['invoiced_products.item_id' => $item_unit['item_id']]);
                    }
                    if ($item_unit['unit_id'] > 0) {
                        $sales->where(['invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                } else {
                    if ($item_unit['item_id'] > 0 && !$item_unit['unit_id']) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id']]);
                    } elseif ($item_unit['item_id'] > 0 && $item_unit['unit_id'] > 0) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id'], 'invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                }
            }
        }

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['invoices.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['invoices.customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['sales_value' => 'SUM(invoices.net_total)']);
        $sales_value = $sales->first()['sales_value'] ? $sales->first()['sales_value'] : 0;
        return $sales_value;
    }

    public function credit_dues($period_start, $period_end, $level, $unit = null)
    {
        if (!is_int($period_start)) {
            $period_start = strtotime($period_start);
        }
        if (!is_int($period_end)) {
            $period_end = strtotime($period_end);
        }

        $sales = TableRegistry::get('invoices')->find('all', ['conditions' => [
            'invoices.invoice_date >=' => $period_start,
            'invoices.invoice_date <=' => $period_end
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $sales->where(['invoices.customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $sales->where(['invoices.customer_unit_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $sales->select(['total_due' => 'SUM(invoices.due)']);
        $due = $sales->first()['total_due'] ? $sales->first()['total_due'] : 0;
        return $due;
    }

    public function collection($period_start, $period_end, $level, $unit)
    {
        $payments = TableRegistry::get('payments')->find('all', ['conditions' => [
            'collection_date >=' => $period_start,
            'collection_date <=' => $period_end,
        ]]);

        if ($level == Configure::read('max_level_no') + 1) {
            if ($unit) {
                $payments->where(['customer_id' => $unit]);
            }
        } else {
            if ($unit) {
                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['unit_name' => $unit]])->first();
                $payments->where(['parent_global_id' => $adminUnitInfo['global_id']]);
            }
        }

        $payments->select(['payment_total' => 'SUM(amount)']);
        $payment_total = $payments->first()['payment_total'] ? $payments->first()['payment_total'] : 0;
        return $payment_total;
    }

//    public function sales_budget($period_start, $period_end, $level, $unit){
//        if(!is_int($period_start)){$period_start = strtotime($period_start);}
//        if(!is_int($period_end)){$period_end = strtotime($period_end);}
//
//        $budget = TableRegistry::get('sales_budgets')->find('all', ['conditions'=>[
//            'sales_budgets.budget_period_start >='=>$period_start,
//            'sales_budgets.budget_period_end <='=>$period_end,
//        ]]);
//
//        if($level==Configure::read('max_level_no')+1){
//            if($unit){
//                $budget->where(['sales_budgets.administrative_unit_id'=>$unit]);
//            }
//        }else{
//            if($unit){
//                $adminUnitInfo = TableRegistry::get('administrative_units')->find('all', ['conditions'=>['unit_name'=>$unit]])->first();
//                $budget->where(['sales_budgets.administrative_unit_global_id'=>$adminUnitInfo['global_id']]);
//            }
//        }
//
//        $budget->select(['total_budget'=>'SUM(sales_budgets.sales_amount)']);
//        $total_budget = $budget->first()['total_budget']?$budget->first()['total_budget']:0;
//        return $total_budget;
//    }

    public function location_age($unit)
    {
        $location_info = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['global_id' => $unit]])->first();
        return $location_info['created_date'];
    }

    public function max_due_invoice_age($invoiceArray)
    {
        if (isset($invoiceArray['max_due_invoice_age'])) {
            return $invoiceArray['max_due_invoice_age'];
        } elseif (isset($invoiceArray[0]['max_due_invoice_age'])) {
            return $invoiceArray[0]['max_due_invoice_age'];
        } else {
            return 0;
        }
    }

    public function invoice_quantity($itemArray)
    {
        $item_unit_ids = [];
        if (sizeof($itemArray) > 0) {
            foreach ($itemArray as $item) {
                $itemInfo = TableRegistry::get('items')->find('all', ['conditions' => ['name' => $item['item_name'], 'status' => 1]])->first();
                $unitInfo = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => $item['unit_name'], 'status' => 1]])->first();
                $item_id = $itemInfo['id'];
                $unit_id = $unitInfo['id'];
                $item_unit_ids[] = ['item_id' => $item_id, 'unit_id' => $unit_id];
            }
        }

        $sales = TableRegistry::get('invoices')->find('all');
        $sales->innerJoin('invoiced_products', 'invoices.id=invoiced_products.invoice_id');
        if (sizeof($item_unit_ids) > 0) {
            foreach ($item_unit_ids as $key => $item_unit) {
                if ($key == 0) {
                    if ($item_unit['item_id'] > 0) {
                        $sales->where(['invoiced_products.item_id' => $item_unit['item_id']]);
                    }
                    if ($item_unit['unit_id'] > 0) {
                        $sales->where(['invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                } else {
                    if ($item_unit['item_id'] > 0 && !$item_unit['unit_id']) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id']]);
                    } elseif ($item_unit['item_id'] > 0 && $item_unit['unit_id'] > 0) {
                        $sales->orWhere(['invoiced_products.item_id' => $item_unit['item_id'], 'invoiced_products.manufacture_unit_id' => $item_unit['unit_id']]);
                    }
                }
            }
        }
        $sales->select(['sales_quantity' => 'SUM(invoiced_products.product_quantity)']);
        $sales_quantity = $sales->first()['sales_quantity'] ? $sales->first()['sales_quantity'] : 0;
        return $sales_quantity;
    }

    public function no_of_immediate_child($unit)
    {
        $location_info = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['global_id' => $unit]])->first();
        return $location_info['no_of_direct_successors'];
    }

//    public function invoice_item_payment_age($itemName, $unitName, $invoice){
//        $item_info = TableRegistry::get('items')->find('all', ['conditions'=>['name'=>str_replace("'", '', $itemName)]])->first();
//        $unit_info = TableRegistry::get('units')->find('all', ['conditions'=>['unit_display_name'=>str_replace("'", '', $unitName)]])->first();
//        $item_id = $item_info['id'];
//        $unit_id = $unit_info['id'];
//
//        if(sizeof($invoice)>0){
//            foreach($invoice['invoiced_products'] as $invoiced_product){
//                if($invoiced_product['item_id']==$item_id && $invoiced_product['manufacture_unit_id']==$unit_id){
//                    if($invoice['invoice_type']==1){
//                        if($invoiced_product['due']==0){
//                            if($invoiced_product['updated_date']-$invoiced_product['delivery_date']==0){
//                                return 0;
//                            }else{
//                                return ($invoiced_product['updated_date']-$invoiced_product['delivery_date'])/3600*24;
//                            }
//                        }else{
//                            return 999999;
//                        }
//                    }elseif($invoice['invoice_type']==2){
//                        if($invoiced_product['due']==0){
//                            if($invoiced_product['updated_date']-$invoiced_product['delivery_date']==0){
//                                return 0;
//                            }else{
//                                return ($invoiced_product['updated_date']-$invoiced_product['delivery_date'])/3600*24;
//                            }
//                        }else{
//                            return 999999;
//                        }
//                    }
//                }else{
//                    return 0;
//                }
//            }
//        }
//    }

    public function invoice_item_payment_age($invoiceArray)
    {
        $invoice_date = $invoiceArray['invoice_date'];
        $last_payment_date = $invoiceArray['last_payment_date'];

        if ($last_payment_date && $last_payment_date > 0) {
            $diff = $last_payment_date - $invoice_date;
            return round($diff / (24 * 3600));
        } else {
            return 3000;
        }
    }

    public function payment_date($contextArray = [])
    {
        if ($contextArray['last_payment_date'] > 0) {
            return $contextArray['last_payment_date'];
        } else {
            return strtotime('01-01-2027');
        }
    }

    public function invoice_payment_age($invoiceArray)
    {
        $invoice_date = $invoiceArray['invoice_date'];
        $last_payment_date = $invoiceArray['last_payment_date'];

        if ($last_payment_date && $last_payment_date > 0) {
            $diff = $last_payment_date - $invoice_date;
            return round($diff / (24 * 3600));
        } else {
            return 3000;
        }
    }

    public function item_unit_quantity_in_cash_invoices_over_a_period($itemName, $invoiceMultiArray, $outer_loop_iteration_no, $item_unit_quantity_found, $diff, $unitName = null)
    {
        $item_info = TableRegistry::get('items')->find('all', ['conditions' => ['name' => str_replace("'", '', $itemName), 'status' => 1]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => str_replace("'", '', $unitName), 'status' => 1]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];
        $converted_quantity = $unit_info['converted_quantity'];
        $unit_type = $unit_info['unit_type'];
        $sum = 0;

        if (sizeof($invoiceMultiArray) > 0) {
            foreach ($invoiceMultiArray as $invoiceArray) {
                if ($invoiceArray['invoice_type'] == array_flip(Configure::read('invoice_type'))['Cash']) {
                    foreach ($invoiceArray['invoiced_products'] as $invoiced_product) {
                        if ($item_id > 0 && $unit_id == null) {
                            if ($invoiced_product['item_id'] == $item_id) {
                                $sum += $invoiced_product['product_quantity'];
                            }
                        } elseif ($item_id > 0 && $unit_id > 0) {
                            if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                                $sum += $invoiced_product['product_quantity'];
                            }
                        }
                    }
                }
            }
        }

        $sum = self::converted_quantity($sum, $unit_type, $converted_quantity);

        if ($sum != 0) {
            $returnArray = $sum;
        } else {
            $returnArray = 0;
        }

        return $returnArray;
    }

    public function item_unit_quantity_in_credit_invoices_over_a_period($itemName, $invoiceMultiArray, $outer_loop_iteration_no, $item_unit_quantity_found, $diff, $unitName = null)
    {
        $item_info = TableRegistry::get('items')->find('all', ['conditions' => ['name' => str_replace("'", '', $itemName), 'status' => 1]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => str_replace("'", '', $unitName), 'status' => 1]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];
        $converted_quantity = $unit_info['converted_quantity'];
        $unit_type = $unit_info['unit_type'];
        $sum = 0;

        if (sizeof($invoiceMultiArray) > 0) {
            foreach ($invoiceMultiArray as $invoiceArray) {
                if ($invoiceArray['invoice_type'] == array_flip(Configure::read('invoice_type'))['Credit']) {
                    foreach ($invoiceArray['invoiced_products'] as $invoiced_product) {
                        if ($item_id > 0 && $unit_id == null) {
                            if ($invoiced_product['item_id'] == $item_id) {
                                $sum += $invoiced_product['product_quantity'];
                            }
                        } elseif ($item_id > 0 && $unit_id > 0) {
                            if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                                $sum += $invoiced_product['product_quantity'];
                            }
                        }
                    }
                }
            }
        }

        $sum = self::converted_quantity($sum, $unit_type, $converted_quantity);

        if ($sum != 0) {
            $returnArray = $sum;
        } else {
            $returnArray = 0;
        }

        return $returnArray;
    }

    public function is_mango_customer($contextArray = [])
    {
        if (isset($contextArray['customer_id']) && $contextArray['customer_id'] > 0) {
            $customer_id = $contextArray['customer_id'];
        } else {
            $customer_id = $contextArray[0]['customer_id'];
        }
        $customer_info = TableRegistry::get('customers')->find('all', ['conditions' => ['id' => $customer_id]])->first();
        if ($customer_info['is_mango'] == 1) {
            return $customer_info['is_mango'];
        } else {
            return 0;
        }
    }

    public function item_unit_quantity($itemName, $contextArray, $outer_loop_iteration_no, $item_unit_quantity_found, $diff, $unitName = null)
    {
        $item_info = TableRegistry::get('items')->find('all', ['conditions' => ['name' => str_replace("'", '', $itemName), 'status' => 1]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => str_replace("'", '', $unitName), 'status' => 1]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];

        $converted_quantity = $unit_info['converted_quantity'];
        $unit_type = $unit_info['unit_type'];
        $sum = 0;

        if (sizeof($contextArray) > 0) {
            foreach ($contextArray['invoiced_products'] as $invoiced_product) {
                if ($item_id > 0 && $unit_id == null) {
                    if ($invoiced_product['item_id'] == $item_id) {
                        $sum += $invoiced_product['product_quantity'];
                    }
                } elseif ($item_id > 0 && $unit_id > 0) {
                    if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                        $sum += $invoiced_product['product_quantity'];
                    }
                }
            }
        }

        $sum = self::converted_quantity($sum, $unit_type, $converted_quantity);

        if ($sum != 0) {
            $returnArray = $sum;
        } else {
            $returnArray = 0;
        }

        return $returnArray;
    }

    public function item_unit_net_sales_value($itemName, $contextArray, $unitName = null)
    {
        $item_info = TableRegistry::get('items')->find('all', ['conditions' => ['name' => str_replace("'", '', $itemName), 'status' => 1]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => str_replace("'", '', $unitName), 'status' => 1]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];

        $sum = 0;

        if (sizeof($contextArray) > 0) {
            if (isset($contextArray[0])) {
                foreach ($contextArray as $singleArray) {
                    foreach ($singleArray['invoiced_products'] as $invoiced_product) {
                        if ($item_id > 0 && $unit_id == null) {
                            if ($invoiced_product['item_id'] == $item_id) {
                                $sum += $invoiced_product['net_total'] ? $invoiced_product['net_total'] : 0;
                            }
                        } elseif ($item_id > 0 && $unit_id > 0) {
                            if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                                $sum += $invoiced_product['net_total'] ? $invoiced_product['net_total'] : 0;
                            }
                        }
                    }
                }
            } else {
                foreach ($contextArray['invoiced_products'] as $invoiced_product) {
                    if ($item_id > 0 && $unit_id == null) {
                        if ($invoiced_product['item_id'] == $item_id) {
                            $sum += $invoiced_product['net_total'] ? $invoiced_product['net_total'] : 0;
                        }
                    } elseif ($item_id > 0 && $unit_id > 0) {
                        if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                            $sum += $invoiced_product['net_total'] ? $invoiced_product['net_total'] : 0;
                        }
                    }
                }
            }
        }

        return $sum ? $sum : 0;
    }

    public function item_bulk_quantity($itemName, $unitName = null, $contextArray = [])
    {
        $item_info = TableRegistry::get('items')->find('all', ['conditions' => ['name' => str_replace("'", '', $itemName), 'status' => 1]])->first();
        $unit_info = TableRegistry::get('units')->find('all', ['conditions' => ['unit_display_name' => str_replace("'", '', $unitName), 'status' => 1]])->first();
        $item_id = $item_info['id'];
        $unit_id = $unit_info['id'];
        $converted_quantity = $unit_info['converted_quantity'];
        $unit_type = $unit_info['unit_type'];

        $sum = 0;

        if (sizeof($contextArray) > 0) {
            foreach ($contextArray['invoiced_products'] as $invoiced_product) {
                if ($item_id > 0 && $unit_id == null) {
                    if ($invoiced_product['item_id'] == $item_id) {
                        $sum += $invoiced_product['product_quantity'];
                    }
                } elseif ($item_id > 0 && $unit_id > 0) {
                    if ($invoiced_product['item_id'] == $item_id && $invoiced_product['manufacture_unit_id'] == $unit_id) {
                        $sum += $invoiced_product['product_quantity'];
                    }
                }
            }
        }

        $sum = self::converted_quantity($sum, $unit_type, $converted_quantity);
        return $sum ? $sum : 0;
    }

    public function is_cash_invoice($contextArray = [])
    {
        if ($contextArray['invoice_type'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function converted_quantity($sum, $unit_type, $converted_quantity)
    {
        // convert to kg/ltr
        if ($unit_type == 1 && $converted_quantity == 0) {
            $sum = $sum / 1000;
        } elseif ($unit_type == 1 && $converted_quantity != 0) {
            $sum = ($sum * $converted_quantity) / 1000;
        } elseif ($unit_type == 2 && $converted_quantity == 0 || $unit_type == 2 && $converted_quantity == null) {
            $sum = $sum * 1;
        } elseif ($unit_type == 2 && $converted_quantity != 0) {
            $sum = $sum * $converted_quantity;
        } elseif ($unit_type == 3 && $converted_quantity == 0) {
            $sum = $sum / 1000;
        } else if ($unit_type == 3 && $converted_quantity != 0) {
            $sum = ($sum * $converted_quantity) / 1000;
        } else if ($unit_type == 4 && $converted_quantity == 0) {
            $sum = $sum * 1;
        } else if ($unit_type == 4 && $converted_quantity != 0) {
            $sum = $sum * $converted_quantity;
        }

        return $sum;
    }

    public function credit_sales($unit_global_id, $start_time, $end_time, $group_by_level, $itemUnitArray = [])
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $invoices = TableRegistry::get('invoices')->find('all');
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $invoices->where(['invoice_date >=' => $start_time]);
        $invoices->where(['invoice_date <=' => $end_time]);
        if (sizeof($itemUnitArray) > 0) {
            $invoices->where(['item_unit_id IN' => $itemUnitArray]);
        }
        $invoices->where(['invoice_type' => array_flip(Configure::read('invoice_type'))['Credit']]);

        $newInvoiceArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_id']])) {
                    $newInvoiceArray[$invoice['customer_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_id']] = $invoice['net_total'];
                }
            }

            return $newInvoiceArray;
        } else {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_unit_global_id']])) {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] = $invoice['net_total'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newInvoiceArray[$child]) ? $newInvoiceArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newInvoiceArray[$expectedUnits['global_id']]) ? $newInvoiceArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function cash_sales($unit_global_id, $start_time, $end_time, $group_by_level, $itemUnitArray = [])
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $invoices = TableRegistry::get('invoices')->find('all');
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $invoices->where(['invoice_date >=' => $start_time]);
        $invoices->where(['invoice_date <=' => $end_time]);
        if (sizeof($itemUnitArray) > 0) {
            $invoices->where(['item_unit_id IN' => $itemUnitArray]);
        }
        $invoices->where(['invoice_type' => 1]);

        $newInvoiceArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_id']])) {
                    $newInvoiceArray[$invoice['customer_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_id']] = $invoice['net_total'];
                }
            }

            return $newInvoiceArray;
        } else {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_unit_global_id']])) {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] = $invoice['net_total'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newInvoiceArray[$child]) ? $newInvoiceArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newInvoiceArray[$expectedUnits['global_id']]) ? $newInvoiceArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function total_sales($unit_global_id, $start_time, $end_time, $group_by_level, $itemUnitArray = [])
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $invoices = TableRegistry::get('invoiced_products')->find('all');
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $invoices->where(['invoice_date >=' => $start_time]);
        $invoices->where(['invoice_date <=' => $end_time]);
        if (sizeof($itemUnitArray) > 0) {
            $invoices->where(['item_unit_id IN' => $itemUnitArray]);
        }

        $newInvoiceArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_id']])) {
                    $newInvoiceArray[$invoice['customer_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_id']] = $invoice['net_total'];
                }
            }

            return $newInvoiceArray;
        } else {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_unit_global_id']])) {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] = $invoice['net_total'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newInvoiceArray[$child]) ? $newInvoiceArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newInvoiceArray[$expectedUnits['global_id']]) ? $newInvoiceArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function cash_collection($unit_global_id, $start_time, $end_time, $is_adjustment, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $payments = TableRegistry::get('invoice_payments')->find('all');
        $payments->where('parent_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $payments->where('parent_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $payments->where(['payment_collection_date >=' => $start_time]);
        $payments->where(['payment_collection_date <=' => $end_time]);
        $payments->where(['is_adjustment' => $is_adjustment]);
        $payments->innerJoin('invoices', 'invoices.id = invoice_payments.invoice_id');
        $payments->where(['invoices.invoice_type' => array_flip(Configure::read('invoice_type'))['Cash']]);

        $newPaymentArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['customer_id']])) {
                    $newPaymentArray[$payment['customer_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['customer_id']] = $payment['invoice_wise_payment_amount'];
                }
            }

            return $newPaymentArray;
        } else {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['parent_global_id']])) {
                    $newPaymentArray[$payment['parent_global_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['parent_global_id']] = $payment['invoice_wise_payment_amount'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newPaymentArray[$child]) ? $newPaymentArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newPaymentArray[$expectedUnits['global_id']]) ? $newPaymentArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function credit_collection($unit_global_id, $start_time, $end_time, $is_adjustment, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $payments = TableRegistry::get('invoice_payments')->find('all');
        $payments->where('parent_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $payments->where('parent_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $payments->where(['payment_collection_date >=' => $start_time]);
        $payments->where(['payment_collection_date <=' => $end_time]);
        $payments->where(['is_adjustment' => $is_adjustment]);
        $payments->innerJoin('invoices', 'invoices.id = invoice_payments.invoice_id');
        $payments->where(['invoices.invoice_type' => array_flip(Configure::read('invoice_type'))['Credit']]);

        $newPaymentArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['customer_id']])) {
                    $newPaymentArray[$payment['customer_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['customer_id']] = $payment['invoice_wise_payment_amount'];
                }
            }

            return $newPaymentArray;
        } else {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['parent_global_id']])) {
                    $newPaymentArray[$payment['parent_global_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['parent_global_id']] = $payment['invoice_wise_payment_amount'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newPaymentArray[$child]) ? $newPaymentArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newPaymentArray[$expectedUnits['global_id']]) ? $newPaymentArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function total_collection($unit_global_id, $start_time, $end_time, $is_adjustment, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $payments = TableRegistry::get('invoice_payments')->find('all');
        $payments->where('parent_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $payments->where('parent_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $payments->where(['payment_collection_date >=' => $start_time]);
        $payments->where(['payment_collection_date <=' => $end_time]);
        $payments->where(['is_adjustment' => $is_adjustment]);
        $payments->innerJoin('invoices', 'invoices.id = invoice_payments.invoice_id');

        $newPaymentArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['customer_id']])) {
                    $newPaymentArray[$payment['customer_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['customer_id']] = $payment['invoice_wise_payment_amount'];
                }
            }

            return $newPaymentArray;
        } else {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['parent_global_id']])) {
                    $newPaymentArray[$payment['parent_global_id']] += $payment['invoice_wise_payment_amount'];
                } else {
                    $newPaymentArray[$payment['parent_global_id']] = $payment['invoice_wise_payment_amount'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newPaymentArray[$child]) ? $newPaymentArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newPaymentArray[$expectedUnits['global_id']]) ? $newPaymentArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function total_credit_notes($unit_global_id, $start_time, $end_time, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $creditNotes = TableRegistry::get('credit_notes')->find('all');
        $creditNotes->where('parent_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $creditNotes->where('parent_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $creditNotes->where(['date >=' => $start_time]);
        $creditNotes->where(['date <=' => $end_time]);

        $newCnArray = [];
        if ($group_by_level == Configure::read('max_level_no')) {
            foreach ($creditNotes as $creditNote) {
                if (isset($newCnArray[$creditNote['customer_id']])) {
                    $newCnArray[$creditNote['customer_id']] += $creditNote['total_after_demurrage'];
                } else {
                    $newCnArray[$creditNote['customer_id']] = $creditNote['total_after_demurrage'];
                }
            }

            return $newCnArray;
        } else {
            foreach ($creditNotes as $creditNote) {
                if (isset($newCnArray[$creditNote['parent_global_id']])) {
                    $newCnArray[$creditNote['parent_global_id']] += $creditNote['total_after_demurrage'];
                } else {
                    $newCnArray[$creditNote['parent_global_id']] = $creditNote['total_after_demurrage'];
                }
            }
        }

        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

        if ($searchUnitInfo['level_no'] == $group_by_level) {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
        } else {
            $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
        }

        $administrativeUnits->where(['level_no' => $group_by_level]);
        $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

        if ($administrativeUnits->toArray()) {
            $xxx = [];
            foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                $xxx[$expectedUnits['global_id']] = 0;
                $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                if (sizeof($childs) > 0) {
                    foreach ($childs as $child) {
                        $xxx[$expectedUnits['global_id']] += isset($newCnArray[$child]) ? $newCnArray[$child] : 0;
                    }
                } else {
                    $xxx[$expectedUnits['global_id']] = isset($newCnArray[$expectedUnits['global_id']]) ? $newCnArray[$expectedUnits['global_id']] : 0;
                }
            }

            return $xxx;
        } else {
            return [];
        }
    }

    public function opening_due($unit_global_id, $date, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);

        $invoices = TableRegistry::get('invoices')->find('all');
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $invoices->where('customer_unit_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $invoices->where(['invoice_date <=' => $date]);

        $payments = TableRegistry::get('payments')->find('all');
        $payments->where('parent_global_id -' . $unit_global_id . '>= ' . $limitStart);
        $payments->where('parent_global_id -' . $unit_global_id . '< ' . $limitEnd);
        $payments->where(['collection_date <=' => $date]);

        $newInvoiceArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_id']])) {
                    $newInvoiceArray[$invoice['customer_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_id']] = $invoice['net_total'];
                }
            }
        } else {
            foreach ($invoices as $invoice) {
                if (isset($newInvoiceArray[$invoice['customer_unit_global_id']])) {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] += $invoice['net_total'];
                } else {
                    $newInvoiceArray[$invoice['customer_unit_global_id']] = $invoice['net_total'];
                }
            }
        }

        $newPaymentArray = [];
        if ($group_by_level == Configure::read('max_level_no') + 1) {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['customer_id']])) {
                    $newPaymentArray[$payment['customer_id']] += $payment['amount'];
                } else {
                    $newPaymentArray[$payment['customer_id']] = $payment['amount'];
                }
            }
        } else {
            foreach ($payments as $payment) {
                if (isset($newPaymentArray[$payment['parent_global_id']])) {
                    $newPaymentArray[$payment['parent_global_id']] += $payment['amount'];
                } else {
                    $newPaymentArray[$payment['parent_global_id']] = $payment['amount'];
                }
            }
        }

        $finalArray = [];
        foreach ($newInvoiceArray as $keyUnit => $newInvoice) {
            if (isset($newPaymentArray[$keyUnit])) {
                $finalArray[$keyUnit] = $newInvoiceArray[$keyUnit] - $newPaymentArray[$keyUnit];
            } else {
                $finalArray[$keyUnit] = $newInvoiceArray[$keyUnit];
            }
        }

        if ($group_by_level == Configure::read('max_level_no') + 1) {
            $customers = TableRegistry::get('customers')->find();
            $customers->select(['id', 'name']);

            $customerArray = [];
            foreach ($customers as $customer) {
                $customerArray[$customer['id']] = $customer['name'];
            }

            $returnArray = [];
            $i = 0;
            foreach ($finalArray as $id => $final) {
                $returnArray[$i]['name'] = $customerArray[$id];
                $returnArray[$i]['total'] = $final;
                $i++;
            }
            return $returnArray;
        } else {
            $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->first();

            $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
            $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
            $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
            $administrativeUnits->where('global_id -' . $unit_global_id . '>= ' . $limitStart);

            if ($searchUnitInfo['level_no'] == $group_by_level) {
                $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd)->orWhere(['global_id' => $unit_global_id]);
            } else {
                $administrativeUnits->where('global_id -' . $unit_global_id . '< ' . $limitEnd);
            }

            $administrativeUnits->where(['level_no' => $group_by_level]);
            $administrativeUnits->select(['global_id', 'level_no', 'unit_name']);

            if ($administrativeUnits->toArray()) {
                $xxx = [];
                foreach ($administrativeUnits->toArray() as $key => $expectedUnits) {
                    $xxx[$key]['name'] = $expectedUnits['unit_name'];
                    $xxx[$key]['total'] = 0;
                    $childs = self::get_child_global_ids($expectedUnits['level_no'], $expectedUnits['global_id']);
                    if (sizeof($childs) > 0) {
                        foreach ($childs as $child) {
                            $xxx[$key]['total'] += isset($finalArray[$child]) ? $finalArray[$child] : 0;
                        }
                    } else {
                        $xxx[$key]['total'] = isset($finalArray[$expectedUnits['global_id']]) ? $finalArray[$expectedUnits['global_id']] : 0;
                    }
                }

                return $xxx;
            } else {
                return [];
            }
        }
    }

    public function sales_budget($unit_global_id, $start_time, $end_time, $group_by_level)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $unit_global_id])->hydrate(false)->first();
        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $conn = ConnectionManager::get('default');

        $start_time_day = intval(date('d', $start_time));
        $start_time_month = intval(date('m', $start_time));
        $start_time_year = intval(date('Y', $start_time));
        $start_time_month_year_concat = $start_time_month . '-' . $start_time_year;
        $start_time_month_first_date = strtotime('01-' . $start_time_month . '-' . $start_time_year);

        $end_time_day = intval(date('d', $end_time));
        $end_time_month = intval(date('m', $end_time));
        $end_time_year = intval(date('Y', $end_time));
        $end_time_month_year_concat = $end_time_month . '-' . $end_time_year;
        $end_time_month_end_date = strtotime(Configure::read('month_end')[$end_time_month] . '-' . $end_time_month . '-' . $start_time_year);

        if ($start_time_month == 12) {
            $middle_month_start_date = strtotime('01' . '-' . '01' . '-' . ($start_time_year + 1));
        } else {
            $middle_month_start_date = strtotime('01' . '-' . ($start_time_month + 1) . '-' . $start_time_year);
        }

        if ($end_time_month == 1) {
            $middle_month_end_date = strtotime(Configure::read('month_end')[12] . '-' . '12' . '-' . ($start_time_year - 1));
        } else {
            $middle_month_end_date = strtotime(Configure::read('month_end')[$end_time_month - 1] . '-' . ($end_time_month - 1) . '-' . $end_time_year);
        }

        $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

        if ($start_time_month_year_concat == $end_time_month_year_concat) {
            $middleArray = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount*("' . intval(date('d', $end_time)) . '"+1-"' . intval(date('d', $start_time)) . '")/DAY(FROM_UNIXTIME(budget_period_end))) as total_amount from sales_budgets
            WHERE budget_period_start >= ' . $start_time_month_first_date . '
            AND budget_period_end <= ' . $end_time_month_end_date . '
            AND administrative_unit_global_id-' . $unit_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $unit_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            $middleArrayResult = $middleArray->fetchAll('assoc');
            $middleArrayFinal = [];
            foreach ($middleArrayResult as $middle) {
                $middleArrayFinal[$middle['global_id']] = $middle['total_amount'];
            }
            $myArray = [$middleArrayFinal];
        } else {
            $middleArray = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_amount from sales_budgets
            WHERE budget_period_start >= ' . $middle_month_start_date . '
            AND budget_period_end <= ' . $middle_month_end_date . '
            AND administrative_unit_global_id-' . $unit_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $unit_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            $middleArrayResult = $middleArray->fetchAll('assoc');
            $middleArrayFinal = [];
            foreach ($middleArrayResult as $middle) {
                $middleArrayFinal[$middle['global_id']] = $middle['total_amount'];
            }

            $fractionStartMonth = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount*((DAY(FROM_UNIXTIME(budget_period_end))+1-"' . intval(date('d', $start_time)) . '")/DAY(FROM_UNIXTIME(budget_period_end)))) as total_amount from sales_budgets
            WHERE budget_period_start <= ' . $start_time . '
            AND budget_period_end >= ' . $start_time . '
            AND administrative_unit_global_id-' . $unit_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $unit_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            $fractionStartMonthResult = $fractionStartMonth->fetchAll('assoc');
            $fractionStartMonthFinal = [];
            foreach ($fractionStartMonthResult as $fractionStartMonth) {
                $fractionStartMonthFinal[$fractionStartMonth['global_id']] = $fractionStartMonth['total_amount'];
            }

            $fractionEndMonth = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount*((DAY(FROM_UNIXTIME(budget_period_end)))/DAY(FROM_UNIXTIME(budget_period_end)))) as total_amount from sales_budgets
            WHERE budget_period_start >= ' . $end_time . '
            AND budget_period_end <= ' . $end_time . '
            AND administrative_unit_global_id-' . $unit_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $unit_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            $fractionEndMonthResult = $fractionEndMonth->fetchAll('assoc');
            $fractionEndMonthFinal = [];
            foreach ($fractionEndMonthResult as $fractionEndMonth) {
                $fractionEndMonthFinal[$fractionEndMonth['global_id']] = $fractionEndMonth['total_amount'];
            }

            $myArray = [$middleArrayFinal, $fractionStartMonthFinal, $fractionEndMonthFinal];
        }

        $sumArray = [];

        foreach ($myArray as $k => $subArray) {
            foreach ($subArray as $id => $value) {
                if (isset($sumArray[$id])) {
                    $sumArray[$id] += $value;
                } else {
                    $sumArray[$id] = $value;
                }
            }
        }

        return $sumArray;
    }

    public function collection_target($space_level, $space_global_id, $start_date, $end_date, $group_by_level)
    {
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if ($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')) {
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $queryOne = $conn->execute('
            SELECT customer_unit_global_id  & ' . $expression . ' as global_id, MIN(invoice_date) as oldest_invoice_date, SUM(net_total) as total_amount from invoices
            WHERE invoice_date+(90*24*3600) < ' . $start_date . ' AND !(last_payment_date < ' . $start_date . ' AND due = 0)
            AND customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            $queryTwo = $conn->execute('
            SELECT customer_unit_global_id  & ' . $expression . ' as global_id, SUM(net_total) as total_amount from invoices
            WHERE invoice_date+(90*24*3600) < ' . $start_date . ' AND !(last_payment_date < ' . $start_date . ' AND due = 0)
            AND customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

        } elseif ($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no') + 1) {
            $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY administrative_unit_id');

        } elseif ($space_level == Configure::read('max_level_no') && $group_by_level == $space_level) {
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget, sales_measure_unit from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY global_id');

        } elseif ($space_level == Configure::read('max_level_no') && $group_by_level > $space_level) {
            $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY administrative_unit_id');
        }

        $results = $query->fetchAll('assoc');
        $arr = [];
        foreach ($results as $result) {
            $arr[$result['global_id']] = $result['total_due'];
        }

        return $arr;
    }

    public function get_child_global_ids($own_level, $own_global_id, $search_level = null)
    {
        $limitStart = pow(2, (Configure::read('max_level_no') - $own_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $own_level) * 5);

        $administrativeUnits = TableRegistry::get('administrative_units')->query()->hydrate(false);
        $administrativeUnits->where('global_id -' . $own_global_id . '>= ' . $limitStart);
        $administrativeUnits->where('global_id -' . $own_global_id . '< ' . $limitEnd);
        if ($search_level) {
            $administrativeUnits->where(['level_no' => $search_level]);
        }
        $administrativeUnits->select(['global_id']);

        if ($administrativeUnits->toArray()) {
            $mainArray = $administrativeUnits->toArray();
            $simple = [];
            foreach ($mainArray as $arr) {
                $simple[] = $arr['global_id'];
            }
            return $simple;
        } else {
            return [];
        }
    }

    public function day_wise_dues($space_level, $space_global_id, $group_by_level)
    {
        $conn = ConnectionManager::get('default');
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if ($group_by_level < 5 && $space_level < 5) {
            if ($space_level != 4) {
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));
                //here it is :: 2^(1+5*level) -1  * 2^(5*(max_level-level))
                $stmt = $conn->execute('
                SELECT invoice_date,customer_unit_global_id  & ' . $expression . ' as GLOBAL_ID,due as DUE from invoices
                WHERE  customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . '
                AND  customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
                OR customer_unit_global_id=' . $space_global_id . '
                AND status =1
                AND due>0
                ');

                $result = $stmt->fetchAll('assoc');
            } else {
                $stmt = $conn->execute('
                SELECT invoice_date,customer_unit_global_id as GLOBAL_ID ,due as DUE from invoices
                WHERE   customer_unit_global_id = ' . $space_global_id . '
                AND status =1
                AND due>0
                ');
                $result = $stmt->fetchAll('assoc');
            }

        } else {
            $stmt = $conn->execute('
                SELECT invoice_date,customer_id as GLOBAL_ID,due as DUE from invoices
                WHERE  customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
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

            if ($date_difference_in_day > 90 && $date_difference_in_day <= 120) {
                unset($result[$key]['invoice_date']);
                $result[$key]['invoice_date'] = 4;
            } else if ($date_difference_in_day > 120 && $date_difference_in_day <= 180) {

                unset($result[$key]['invoice_date']);
                $result[$key]['invoice_date'] = 5;
            } else if ($date_difference_in_day > 180) {

                unset($result[$key]['invoice_date']);
                $result[$key]['invoice_date'] = 8;

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

        return $groupByDaySpan;
    }
}
