<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * Stocks Controller
 *
 * @property \App\Model\Table\StocksTable $Stocks
 */
class CreditSalesPoliciesController extends AppController
{
    public $paginate = [
        'limit' => 15,
        'order' => [
            'CreditSalesPolicies.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->loadModel('CreditSalesPolicies');

        $creditSalesPolicies = $this->CreditSalesPolicies->find('all', [
            'conditions' => ['status !=' => 99],
            'contain' => []
        ]);
        $creditSalesPolicies = $this->paginate($creditSalesPolicies);

        $this->set(compact('creditSalesPolicies'));
        $this->set('_serialize', ['creditSalesPolicies']);
    }

    /**
     * View method
     *
     * @param string|null $id Stock id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $stock = $this->Stocks->get($id, [
            'contain' => ['Warehouses', 'Items']
        ]);
        $this->set('stock', $stock);
        $this->set('_serialize', ['stock']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Auth->user();
        $time = time();
        $creditSalesPolicy = $this->CreditSalesPolicies->newEntity();

        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($user, $time, &$saveStatus)
                {
                    $input = $this->request->data;
                    $creditSalesPolicy = $this->CreditSalesPolicies->newEntity();
                    $data['policy_start_date'] = strtotime($input['policy_start_date']);
                    $data['policy_expected_end_date'] = strtotime($input['policy_expected_end_date']);
                    $data['policy_detail'] = $input['policy_detail'];
                    $data['created_by'] = $user['id'];
                    $data['created_date'] = $time;

                    $creditSalesPolicy = $this->CreditSalesPolicies->patchEntity($creditSalesPolicy, $data);
                    $this->CreditSalesPolicies->save($creditSalesPolicy);
                });

                $this->Flash->success('The Policy is defined. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                $this->Flash->error('Policy Not Defined. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $policyDetail = [];
        $policyDetail['credit_invoice_max_age_for_credit_invoicing'] = 180;
        $policyDetail['credit_invoice_max_age_for_cash_invoicing'] = 120;
        $policyDetail['commissions_based_on_payment_age']['commissions'][0]['payment_age_start'] = 0;
        $policyDetail['commissions_based_on_payment_age']['commissions'][0]['payment_age_end'] = 30;
        $policyDetail['commissions_based_on_payment_age']['commissions'][0]['commission'] = 8;
        $policyDetail['commissions_based_on_payment_age']['commissions'][1]['payment_age_start'] = 31;
        $policyDetail['commissions_based_on_payment_age']['commissions'][1]['payment_age_end'] = 60;
        $policyDetail['commissions_based_on_payment_age']['commissions'][1]['commission'] = 7;
        $policyDetail['commissions_based_on_payment_age']['commissions'][2]['payment_age_start'] = 61;
        $policyDetail['commissions_based_on_payment_age']['commissions'][2]['payment_age_end'] = 90;
        $policyDetail['commissions_based_on_payment_age']['commissions'][2]['commission'] = 6;
        $policyDetail['commissions_based_on_payment_age']['commissions'][3]['payment_age_start'] = 91;
        $policyDetail['commissions_based_on_payment_age']['commissions'][3]['payment_age_end'] = 180;
        $policyDetail['commissions_based_on_payment_age']['commissions'][3]['commission'] = 5;
        $policyDetail['commissions_based_on_payment_age']['adjustment_time_period'][0]['commission_adjusted_from'] = "01-07";
        $policyDetail['commissions_based_on_payment_age']['adjustment_time_period'][0]['commission_adjusted_within'] = "31-12";
        $policyDetail['commissions_based_on_payment_age']['adjustment_time_period'][1]['commission_adjusted_from'] = "01-01";
        $policyDetail['commissions_based_on_payment_age']['adjustment_time_period'][1]['commission_adjusted_within'] = "30-06";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_invoice_from'] = '';
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_invoice_upto'] = "30-09";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['payment_from'] = "01-10";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['payment_upto'] = "30-06";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_payment_ratio'] = 1;
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['commission'] = 5;
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_invoice_from'] = '';
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_invoice_upto'] = "31-03";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['payment_from'] = "01-04";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['payment_upto'] = "31-12";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['due_payment_ratio'] = 1;
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['commissions'][0]['commission'] = 5;
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['adjustment_time_period'][0]['commission_adjusted_from'] = "01-07";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['adjustment_time_period'][0]['commission_adjusted_within'] = "31-12";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['adjustment_time_period'][1]['commission_adjusted_from'] = "01-01";
        $policyDetail['commission_for_paying_due_invoices_of_a_particular_time_period_within_another_specified_time_period']['adjustment_time_period'][1]['commission_adjusted_within'] = "30-06";

        $this->set(compact('policyDetail', 'creditSalesPolicy'));
        $this->set('_serialize', ['policyDetail']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Stock id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $stock = $this->Stocks->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $stock = $this->Stocks->patchEntity($stock, $data);
            if ($this->Stocks->save($stock)) {
                $this->Flash->success('The stock has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The stock could not be saved. Please, try again.');
            }
        }
        $warehouses = $this->Stocks->Warehouses->find('list', ['conditions' => ['status' => 1]]);
        $items = $this->Stocks->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('stock', 'warehouses', 'items'));
        $this->set('_serialize', ['stock']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Stock id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $stock = $this->Stocks->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $stock = $this->Stocks->patchEntity($stock, $data);
        if ($this->Stocks->save($stock)) {
            $this->Flash->success('The stock has been deleted.');
        } else {
            $this->Flash->error('The stock could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
