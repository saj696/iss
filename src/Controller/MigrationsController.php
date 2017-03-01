<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * AccountHeads Controller
 *
 * @property \App\Model\Table\AccountHeadsTable $AccountHeads
 */
class MigrationsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'AccountHeads.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use (&$saveStatus, $conn)
            {
//                $query = $conn->execute('SELECT DISTINCT(customer_code) from personal_accounts_temp');
//                $results = $query->fetchAll('assoc');
//
//                foreach($results as $result){
//                    $query = $conn->execute('DELETE FROM personal_accounts where customer_code= "'.$result['customer_code'].'"');
//                }

//                $invoices = TableRegistry::get('invoices')->find()->hydrate(false);
//                $invoices->where(['id >'=>2654]);
//                $invoices->toArray();
//
//                foreach($invoices as $invoice){
//                    $customer = TableRegistry::get('customers')->find()->hydrate(false);
//                    $customer->where(['code'=>$invoice['customer_code']])->first();
//                    $customer = $customer->toArray()[0];
//
//                    // update invoice data
//                    $invoiceUpdate = TableRegistry::get('invoices');
//                    $query = $invoiceUpdate->query();
//                    $query->update()->set([
//                        'customer_level_no' => $customer['level_no'],
//                        'customer_unit_global_id' => $customer['unit_global_id'],
//                        'customer_type' => $customer['business_type'],
//                        'customer_id' => $customer['id'],
//                        'approval_status' => 3,
//                        'invoice_type' => 2,
//                        'max_due_invoice_age' => 0,
//                    ])->where(['id' => $invoice['id']])->execute();
//                }

//                =====================================================================

//                $invoices = TableRegistry::get('personal_accounts')->find()->hydrate(false);
//                $invoices->where(['id >'=>807]);
//                $invoices->toArray();
//
//                foreach($invoices as $invoice){
//                    $customer = TableRegistry::get('customers')->find()->hydrate(false);
//                    $customer->where(['code'=>$invoice['customer_code']])->first();
//                    $customer = $customer->toArray()[0];
//
//                    // update invoice data
//                    $invoiceUpdate = TableRegistry::get('personal_accounts');
//                    $query = $invoiceUpdate->query();
//                    $query->update()->set([
//                        'applies_to' => 1,
//                        'applies_to_id' => $customer['id'],
//                        'account_code' => 211000,
//                        'unit_global_id' => $customer['unit_global_id'],
//                        'from_date' => 946684800,
//                    ])->where(['id' => $invoice['id']])->execute();
//                }
            });

            echo 'Migration done successfully. Thank you!';
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
        }

        $this->autoRender = false;

    }
}
