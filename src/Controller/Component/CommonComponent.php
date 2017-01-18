<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
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
                        $value = $stock['quantity'] /1000;
                        $sum += $value;
                    } else {
                        $sum += $stock['quantity'];
                    }
                } else {
                    if ($stock['unit']['unit_type'] == 1 || $stock['unit']['unit_type'] == 3) {
                        $value = $stock['unit']['converted_quantity'] * $stock['quantity'] /1000;
                        $sum += $value;
                    } else {
                        $value = $stock['unit']['converted_quantity'] *$stock['quantity'];
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
            pr($due_available);die;
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

    public function getPdf($html){
        \Composer\Autoload\includeFile(ROOT . '\vendor' . DS  . 'mpdf' . DS .'mpdf' . DS . 'mpdf.php');
        $mpdf=new mPDF();
        $mpdf->useAdobeCJK=true;
        //$mpdf->autoLangToFont(AUTOFONT_ALL);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT.'css/mpdf.css'),1);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT.'css/report.css'),1);
        $mpdf->WriteHTML(file_get_contents(WWW_ROOT.'assets/global/plugins/bootstrap/css/bootstrap.min.css'),1);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

}
