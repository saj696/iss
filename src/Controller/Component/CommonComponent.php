<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Hashids\Hashids;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;
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

}
