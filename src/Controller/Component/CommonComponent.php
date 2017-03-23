<?php
namespace App\Controller\Component;

use App\View\Helper\FunctionHelper;
use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\View\View;
use Hashids\Hashids;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;
use mPDF;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Common component
 */
class CommonComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    public $components = ['Auth'];
    protected $_defaultConfig = [];

    /**
     * Hashids Settings
     *
     * @var array
     */

    /**
     * Hashids function
     *
     * @return object
     */
    public function hashids()
    {
        $security = Configure::read('security');
        return $hashids = new Hashids(
            $security['salt'],
            $security['min_hash_length'],
            $security['alphabet']
        );
    }


    public function get_bulk_unit_sum_from_stock($warehouse_id, $item_id)
    {
        $stock_table = TableRegistry::get('stocks');
        $stock_info = $stock_table->find('all')->contain(['Items', 'Units', 'Warehouses'])
            ->where(['warehouse_id' => $warehouse_id, 'item_id' => $item_id,
                'Units.is_sales_unit' => 0,
                'stocks.status' => 1,
                'Units.status' => 1,
                'Items.status' => 1,
                'Warehouses.status' => 1])
            ->hydrate(false)
            ->toArray();
        $sum = 0;
        if (!empty($stock_info)) {
            foreach ($stock_info as $stock):
                if ($stock['unit']['unit_size'] == 0) {
                    if ($stock['unit']['unit_type'] == 1 || $stock['unit']['unit_type'] == 3) {
                        $value = $stock['quantity'] / 1000;
                        $sum += $value;
                    } else {
                        $sum += $stock['quantity'];
                    }
                } else {
                    if ($stock['unit']['unit_type'] == 1 || $stock['unit']['unit_type'] == 3) {
                        $value = $stock['unit']['converted_quantity'] * $stock['quantity'] / 1000;
                        $sum += $value;
                    } else {
                        $value = $stock['unit']['converted_quantity'] * $stock['quantity'];
                        $sum += $value;
                    }
                }
            endforeach;
            return $sum;
        } else {
            return 0;

        }
    }

    public function initiate_stock($warehouse_id, $item_id, $manufacture_unit_id, $quantity)
    {
        $time = time();
        $user = $this->Auth->user();
        $stocks_table = TableRegistry::get('stocks');

        $stocks = $stocks_table->find('all')
            ->where(['item_id' => $item_id, 'warehouse_id' => $warehouse_id, 'manufacture_unit_id' => $manufacture_unit_id, 'status' => 1])
            ->hydrate(false)
            ->first();
        if (empty($stocks)) {
            $stock_entity = $stocks_table->newEntity();
            $initiate_stock['item_id'] = $item_id;
            $initiate_stock['warehouse_id'] = $warehouse_id;
            $initiate_stock['manufacture_unit_id'] = $manufacture_unit_id;
            $initiate_stock['quantity'] = $quantity;
            $initiate_stock['type'] = 6;
            $initiate_stock['created_by'] = $user['id'];
            $initiate_stock['created_date'] = $time;
            $stock_entity = $stocks_table->patchEntity($stock_entity, $initiate_stock);
            if ($stocks_table->save($stock_entity)) {
                $result['stock_id'] = $stock_entity->id;
                return $result; //stock id
            } else {
                pr($stock_entity->errors());
                return 0;
                die;
            }
        } else {
            echo "Stock Not Empty";
            die;
        }

    }

    public function pay_invoice_due($customer_id, $amount, $payment_account_code)
    {
        $response_array = [];
        $invoice_table = TableRegistry::get('invoices');
        $invoices = TableRegistry::get('invoices')->
        find('all', [
            'fields' => ['id', 'customer_id', 'net_total', 'due', 'invoice_date', 'customer_type', 'customer_unit_global_id'],
            'contain' => ['Customers'],
            'conditions' => ['customer_id' => $customer_id, 'Invoices.status' => 1]])
            ->order(['invoice_date' => 'ASC'])->hydrate(false)->toArray();

        if (empty($invoices)) {
            echo "Invoice not found for this customer";
            die;
        }

        foreach ($invoices as $inv_due) {
            if ($inv_due['due'] != 0) {
                $due_available = 1;
            } else {
                $due_available = 0;
            }
            pr($due_available);
            die;
        }

        $time = time();
        $user = $this->Auth->user();
        $credit_note_amount = $amount;
        $connection = ConnectionManager::get('default');
        $payment_table = TableRegistry::get('payments');
        $payment_entity = $payment_table->newEntity();
        $payment_data['customer_id'] = $customer_id;
        $payment_data['customer_type'] = $invoices[0]['customer_type'];
        $payment_data['parent_global_id'] = $invoices[0]['customer_unit_global_id'];
        $payment_data['payment_account'] = $payment_account_code;
        $payment_data['amount'] = $amount;
        $payment_data['collection_date'] = $time;
        $payment_data['created_date'] = $time;
        $payment_data['created_by'] = $user['id'];
        $payment_data['is_adjustment'] = 1;
        $payment_entity = $payment_table->patchEntity($payment_entity, $payment_data);

        $connection->transactional(function ($connection)
        use ($time, $user, $credit_note_amount, $invoice_table, $customer_id, $amount, $invoices, $payment_data, $payment_entity, $payment_table) {
            foreach ($invoices as $invoice):
                if ($credit_note_amount != 0) {
                    if ($invoice['due'] <= $credit_note_amount) {
                        $paid_due = $credit_note_amount - $invoice['due'];
                        $credit_note_amount = $paid_due;
                        $query = $invoice_table->query();
                        $query->update()
                            ->set(['due' => 0, 'updated_date' => $time, 'updated_by' => $user['id']])
                            ->where(['customer_id' => $customer_id, 'id' => $invoice['id']])
                            ->execute();
                        //$connection->execute('UPDATE invoices SET due = ?, updated_date =? ,updated_by = ? WHERE customer_id = ? AND id=?', [0, $time, $user['id'], $customer_id, $invoice['id']]);

                    } else {
                        $paid_due = $invoice['due'] - $credit_note_amount;
                        $credit_note_amount = 0;
                        $query = $invoice_table->query();
                        $query->update()
                            ->set(['due' => $paid_due, 'updated_date' => $time, 'updated_by' => $user['id']])
                            ->where(['customer_id' => $customer_id, 'id' => $invoice['id']])
                            ->execute();
                        // $connection->execute('UPDATE invoices SET due = ?, updated_date =?, updated_by = ? WHERE customer_id = ? AND  id=?', [$paid_due, $time, $user['id'], $customer_id, $invoice['id']]);

                    }
                }
            endforeach;

            if ($payment_table->save($payment_entity, $payment_data)) {
                echo "Payment Was Successful";
            } else {
                pr($payment_entity->errors());
                echo "Payment Was Unsuccessful";
                die;
            }

            // return $payment_table->save($payment_entity, $payment_data);
        });
    }

    public function closest($array, $price)
    {
        foreach ($array as $k => $v) {
            $diff[abs($v - $price)] = $k;
        }

        $closest_key = $diff[min(array_keys($diff))];
        return array($closest_key, $array[$closest_key]);
        return $item;
    }

// item name resolver
    public function item_name_resolver($warehouse_id)
    {
        $items = TableRegistry::get('warehouse_items')->find()
            ->select(['use_alias' => 'warehouse_items.use_alias', 'id' => 'items.id', 'name' => 'items.name', 'alias' => 'items.alias'])
            ->where(['warehouse_items.warehouse_id' => $warehouse_id])
            ->leftJoin('items', 'items.id=warehouse_items.item_id')
            ->toArray();
        $item = [];
        foreach ($items as $row) {

            if ($row['use_alias']) {
                $item[$row['id']] = $row['alias'];

            } else {
                $item[$row['id']] = $row['name'];

            }
        }
        return $item;
    }

    public function getAccountWisePaymentAmount($customer_id, array $payment_account, $start_date, $end_date)
    {
        $paymentsTable = TableRegistry::get('payments');
        $query = $paymentsTable->find()
            ->where(['customer_id' => $customer_id, 'status' => 1])
            ->andWhere(['payment_account IN' => $payment_account])
            ->andWhere(['is_adjustment' => 1])
            ->andWhere(
                function ($exp) use ($start_date, $end_date) {
                    return $exp->between('collection_date', $start_date, $end_date);
                });
        $query = $query->select(['result' => $query->func()->sum('amount')])->hydrate(false)->toArray();

        if (!empty($query[0]['result'])) {
            $result = $query[0]['result'];
        } else {
            $result = 0;
        }
        return $result;

    }

    public function getChildCategories($category_global_id, $level_no)
    {
        $limitStart = pow(2, (Configure::read('category_max_level_no') - $level_no - 1) * 6);
        $limitEnd = pow(2, (Configure::read('category_max_level_no') - $level_no) * 6);

        $categories = TableRegistry::get('categories')->query();
        $categories->where('global_id -' . $category_global_id . '>= ' . $limitStart);
        $categories->where('global_id -' . $category_global_id . '< ' . $limitEnd);
        $categories->where('categories.number_of_direct_successors=0');
        $categories->where('categories.status!= 99')->hydrate(false);
        $result = [];
        foreach ($categories as $category):
            array_push($result, $category['id']);
        endforeach;
        return $result;
    }

    public function get_administrative_units($level)
    {
        $user = $this->Auth->user();
        $ad_u_id = $user['administrative_unit_id'];
        $data_global = TableRegistry::get('administrative_units')->get($ad_u_id);

        if ($user['level_no'] == $level) {
            $result = [];
            $data_global = TableRegistry::get('administrative_units')->find()
                ->select(['id', 'unit_name', 'global_id'])
                ->where(['id' => $ad_u_id])
                ->toArray();
            $result = $data_global;
            return $result;
        }

        $limitStart = pow(2, (Configure::read('max_level_no') - $user['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $user['level_no']) * 5);
        $associated = TableRegistry::get('administrative_units')->query();
        $associated->select(['id', 'unit_name', 'global_id']);
        $associated->where('global_id -' . $data_global['global_id'] . '>= ' . $limitStart);
        $associated->where('global_id -' . $data_global['global_id'] . '< ' . $limitEnd);
        $associated->where(['level_no' => $level]);
        $associated->hydrate(false);

        return $associated->toArray();
    }

    public function administrative_unit_wise_credit_limit($top_global_id, $provided_level_no)
        // $top_global_id's hierarchy must be greater than $provided_level_no
        //e.g ::  (area,territory)
    {
        $searchUnitInfo = TableRegistry::get('administrative_units')->find()->where(['global_id' => $top_global_id])->hydrate(false)->first();
        $limitStart = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no'] - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $searchUnitInfo['level_no']) * 5);
        $conn = ConnectionManager::get('default');
        if ($provided_level_no < 5) {
            $expression = (pow(2, (1 + 5 * $provided_level_no)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $provided_level_no)));
            //here it is :: 2^(1+5*level) -1  * 2^(5*(max_level-level))
            $stmt = $conn->execute('
            SELECT unit_global_id  & ' . $expression . ' as GLOBAL_ID,SUM(credit_limit) as CREDIT_LIMIT from customers
            WHERE  unit_global_id-' . $top_global_id . ' >= ' . $limitStart . '
             AND unit_global_id-' . $top_global_id . ' < ' . $limitEnd . '
            AND status =1
            GROUP BY GLOBAL_ID');
            $result = $stmt->fetchAll('assoc');
        } else {
            if ($searchUnitInfo['level_no'] == 4) {
                $stmt = $conn->execute('
            SELECT id as GLOBAL_ID , SUM(credit_limit) as CREDIT_LIMIT from customers
            WHERE   unit_global_id = ' . $top_global_id . '
            GROUP BY id');
                $result = $stmt->fetchAll('assoc');

            } else {
                $stmt = $conn->execute('
            SELECT id as GLOBAL_ID, SUM(credit_limit) as CREDIT_LIMIT from customers
            WHERE  unit_global_id-' . $top_global_id . ' >= ' . $limitStart . ' AND unit_global_id-' . $top_global_id . ' < ' . $limitEnd . '
            GROUP BY id');
                $result = $stmt->fetchAll('assoc');
            }
        }
        return $result;

    }

//    Output item name generation
    public function specific_item_name_resolver($warehouse_id, $item_id)
    {
        $items = TableRegistry::get('warehouse_items')->find()
            ->select(['use_alias' => 'warehouse_items.use_alias', 'id' => 'items.id', 'name' => 'items.name', 'alias' => 'items.alias'])
            ->where(['warehouse_items.warehouse_id' => $warehouse_id])
            ->where(['items.id' => $item_id])
            ->leftJoin('items', 'items.id=warehouse_items.item_id')
            ->toArray();
        $item = [];
        foreach ($items as $row) {

            if ($row['use_alias']) {
                $item['name'] = $row['alias'];
                $item['id'] = $row['id'];

            } else {
                $item['name'] = $row['name'];
                $item['id'] = $row['id'];

            }
        }
        return $item;
    }

    public function getPdf($html)
    {
        \Composer\Autoload\includeFile(ROOT . '\vendor' . DS . 'mpdf' . DS . 'mpdf' . DS . 'mpdf.php');
        $mpdf = new mPDF();
        $mpdf->useAdobeCJK = true;
        //$mpdf->autoLangToFont(AUTOFONT_ALL);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT . 'css/mpdf.css'), 1);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT . 'css/report.css'), 1);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT . 'assets/global/plugins/bootstrap/css/bootstrap.min.css'), 1);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

    public function pay_selected_invoices($customer_type, $customer_id, $parent_global_id,
                                          array $invoice_payments, $payment_account,
                                          $is_adjustment = false, $total_amount = null)
    {
        $conn = ConnectionManager::get('default');
        $user = $this->Auth->user();
        $time = time();

        if ($payment_account == 310000 || $payment_account == 312000 || $payment_account == 313000 || $payment_account == 130000) {
            $is_adjustment = true;
        }

        $invoice_table = TableRegistry::get('invoices');
        $invoice_payments_table = TableRegistry::get('invoice_payments');
        $InvProductsTable = TableRegistry::get('invoiced_products');
        $InvProductsPaymentTable = TableRegistry::get('invoiced_products_payments');

        if (!$total_amount) {
            echo "Amount Missing";
            die;
        } else if (!$customer_id) {
            echo "Customer Not found";
            die;
        } else if (!$customer_type) {
            echo "Customer Type Not Found";
            die;
        }
        if (empty($invoice_payments['invoices'])) {
            echo "No Invoice Found";
            die;
        } else {
            try {
                $conn->transactional(function ($conn) use (
                    $time, $user, $invoice_table, $InvProductsPaymentTable, $InvProductsTable, $invoice_payments_table,
                    $customer_type, $customer_id, $parent_global_id, $invoice_payments, $payment_account, $is_adjustment, $total_amount
                ) {
                    $invoice_n_amount_array = [];
                    foreach ($invoice_payments['invoices'] as $invoice_arr):
                        $invoice_n_amount_array[$invoice_arr['id']] = $invoice_arr['amount'];
                    endforeach;
                    $invoice_id_array = array_keys($invoice_n_amount_array);
                    //payment table insertion
                    $payment_table = TableRegistry::get('payments');
                    $payment_entity = $payment_table->newEntity();
                    $payment_data['customer_id'] = $customer_id;
                    $payment_data['customer_type'] = $customer_type;
                    $payment_data['parent_global_id'] = $parent_global_id;
                    $payment_data['payment_account'] = $payment_account;
                    $payment_data['amount'] = $total_amount;
                    $payment_data['collection_date'] = $time;
                    $payment_data['created_date'] = $time;
                    $payment_data['created_by'] = $user['id'];
                    $payment_data['is_adjustment'] = $is_adjustment;
                    $payment_entity = $payment_table->patchEntity($payment_entity, $payment_data);

                    if ($payment_table->save($payment_entity)):
                        //update invoice table
                        //echo "payment ok";       //payment done
                        foreach ($invoice_payments['invoices'] as $invoice_payments):

                            $get_invoice = $invoice_table->find()
                                ->where(['id' => $invoice_payments['id']])
                                ->andWhere(['status' => 1])
                                ->order(['id' => 'ASC'])
                                ->first();

                            if ($get_invoice['due'] > 0) {
                                $invoice_data_for_update['due'] = $get_invoice['due'] - $invoice_payments['amount'];
                                $invoice_data_for_update['updated_date'] = $time;
                                $invoice_data_for_update['last_payment_date'] = $time;
                                $invoice_data_for_update['updated_by'] = $user['id'];
                                $invoice_data_for_update['payment_nature'] = $this->decide_payment_nature($is_adjustment, $get_invoice['payment_nature']);
                                $invoice_table->patchEntity($get_invoice, $invoice_data_for_update);
                            } else {
                                //return false;
                            }
                            if ($invoice_table->save($get_invoice)) {
                                ///insert into invoice_payments
                                //echo "__InvoiceUpdated" . $get_invoice['due'] . 'DUE NOW';

                                $invoice_payments_entity = $invoice_payments_table->newEntity();
                                $invoice_payments_entity->customer_id = $customer_id;
                                $invoice_payments_entity->customer_type = $customer_type;
                                $invoice_payments_entity->parent_global_id = $parent_global_id;
                                $invoice_payments_entity->invoice_id = $invoice_payments['id'];
                                $invoice_payments_entity->invoice_date = $get_invoice['invoice_date'];
                                $invoice_payments_entity->invoice_delivery_date = $get_invoice['delivery_date'];
                                $invoice_payments_entity->payment_id = $payment_entity->id;
                                $invoice_payments_entity->payment_collection_date = $time;
                                $invoice_payments_entity->invoice_wise_payment_amount = $invoice_payments['amount'];///
                                $invoice_payments_entity->created_by = $user['id'];
                                $invoice_payments_entity->is_adjustment = $is_adjustment;
                                $invoice_payments_entity->created_date = $time;

                                if ($invoice_payments_table->save($invoice_payments_entity)) {
                                    // echo "__invoicePaymentOK__";

                                }
                            }
                        endforeach;

                        $Inv_Inv_Products = TableRegistry::get('invoices')
                            ->find()
                            ->contain(['InvoicedProducts'])
                            ->hydrate(false)
                            ->where(['Invoices.id IN' => $invoice_id_array])
                            ->andWhere(['Invoices.status' => 1])
                            ->toArray();

                        foreach ($Inv_Inv_Products as $invoices):
                            $amount_to_pay = $invoice_n_amount_array[$invoices['id']];
                            if (!empty($invoices['invoiced_products'])) {
                                foreach ($invoices['invoiced_products'] as $invoiced_products):
                                    if ($amount_to_pay != 0) {
                                        if ($invoiced_products['due'] <= $amount_to_pay && $invoiced_products['due'] != 0) {
                                            $paid_due = $amount_to_pay - $invoiced_products['due'];
                                            $amount_to_pay = $paid_due;

                                            $get_invoice_prod = $InvProductsTable->get($invoiced_products['id']);
                                            $invoice_prod_data_for_update['due'] = 0;
                                            $invoice_prod_data_for_update['updated_date'] = $time;
                                            $invoice_prod_data_for_update['last_payment_date'] = $time;
                                            $invoice_prod_data_for_update['updated_by'] = $user['id'];
                                            $invoice_prod_data_for_update['payment_nature'] =
                                                $this->decide_payment_nature($is_adjustment, $get_invoice_prod['payment_nature']);
                                            $InvProductsTable->patchEntity($get_invoice_prod, $invoice_prod_data_for_update);

                                            if ($InvProductsTable->save($get_invoice_prod)) {
                                                $invoiced_product_payment = $InvProductsPaymentTable->newEntity();
                                                $invoiced_product_payment->customer_id = $customer_id;
                                                $invoiced_product_payment->customer_type = $customer_type;
                                                $invoiced_product_payment->parent_global_id = $parent_global_id;
                                                $invoiced_product_payment->invoice_id = $invoiced_products['invoice_id'];
                                                $invoiced_product_payment->item_id = $invoiced_products['item_id'];
                                                $invoiced_product_payment->manufacture_unit_id = $invoiced_products['manufacture_unit_id'];
                                                $invoiced_product_payment->invoice_date = $invoiced_products['invoice_date'];
                                                $invoiced_product_payment->invoice_delivery_date = $invoiced_products['delivery_date'];
                                                $invoiced_product_payment->invoice_payment_id = $payment_entity->id;//global payment
                                                $invoiced_product_payment->payment_collection_date = $time;
                                                $invoiced_product_payment->item_wise_payment_amount = $invoiced_products['due'];
                                                $invoiced_product_payment->created_by = $user['id'];
                                                $invoiced_product_payment->is_adjustment = $is_adjustment;
                                                $invoiced_product_payment->created_date = $time;
                                                if ($InvProductsPaymentTable->save($invoiced_product_payment)) {
                                                    // echo "InvoiceProductPaymentDone_when_due_cleared_";
                                                }
                                            }

                                        } else if ($invoiced_products['due'] != 0 && $invoiced_products['due'] > $amount_to_pay) {

                                            $paid_due = $invoiced_products['due'] - $amount_to_pay;
                                            $get_invoice_prod = $InvProductsTable->get($invoiced_products['id']);
                                            $invoice_prod_data_for_update['due'] = $paid_due;
                                            $invoice_prod_data_for_update['updated_date'] = $time;
                                            $invoice_prod_data_for_update['last_payment_date'] = $time;
                                            $invoice_prod_data_for_update['updated_by'] = $user['id'];
                                            $InvProductsTable->patchEntity($get_invoice_prod, $invoice_prod_data_for_update);
                                            if ($InvProductsTable->save($get_invoice_prod)) {
                                                $invoiced_product_payment = $InvProductsPaymentTable->newEntity();
                                                $invoiced_product_payment->customer_id = $customer_id;
                                                $invoiced_product_payment->customer_type = $customer_type;
                                                $invoiced_product_payment->parent_global_id = $parent_global_id;
                                                $invoiced_product_payment->invoice_id = $invoiced_products['invoice_id'];
                                                $invoiced_product_payment->item_id = $invoiced_products['item_id'];
                                                $invoiced_product_payment->manufacture_unit_id = $invoiced_products['manufacture_unit_id'];
                                                $invoiced_product_payment->invoice_date = $invoiced_products['invoice_date'];
                                                $invoiced_product_payment->invoice_delivery_date = $invoiced_products['delivery_date'];
                                                $invoiced_product_payment->invoice_payment_id = $payment_entity->id;//global payment
                                                $invoiced_product_payment->payment_collection_date = $time;
                                                $invoiced_product_payment->item_wise_payment_amount = $amount_to_pay;
                                                $invoiced_product_payment->created_by = $user['id'];
                                                $invoiced_product_payment->is_adjustment = $is_adjustment;
                                                $invoiced_product_payment->created_date = $time;
                                                if ($InvProductsPaymentTable->save($invoiced_product_payment)) {
                                                    //  echo "InvoiceProductPaymentDone_when_due_lessen";
                                                }
                                            }
                                            $amount_to_pay = 0;
                                        }
                                    } else {
                                        //echo "amount nai";
                                    }
                                endforeach;
                            }
                        endforeach;

                    endif;

                });
            } catch (Exception $e) {

                pr($e->getMessage());
                die;
            }
        }
    }

    public function decide_payment_nature($is_adjustment, $payment_nature)
    {
        if ($is_adjustment == 1 && $payment_nature == 0) {
            $final_payment_nature = 2;
        } else if ($is_adjustment == 0 && $payment_nature == 0) {
            $final_payment_nature = 1;

        } else if ($is_adjustment == 1 && $payment_nature == 1) {
            $final_payment_nature = 3;
        } else if ($is_adjustment == 0 && $payment_nature == 1) {
            $final_payment_nature = 1;

        } else if ($is_adjustment == 1 && $payment_nature == 2) {

            $final_payment_nature = 2;

        } else if ($is_adjustment == 0 && $payment_nature == 2) {
            $final_payment_nature = 3;

        } else if ($is_adjustment == 0 || $is_adjustment == 1 && $payment_nature == 3) {
            $final_payment_nature = 3;
        }

        return $final_payment_nature;
    }

    public function select_n_pay_invoices($customer_type, $customer_id, $parent_global_id, $payment_account, $is_adjustment, $total_amount)
    {
        $invoice_table = TableRegistry::get('invoices');
        $DueInvoices = $invoice_table
            ->find()
            ->select(['id', 'due', 'net_total', 'status'])
            ->where(['due >' => 0, 'status' => 1])
            ->order(['invoice_date' => 'ASC'])
            ->hydrate(false)
            ->toArray();

        $invoice_payments = [];
        $amount = $total_amount;
        $i = 0;
        foreach ($DueInvoices as $invoice):
            if ($amount != 0) {
                if ($invoice['due'] <= $amount) {
                    $paid_due = $amount - $invoice['due'];
                    $amount = $paid_due;
                    $invoice_payments['invoices'][$i]['id'] = $invoice['id'];
                    $invoice_payments['invoices'][$i]['amount'] = $invoice['due'];
                } else {
                    $paid_due = $invoice['due'] - $amount;
                    $invoice_payments['invoices'][$i]['id'] = $invoice['id'];
                    $invoice_payments['invoices'][$i]['amount'] = $amount;
                    $amount = 0;
                }
            }
            $i++;
        endforeach;

        $this->pay_selected_invoices($customer_type, $customer_id, $parent_global_id, $invoice_payments, $payment_account, $is_adjustment, $total_amount);
    }

    public function getWonOffer($applicablePostfix, $invoiceArray, $offer_id)
    {
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());

        $wonOffers = [];
        $offer_found = 0;
        $offer_found_count = 0;
        // general condition check
        $general = $applicablePostfix['general'];

        foreach ($general as $k => $genPost) {
            if ($genPost['type'] == 'function') {
                if ($genPost['name'] == 'item_unit_quantity') {
                    $argArray = explode(',', $genPost['arg']);
                    $result = $FunctionHelper->$genPost['name']($argArray[0], $invoiceArray, 0, 0, 0, $argArray[1]);
                } elseif ($genPost['name'] == 'max_due_invoice_age' || $genPost['name'] == 'is_mango_customer' || $genPost['name'] == 'is_cash_invoice' || $genPost['name'] == 'payment_date' || $genPost['name'] == 'invoice_payment_age') {
                    $result = $FunctionHelper->$genPost['name']($invoiceArray);
                } elseif($genPost['name'] == 'item_bulk_quantity'){
                    $argArray = explode(',', $genPost['arg']);
                    $result = $FunctionHelper->$genPost['name']($argArray[0], $invoiceArray, 0, 0, 0, $argArray[1]);
                }elseif($genPost['name'] == 'item_unit_net_sales_value'){
                    $argArray = explode(',', $genPost['arg']);
                    $result = $FunctionHelper->$genPost['name']($argArray[0], $invoiceArray, $argArray[1]);
                }

                if (isset($result)) {
                    $general[$k]['type'] = 'number';
                    $general[$k]['number'] = $result;
                    unset($general[$k]['name']);
                    unset($general[$k]['arg']);
                }
            }
        }

        $generalEvaluation = $FunctionHelper->postfix_evaluator($general)['result'];

        // If general condition is true then specific condition will be evaluated
        if ($generalEvaluation) {
            $specific = $applicablePostfix['specific'];
            $difference = [];
            $itemIdArray = [];

            for($i=0; 1 ; $i++){
                $offer_found = 0;
                foreach ($specific as $key => $specPost) {
                    $itemUnitIdArray = [];
                    $item_unit_quantity_found = 0;
                    $first_time_occurence_of_item_unit_qty_greater_than_zero= 0;

                    // item_unit_quantity_found = 0, 1, 2, 3
                    // 0 means item unit qty yet not found
                    // 1 means first time found with +
                    // 2 means other than first time found
                    // 3 means first time found and no + afterwards

                    foreach ($specPost['condition'] as $k => $specCon) {
                        if ($specCon['type'] == 'function') {
                            if ($specCon['name'] == 'item_unit_quantity') {

                                if(!isset($specCon['plus_found_after_item_unit_quantity'])){
                                    $first_time_occurence_of_item_unit_qty_greater_than_zero = 0;
                                }

                                $argArray = explode(',', $specCon['arg']);
                                $result = $FunctionHelper->$specCon['name']($argArray[0], $invoiceArray, $i, $item_unit_quantity_found,$first_time_occurence_of_item_unit_qty_greater_than_zero, $argArray[1]);
                                $item_unit_info = TableRegistry::get('item_units')->find('all', ['conditions'=>['item_name'=>str_replace("'", '', $argArray[0]), 'unit_display_name'=>str_replace("'", '', $argArray[1]), 'status'=>1]])->first();
                                $item_unit_id = $item_unit_info['id'];
                                $itemUnitIdArray[] = $item_unit_id;

                                if($i>0){
                                    if(($first_time_occurence_of_item_unit_qty_greater_than_zero==0)&&($result>0)){
                                        $result= $difference[$item_unit_id];
                                        $first_time_occurence_of_item_unit_qty_greater_than_zero=1;
                                    }
                                    else {
                                        $result = 0;
                                    }
                                }

                            } elseif ($specCon['name'] == 'max_due_invoice_age' || $specCon['name'] == 'is_mango_customer' || $specCon['name'] == 'is_cash_invoice' || $specCon['name'] == 'payment_date' || $specCon['name'] == 'invoice_payment_age' || $specCon['name'] == 'invoice_item_payment_age') {
                                $result = $FunctionHelper->$specCon['name']($invoiceArray);
                            }elseif($specCon['name'] == 'item_bulk_quantity'){
                                $argArray = explode(',', $specCon['arg']);
                                $result = $FunctionHelper->$specCon['name']($argArray[0], $argArray[1], $invoiceArray);
                            }elseif($specCon['name'] == 'item_unit_net_sales_value'){
                                $argArray = explode(',', $specCon['arg']);
                                $result = $FunctionHelper->$specCon['name']($argArray[0], $invoiceArray, $argArray[1]);
                            }

                            if (isset($result)) {
                                $specPost['condition'][$k]['type'] = 'number';
                                $specPost['condition'][$k]['number'] = $result;
                                unset($specPost['condition'][$k]['name']);
                                unset($specPost['condition'][$k]['arg']);
                            }
                        }
                    }

                    $eval = $FunctionHelper->postfix_evaluator($specPost['condition'], $specPost['range_start']);
                    $specConEvaluation = $eval['result'];

                    // If specific condition is true then amount will be evaluated
                    if ($specConEvaluation) {
                        if($specPost['range_start']==0){
                            $exit_outer_loop = 1;
                        }

                        $itemUnitIdArray = array_unique($itemUnitIdArray);
                        foreach($itemUnitIdArray as $itemUnit){
                            $difference[$itemUnit] = $eval['diff'];
                        }

                        foreach ($specPost['amount'] as $k => $specAmount) {
                            if ($specAmount['type'] == 'function') {
                                $exit_outer_loop = 1;

                                if ($specAmount['name'] == 'item_unit_quantity') {
                                    $argArray = explode(',', $specAmount['arg']);
                                    $result = $FunctionHelper->$specAmount['name']($argArray[0], $invoiceArray, 0, 0, 0, $argArray[1]);
                                } elseif ($specAmount['name'] == 'max_due_invoice_age' || $specAmount['name'] == 'is_mango_customer' || $specAmount['name'] == 'is_cash_invoice' || $specAmount['name'] == 'payment_date' || $specAmount['name'] == 'invoice_payment_age' || $specAmount['name'] == 'invoice_item_payment_age') {
                                    $result = $FunctionHelper->$specAmount['name']($invoiceArray);
                                } elseif ($specAmount['name'] == 'item_bulk_quantity'){
                                    $argArray = explode(',', $specAmount['arg']);
                                    $result = $FunctionHelper->$specAmount['name']($argArray[0], $argArray[1], $invoiceArray);
                                }elseif($specAmount['name'] == 'item_unit_net_sales_value'){
                                    $argArray = explode(',', $specAmount['arg']);
                                    $result = $FunctionHelper->$specAmount['name']($argArray[0], $invoiceArray, $argArray[1]);
                                }

                                if (isset($result)) {
                                    $specPost['amount'][$k]['type'] = 'number';
                                    $specPost['amount'][$k]['number'] = $result;
                                    unset($specPost['amount'][$k]['name']);
                                    unset($specPost['amount'][$k]['arg']);
                                }
                            }
                        }

                        $specAmountEvaluation = $FunctionHelper->postfix_evaluator($specPost['amount'])['result'];
                        $specAmountEvaluationFunctionFound = $FunctionHelper->postfix_evaluator($specPost['amount'])['function_found'];

                        $wonOffers[$i][$key]['value'] = $specAmountEvaluation;
                        $wonOffers[$i][$key]['offer_id'] = $offer_id;
                        $wonOffers[$i][$key]['offer_type'] = $specific[$key]['offer_type'];
                        $wonOffers[$i][$key]['offer_name'] = $specific[$key]['offer_name'];
                        $wonOffers[$i][$key]['offer_unit_name'] = $specific[$key]['offer_unit_name'];
                        $wonOffers[$i][$key]['amount_type'] = $specific[$key]['amount_type'];
                        $wonOffers[$i][$key]['payment_mode'] = $specific[$key]['payment_mode'];
                        $wonOffers[$i][$key]['amount_unit'] = $specific[$key]['amount_unit'];

                        $offer_found = 1;
                        $offer_found_count++;
                    }

                    if((sizeof($specific)-1 == $key) && ($offer_found == 0)){
                        $exit_outer_loop = 1;
                        break;
                    }
                }

                if(isset($exit_outer_loop) && $exit_outer_loop==1){
                    break;
                }
            }
        }

        $newArr = [];

        foreach($wonOffers as $iteration=>$specificWon){
            foreach($specificWon as $key=>$won){
                $newArr[] = $won;
            }
        }

        $testArray = [];
        foreach($newArr as $sl=>$new){
            $testArray[$sl] =  $new['offer_type'];
        }

        $testArray = array_unique($testArray);

        $sumArray = [];
        foreach($testArray as $test){
            $sumArray[$test] = 0;
            foreach($newArr as $k=>$new){
                if($test==$new['offer_type']){
                    $sumArray[$test] += $new['value'];
                }
            }
        }

        $final = [];
        $is = 0;
        foreach($sumArray as $offer_type=>$sum){
            foreach($newArr as $new){
                if($new['offer_type']==$offer_type){
                    $final[$is]['value'] = $sum;
                    $final[$is]['offer_id'] = $new['offer_id'];
                    $final[$is]['offer_type'] = $new['offer_type'];
                    $final[$is]['offer_name'] = $new['offer_name'];
                    $final[$is]['offer_unit_name'] = $new['offer_unit_name'];
                    $final[$is]['amount_type'] = $new['amount_type'];
                    $final[$is]['payment_mode'] = $new['payment_mode'];
                    $final[$is]['amount_unit'] = $new['amount_unit'];
                }
            }
            $is++;
        }
        return $final;
    }

    public function getWonCumulativeOffer($applicablePostfixArray, $invoiceArray, $offer_id)
    {
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());
        $wonOffers = [];
        $passedLevelOneInvoices = [];
        $passedLevelTwoInvoices = [];
        $offer_found = 0;
        $offer_found_count = 0;

        foreach ($applicablePostfixArray as $key => $applicablePostfix) {
            if ($key == 0) {
                // general condition check
                foreach ($invoiceArray as $invoice) {
                    $general = $applicablePostfixArray[$key]['general'];

                    foreach ($general as $k => $genPost) {
                        if ($genPost['type'] == 'function') {
                            if ($genPost['name'] == 'item_unit_quantity' || $genPost['name'] == 'item_unit_quantity_in_credit_invoices_over_a_period' || $genPost['name'] == 'item_unit_quantity_in_cash_invoices_over_a_period') {
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $invoice, 0, 0, 0, $argArray[1]);
                            } elseif ($genPost['name'] == 'max_due_invoice_age' || $genPost['name'] == 'is_mango_customer' || $genPost['name'] == 'is_cash_invoice' || $genPost['name'] == 'payment_date' || $genPost['name'] == 'invoice_payment_age') {
                                $result = $FunctionHelper->$genPost['name']($invoice);
                            } elseif($genPost['name'] == 'item_bulk_quantity'){
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $argArray[1], $invoice);
                            }elseif($genPost['name'] == 'item_unit_net_sales_value'){
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $invoice, $argArray[1]);
                            }

                            if (isset($result)) {
                                $general[$k]['type'] = 'number';
                                $general[$k]['number'] = $result;
                                unset($general[$k]['name']);
                                unset($general[$k]['arg']);
                            }
                        }
                    }

                    $generalEvaluation = $FunctionHelper->postfix_evaluator($general)['result'];
                    if ($generalEvaluation) {
                        $passedLevelOneInvoices[] = $invoice;
                    }
                }
            } elseif ($key == 1) {
                // general condition check
                foreach ($invoiceArray as $invoice) {
                    $general = $applicablePostfixArray[$key]['general'];

                    foreach ($general as $k => $genPost) {
                        if ($genPost['type'] == 'function') {
                            if ($genPost['name'] == 'item_unit_quantity' || $genPost['name'] == 'item_unit_quantity_in_credit_invoices_over_a_period' || $genPost['name'] == 'item_unit_quantity_in_cash_invoices_over_a_period') {
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $invoice, 0, 0, 0, $argArray[1]);
                            } elseif ($genPost['name'] == 'max_due_invoice_age' || $genPost['name'] == 'is_mango_customer' || $genPost['name'] == 'is_cash_invoice' || $genPost['name'] == 'payment_date' || $genPost['name'] == 'invoice_payment_age') {
                                $result = $FunctionHelper->$genPost['name']($invoice);
                            } elseif ($genPost['name'] == 'item_bulk_quantity'){
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $argArray[1], $invoice);
                            }elseif($genPost['name'] == 'item_unit_net_sales_value'){
                                $argArray = explode(',', $genPost['arg']);
                                $result = $FunctionHelper->$genPost['name']($argArray[0], $invoice, $argArray[1]);
                            }

                            if (isset($result)) {
                                $general[$k]['type'] = 'number';
                                $general[$k]['number'] = $result;
                                unset($general[$k]['name']);
                                unset($general[$k]['arg']);
                            }
                        }
                    }

                    $generalEvaluation = $FunctionHelper->postfix_evaluator($general)['result'];
                    if ($generalEvaluation) {
                        $passedLevelTwoInvoices[] = $invoice;
                    }
                }

                // Specifics check
                if (sizeof($passedLevelTwoInvoices) > 0) {
                    $specific = $applicablePostfix['specific'];

                    $difference = [];
                    $itemIdArray = [];
                    $exit_outer_loop=0;

                    for($i=0; 1 ; $i++){
                        $offer_found = 0;

                        foreach ($specific as $sl => $specPost) {
                            $itemUnitIdArray = [];
                            $item_unit_quantity_found = 0;
                            $first_time_occurence_of_item_unit_qty_greater_than_zero= 0;

                            foreach ($specPost['condition'] as $k => $specCon) {
                                if ($specCon['type'] == 'function') {
                                    if ($specCon['name'] == 'item_unit_quantity' || $specCon['name'] == 'item_unit_quantity_in_credit_invoices_over_a_period' || $specCon['name'] == 'item_unit_quantity_in_cash_invoices_over_a_period') {

                                        if(!isset($specCon['plus_found_after_item_unit_quantity'])){
                                            $first_time_occurence_of_item_unit_qty_greater_than_zero = 0;
                                        }

                                        $argArray = explode(',', $specCon['arg']);
                                        $result = $FunctionHelper->$specCon['name']($argArray[0], $passedLevelTwoInvoices, $i, $item_unit_quantity_found,$first_time_occurence_of_item_unit_qty_greater_than_zero, $argArray[1]);
                                        $item_unit_info = TableRegistry::get('item_units')->find('all', ['conditions'=>['item_name'=>str_replace("'", '', $argArray[0]), 'unit_display_name'=>str_replace("'", '', $argArray[1]), 'status'=>1]])->first();
                                        $item_unit_id = $item_unit_info['id'];
                                        $itemUnitIdArray[] = $item_unit_id;

                                        if($i>0){
                                            if(($first_time_occurence_of_item_unit_qty_greater_than_zero==0)&&($result>0)){
                                                $result= $difference[$item_unit_id];
                                                $first_time_occurence_of_item_unit_qty_greater_than_zero=1;
                                            }
                                            else {
                                                $result = 0;
                                            }
                                        }

                                    } elseif ($specCon['name'] == 'max_due_invoice_age' || $specCon['name'] == 'is_mango_customer' || $specCon['name'] == 'is_cash_invoice' || $specCon['name'] == 'payment_date' || $specCon['name'] == 'invoice_payment_age' || $specCon['name'] == 'invoice_item_payment_age') {
                                        $result = $FunctionHelper->$specCon['name']($passedLevelTwoInvoices);
                                    } elseif($specCon['name'] == 'item_bulk_quantity'){
                                        $argArray = explode(',', $specCon['arg']);
                                        $result = $FunctionHelper->$specCon['name']($argArray[0], $argArray[1], $passedLevelTwoInvoices);
                                    }elseif($specCon['name'] == 'item_unit_net_sales_value'){
                                        $argArray = explode(',', $specCon['arg']);
                                        $result = $FunctionHelper->$specCon['name']($argArray[0], $passedLevelTwoInvoices, $argArray[1]);
                                    }

                                    if (isset($result)) {
                                        $specPost['condition'][$k]['type'] = 'number';
                                        $specPost['condition'][$k]['number'] = $result;
                                        unset($specPost['condition'][$k]['name']);
                                        unset($specPost['condition'][$k]['arg']);
                                    }
                                }
                            }

                            $eval = $FunctionHelper->postfix_evaluator($specPost['condition'], $specPost['range_start']);
                            $specConEvaluation = $eval['result'];

                            // If specific condition is true then amount will be evaluated
                            if ($specConEvaluation) {
                                if($specPost['range_start']==0){
                                    $exit_outer_loop = 1;
                                }

                                $itemUnitIdArray = array_unique($itemUnitIdArray);
                                foreach($itemUnitIdArray as $itemUnit){
                                    $difference[$itemUnit] = $eval['diff'];
                                }

                                foreach ($specPost['amount'] as $k => $specAmount) {
                                    if ($specAmount['type'] == 'function') {
                                        $exit_outer_loop = 1;

                                        if ($specAmount['name'] == 'item_unit_quantity' || $specAmount['name'] == 'item_bulk_quantity' || $specAmount['name'] == 'item_unit_quantity_in_credit_invoices_over_a_period' || $specAmount['name'] == 'item_unit_quantity_in_cash_invoices_over_a_period') {
                                            $argArray = explode(',', $specAmount['arg']);
                                            $result = $FunctionHelper->$specAmount['name']($argArray[0], $passedLevelTwoInvoices, 0, 0, 0, $argArray[1]);
                                        } elseif ($specAmount['name'] == 'max_due_invoice_age' || $specAmount['name'] == 'is_mango_customer' || $specAmount['name'] == 'is_cash_invoice' || $specAmount['name'] == 'payment_date' || $specAmount['name'] == 'invoice_payment_age' || $specAmount['name'] == 'invoice_item_payment_age') {
                                            $result = $FunctionHelper->$specAmount['name']($passedLevelTwoInvoices);
                                        }elseif($specAmount['name'] == 'item_unit_net_sales_value'){
                                            $argArray = explode(',', $specAmount['arg']);
                                            $result = $FunctionHelper->$specAmount['name']($argArray[0], $passedLevelTwoInvoices, $argArray[1]);
                                        }

                                        if (isset($result)) {
                                            $specPost['amount'][$k]['type'] = 'number';
                                            $specPost['amount'][$k]['number'] = $result;
                                            unset($specPost['amount'][$k]['name']);
                                            unset($specPost['amount'][$k]['arg']);
                                        }
                                    }
                                }

                                $specAmountEvaluation = $FunctionHelper->postfix_evaluator($specPost['amount'])['result'];
                                $specAmountEvaluationFunctionFound = $FunctionHelper->postfix_evaluator($specPost['amount'])['function_found'];
                                $wonOffers[$i][$sl]['value'] = $specAmountEvaluation;
                                $wonOffers[$i][$sl]['offer_id'] = $offer_id;
                                $wonOffers[$i][$sl]['offer_type'] = $specific[$key]['offer_type'];
                                $wonOffers[$i][$sl]['offer_name'] = $specific[$key]['offer_name'];
                                $wonOffers[$i][$sl]['offer_unit_name'] = $specific[$key]['offer_unit_name'];
                                $wonOffers[$i][$sl]['amount_type'] = $specific[$key]['amount_type'];
                                $wonOffers[$i][$sl]['payment_mode'] = $specific[$key]['payment_mode'];
                                $wonOffers[$i][$sl]['amount_unit'] = $specific[$key]['amount_unit'];

                                $offer_found = 1;
                                $offer_found_count++;
                            }

                            if((sizeof($specific)-1 == $sl) && ($offer_found == 0)){
                                $exit_outer_loop = 1;
                                break;
                            }
                        }

                        if(isset($exit_outer_loop) && $exit_outer_loop==1){
                            break;
                        }
                        if($i==1){break;}
                    }
                }
            }
        }

        $newArr = [];

        if(sizeof($wonOffers)>0){
            foreach($wonOffers as $iteration=>$specificWon){
                foreach($specificWon as $key=>$won){
                    $newArr[] = $won;
                }
            }

            $testArray = [];
            foreach($newArr as $sl=>$new){
                $testArray[$sl] =  $new['offer_type'];
            }

            $testArray = array_unique($testArray);

            $sumArray = [];
            foreach($testArray as $test){
                $sumArray[$test] = 0;
                foreach($newArr as $k=>$new){
                    if($test==$new['offer_type']){
                        $sumArray[$test] += $new['value'];
                    }
                }
            }

            $final = [];
            $is = 0;
            foreach($sumArray as $offer_type=>$sum){
                foreach($newArr as $new){
                    if($new['offer_type']==$offer_type){
                        $final[$is]['value'] = $sum;
                        $final[$is]['offer_id'] = $new['offer_id'];
                        $final[$is]['offer_type'] = $new['offer_type'];
                        $final[$is]['offer_name'] = $new['offer_name'];
                        $final[$is]['offer_unit_name'] = $new['offer_unit_name'];
                        $final[$is]['amount_type'] = $new['amount_type'];
                        $final[$is]['payment_mode'] = $new['payment_mode'];
                        $final[$is]['amount_unit'] = $new['amount_unit'];
                    }
                }
                $is++;
            }

            return $final;
        }else{
            return [];
        }
    }

    public function getCustomerDue($customer_id, $date)
    {
        $closestUptoDateDue = TableRegistry::get('personal_accounts')->find()->hydrate(false);
        $closestUptoDateDue->where(['account_code' => Configure::read('account_receivable_code')]);
        $closestUptoDateDue->where(['applies_to_id' => $customer_id]);
        $closestUptoDateDue->where(['upto_date <' => $date]);
        $closestUptoDateDue->order(['upto_date' => 'DESC']);
        $closestUptoDateDue->first();

        if ($closestUptoDateDue->toArray()) {
            $uptoDateDue = $closestUptoDateDue->toArray()[0]['balance_value'];
            $uptoDate = $closestUptoDateDue->toArray()[0]['upto_date'];
        } else {
            $uptoDateDue = 0;
            $uptoDate = strtotime('01-01-2010');
        }

        $betweenDateInvoices = TableRegistry::get('invoices')->find()->hydrate(false);
        $betweenDateInvoices->where(['invoice_date >' => $uptoDate]);
        $betweenDateInvoices->where(['invoice_date <=' => $date]);
        $betweenDateInvoices->where(['customer_id' => $customer_id]);
        $betweenDateInvoices->where(['invoice_date', 'customer_id', 'net_total']);
        $betweenDateInvoices->select(['net_total' => 'SUM(net_total)']);

        if ($betweenDateInvoices->toArray()) {
            $betweenDateInvoicesNetTotal = $betweenDateInvoices->toArray()[0]['net_total'];
        } else {
            $betweenDateInvoicesNetTotal = 0;
        }

        $betweenDatePayments = TableRegistry::get('invoice_payments')->find()->hydrate(false);
        $betweenDatePayments->where(['payment_collection_date >' => $uptoDate]);
        $betweenDatePayments->where(['payment_collection_date <=' => $date]);
        $betweenDatePayments->where(['customer_id' => $customer_id]);
        $betweenDatePayments->select(['invoice_wise_payment_amount' => 'SUM(invoice_wise_payment_amount)']);
        $betweenDatePayments->first();
        if ($betweenDatePayments->toArray()) {
            $betweenDatePaymentsNetTotal = $betweenDatePayments->toArray()[0]['invoice_wise_payment_amount'];
        } else {
            $betweenDatePaymentsNetTotal = 0;
        }

        $finalDue = $uptoDateDue + $betweenDateInvoicesNetTotal - $betweenDatePaymentsNetTotal;
        return $finalDue;
    }

    public function get_balance_value_of_account_head($code)
    {
        $res = TableRegistry::get('accounts')->find('all')
            ->where(['code' => $code, 'status' => 1])
            ->orderDesc('created_date')
            ->hydrate(false)->first();

        $result['upto_date'] = $res['upto_date'];
        $result['balance_value'] = $res['balance_value'];
        return $result;
    }

    public function gross_due_of_customer($customer_id)
    {
        $invoice_table = TableRegistry::get('invoices');
        $result = $invoice_table->find()
            ->where(['customer_id' => $customer_id, 'status' => 1]);
        $total_due = $result->select(['gross_due' => $result->func()->sum('due')])->hydrate(false)->toArray();
        if (empty($total_due)) {
            return 0;
        } else {
            return $total_due[0]['gross_due'];
        }
    }

    public function get_total_credit_note_amount($code, $date)
    {
        $get_account_data = $this->get_balance_value_of_account_head($code);
        $start_date = $get_account_data['upto_date'];
        $end_date = $date - 3600; //1 hour before
        $credit_note_approved_adjusted =
            TableRegistry::get('credit_notes')
                ->find('all')
                ->select(['total_after_demurrage', 'id', 'remaining_amount'])
                ->hydrate(false)
                ->where(['approval_status' => 3])
                ->orWhere(['adjustment_status' => 1])
                ->orWhere(['adjustment_status' => 2])
                ->andWhere(
                    function ($exp) use ($start_date, $end_date) {
                        return $exp->between('date', $start_date, $end_date);
                    })
                ->andWhere(['status' => 1])->toArray();

        $sum_of_credit_note_approved_adjusted_all = 0;
        foreach ($credit_note_approved_adjusted as $approved_adjusted_all):
            $sum_of_credit_note_approved_adjusted_all += $approved_adjusted_all['remaining_amount'];
        endforeach;


        $credit_note_adjusted = TableRegistry::get('credit_note_adjustments')
            ->find('all')
            ->select(['adjustment_amount', 'adjustment_date', 'credit_note_id'])
            ->hydrate(false)
            ->Where(
                function ($exp) use ($start_date, $end_date) {
                    return $exp->between('adjustment_date', $start_date, $end_date);
                })
            ->andWhere(['status' => 1])->toArray();

        $total_of_credit_note_adjusted = 0;
        foreach ($credit_note_adjusted as $adjusted):
            $total_of_credit_note_adjusted += $adjusted['adjustment_amount'];
        endforeach;

        $adjustable_amount = ($sum_of_credit_note_approved_adjusted_all + $get_account_data['balance_value']) - $total_of_credit_note_adjusted;

        return $adjustable_amount;
    }

    public function get_unit_opening_due($space_level, $space_global_id, $group_by_level, $date){
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT customer_unit_global_id  & ' . $expression . ' as global_id, SUM(due) as total_due from invoices
            WHERE invoice_date <= ' . $date . '
            AND customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

        }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(due) as total_due from invoices
            WHERE invoice_date <= ' . $date . '
            AND customer_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND customer_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY customer_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT customer_unit_global_id  & ' . $expression . ' as global_id, SUM(due) as total_due from invoices
            WHERE invoice_date <= ' . $date . '
            AND customer_unit_global_id = ' . $space_global_id . '
            GROUP BY global_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(due) as total_due from invoices
            WHERE invoice_date <= ' . $date . '
            AND customer_unit_global_id = ' . $space_global_id . '
            GROUP BY customer_id');
        }

        $results = $query->fetchAll('assoc');
        $arr = [];
        foreach($results as $result){
            $arr[$result['global_id']] = $result['total_due'];
        }

        return $arr;
    }

    public function get_unit_credit_note_amount($space_level, $space_global_id, $start_date, $end_date, $group_by_level)
    {
        $approval_status = array_flip(Configure::read('credit_note_approval_status'))['approved'];
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT parent_global_id  & ' . $expression . ' as global_id, SUM(total_after_demurrage) as total from credit_notes
            WHERE date >= ' . $start_date . '
            AND date <= ' . $end_date . '
            AND approval_status = ' . $approval_status . '
            AND parent_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND parent_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

        }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(total_after_demurrage) as total from credit_notes
            WHERE date >= ' . $start_date . '
            AND date <= ' . $end_date . '
            AND approval_status = ' . $approval_status . '
            AND parent_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND parent_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY customer_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT parent_global_id  & ' . $expression . ' as global_id, SUM(total_after_demurrage) as total from credit_notes
            WHERE date >= ' . $start_date . '
            AND date <= ' . $end_date . '
            AND approval_status = ' . $approval_status . '
            AND parent_global_id = ' . $space_global_id . '
            GROUP BY global_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(total_after_demurrage) as total from credit_notes
            WHERE date >= ' . $start_date . '
            AND date <= ' . $end_date . '
            AND approval_status = ' . $approval_status . '
            AND parent_global_id = ' . $space_global_id . '
            GROUP BY customer_id');
        }

        $results = $query->fetchAll('assoc');
        $arr = [];
        foreach($results as $result){
            $arr[$result['global_id']] = $result['total'];
        }
        return $arr;
    }

    public function get_unit_adjustment_amount($space_level, $space_global_id, $start_date, $end_date, $group_by_level)
    {
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT parent_global_id  & ' . $expression . ' as global_id, SUM(invoice_wise_payment_amount) as total from invoice_payments
            WHERE created_date >= ' . $start_date . '
            AND created_date <= ' . $end_date . '
            AND is_adjustment = 1
            AND parent_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND parent_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

        }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(invoice_wise_payment_amount) as total from invoice_payments
            WHERE created_date >= ' . $start_date . '
            AND created_date <= ' . $end_date . '
            AND is_adjustment = 1
            AND parent_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND parent_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY customer_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
            $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

            $query = $conn->execute('
            SELECT parent_global_id  & ' . $expression . ' as global_id, SUM(invoice_wise_payment_amount) as total from invoice_payments
            WHERE created_date >= ' . $start_date . '
            AND created_date <= ' . $end_date . '
            AND is_adjustment = 1
            AND parent_global_id = ' . $space_global_id . '
            GROUP BY global_id');

        }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
            $query = $conn->execute('
            SELECT customer_id as global_id, SUM(invoice_wise_payment_amount) as total from invoice_payments
            WHERE created_date >= ' . $start_date . '
            AND created_date <= ' . $end_date . '
            AND is_adjustment = 1
            AND parent_global_id = ' . $space_global_id . '
            GROUP BY customer_id');
        }

        $results = $query->fetchAll('assoc');
        $arr = [];
        foreach($results as $result){
            $arr[$result['global_id']] = $result['total'];
        }
        return $arr;
    }

    public function get_unit_sales_budget($space_level, $space_global_id, $group_by_level, $start_date, $end_date, $product_scope, $item_id=null){
        $limitStart = pow(2, (Configure::read('max_level_no') - $space_level - 1) * 5);
        $limitEnd = pow(2, (Configure::read('max_level_no') - $space_level) * 5);
        $conn = ConnectionManager::get('default');

        if($product_scope == 1 && $item_id>0){
            if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . ' AND item_id = ' . $item_id . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . ' AND item_id = ' . $item_id .'
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY administrative_unit_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget, sales_measure_unit from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_id = ' . $item_id . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY global_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_id = ' . $item_id . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY administrative_unit_id');
            }
        }elseif($product_scope == 3 && $item_id>0){
            if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_unit_id = ' . $item_id . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_unit_id = ' . $item_id . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY administrative_unit_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget, sales_measure_unit from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_unit_id = ' . $item_id . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY global_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '  AND item_unit_id = ' . $item_id . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY administrative_unit_id');
            }
        }else{
            if($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY global_id');

            }elseif($space_level < Configure::read('max_level_no') && $group_by_level <= Configure::read('max_level_no')+1){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id-' . $space_global_id . ' >= ' . $limitStart . ' AND administrative_unit_global_id-' . $space_global_id . ' < ' . $limitEnd . '
            GROUP BY administrative_unit_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level == $space_level){
                $expression = (pow(2, (1 + 5 * $group_by_level)) - 1) * pow(2, (5 * (Configure::read('max_level_no') - $group_by_level)));

                $query = $conn->execute('
            SELECT administrative_unit_global_id  & ' . $expression . ' as global_id, SUM(budget_amount) as total_budget, sales_measure_unit from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY global_id');

            }elseif($space_level == Configure::read('max_level_no') && $group_by_level > $space_level){
                $query = $conn->execute('
            SELECT administrative_unit_id as global_id, SUM(budget_amount) as total_budget from sales_budgets
            WHERE budget_period_start >= ' . $start_date . ' AND budget_period_end >= ' . $end_date . '
            AND administrative_unit_global_id = ' . $space_global_id . '
            GROUP BY administrative_unit_id');
            }
        }


        $results = $query->fetchAll('assoc');
        $arr = [];
        foreach($results as $result){
            $arr[$result['global_id']]['budget'] = $result['total_budget'];
        }
        return $arr;
    }

}
