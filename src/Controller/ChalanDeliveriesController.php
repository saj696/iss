<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * InvoiceChalans Controller
 *
 * @property \App\Model\Table\InvoiceChalansTable $InvoiceChalans
 */
class ChalanDeliveriesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'PoEvents.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = $this->Auth->user();
        $this->loadModel('PoEvents');
        $events = $this->PoEvents->find('all', [
            'conditions' => ['PoEvents.status !=' => 99, 'recipient_id'=>$user['id'], 'is_action_taken'=>0, 'event_type'=>array_flip(Configure::read('po_event_types'))['deliver']],
            'contain'=>['InvoiceChalans'=>['InvoiceChalanDetails']]
        ]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemArray = $SystemHelper->get_item_unit_array();

        $events = $this->paginate($events);
        $this->set(compact('events', 'itemArray'));
        $this->set('_serialize', ['events']);
    }

    /**
     * View method
     *
     * @param string|null $id Invoice Chalan id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $invoiceChalan = $this->InvoiceChalans->get($id, [
            'contain' => []
        ]);
        $this->set('invoiceChalan', $invoiceChalan);
        $this->set('_serialize', ['invoiceChalan']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Invoice Chalan id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->loadModel('PoEvents');
        $this->loadModel('Stocks');
        $this->loadModel('ItemUnits');
        $user = $this->Auth->user();
        $time = time();
        $event = $this->PoEvents->find('all', [
            'conditions' => ['PoEvents.id' => $id, 'recipient_id'=>$user['id'], 'is_action_taken'=>0, 'event_type'=>array_flip(Configure::read('po_event_types'))['deliver']],
            'contain'=>['InvoiceChalans'=>['InvoiceChalanDetails']]
        ])->first();

        $invoiceIds = json_decode($event['invoice_chalan']['reference_invoices'], true);
        $chalanDetail = $event['invoice_chalan']['invoice_chalan_details'];

        try {
            $saveStatus = 0;
            $conn = ConnectionManager::get('default');
            $conn->transactional(function () use ($event, $invoiceIds, $chalanDetail, $id, $user, $time, &$saveStatus)
            {
                // Invoice delivery status change
                foreach($invoiceIds as $invoiceId){
                    $invoice = TableRegistry::get('invoices');
                    $query = $invoice->query();
                    $query->update()->set(['delivery_status' => array_flip(Configure::read('invoice_delivery_status'))['delivered']])->where(['id' => $invoiceId])->execute();
                }
                // Warehouse Stock reduce
                $warehouse_id = $user['warehouse_id'];
                foreach($chalanDetail as $detail) {
                    $temUnitInfo = $this->ItemUnits->get($detail['item_unit_id']);
                    $stockInfo = $this->Stocks->find('all', ['conditions'=>['status !='=>99, 'warehouse_id'=>$warehouse_id, 'item_id'=>$temUnitInfo['item_id'], 'manufacture_unit_id'=>$temUnitInfo['manufacture_unit_id']]])->first();

                    if($stockInfo && ($stockInfo->quantity > $detail['quantity'])) {
                        $newStockQuantity = $stockInfo->quantity - $detail['quantity'];
                        $stock = TableRegistry::get('stocks');
                        $query = $stock->query();
                        $query->update()->set(['quantity' => $newStockQuantity])->where(['id' => $stockInfo->id])->execute();
                    } else {
                        $this->Flash->error('Stock is not enough, delivery not possible. Please try again!');
                        throw new \Exception('error');
                        break;
                    }
                }

                //Chalan status update
                $chalan = TableRegistry::get('invoice_chalans');
                $query = $chalan->query();
                $query->update()->set(['chalan_status' => array_flip(Configure::read('invoice_chalan_status'))['delivered']])->where(['id' => $event['invoice_chalan']['id']])->execute();
                //Event action update
                $chalan = TableRegistry::get('po_events');
                $query = $chalan->query();
                $query->update()->set(['is_action_taken' => 1])->where(['id' => $id])->execute();
            });

            $this->Flash->success('You have successfully delivered the chalan. Thank you!');
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
            $this->Flash->error('Chalan delivery not possible. Please try again!');
            return $this->redirect(['action' => 'index']);
        }
    }
}
