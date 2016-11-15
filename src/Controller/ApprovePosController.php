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
 * Pos Controller
 *
 * @property \App\Model\Table\PosTable $Pos
 */
class ApprovePosController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Pos.id' => 'desc'
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
        $this->loadModel('Pos');
        $this->loadModel('PoEvents');
        $events = $this->PoEvents->find('all', [
            'conditions' => ['Pos.status !=' => 99, 'recipient_id'=>$user['id']],
            'contain' => ['Pos'=>['PoProducts', 'Customers']]
        ]);

        $this->set('events', $this->paginate($events));
        $this->set('_serialize', ['events']);
    }

    /**
     * View method
     *
     * @param string|null $id Po id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $this->loadModel('Pos');
        $po = $this->Pos->get($id, [
            'contain' => ['Customers']
        ]);
        $this->set('po', $po);
        $this->set('_serialize', ['po']);
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
        $po = $this->Pos->newEntity();
        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($po, $user, $time, &$saveStatus)
                {
                    $data = $this->request->data;
                    $this->loadModel('Pos');
                    $this->loadModel('AdministrativeUnits');
                    $this->loadModel('Customers');
                    $this->loadModel('PoProducts');
                    $this->loadModel('DepotCoverages');
                    $this->loadModel('PoEvents');
                    $this->loadModel('Users');
                    $this->loadModel('Depots');

                    $poData['customer_level_no'] = $data['customer_level_no'];
                    $customerUnitInfo = $this->AdministrativeUnits->get($data['customer_unit']);
                    $poData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                    $poData['customer_id'] = $data['customer_id'];
                    $customerInfo = $this->Customers->get($data['customer_id']);
                    if($customerInfo['is_mango']==1):
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
                    else:
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
                    endif;
                    $poData['po_date'] = strtotime($data['po_date']);
                    $poData['delivery_date'] = strtotime($data['delivery_date']);
                    $poData['invoice_type'] = $data['invoice_type'];
                    $poData['net_total'] = $data['total_amount_hidden'];

                    $poData['created_by'] = $user['id'];
                    $poData['created_date'] = $time;
                    $po = $this->Pos->patchEntity($po, $poData);
                    $result = $this->Pos->save($po);
                    // PO Products table insert
                    foreach($data['detail'] as $item_id=>$itemDetail):
                        $poProducts = $this->PoProducts->newEntity();
                        $poProductData['po_id'] = $result['id'];
                        $poProductData['product_id'] = $item_id;
                        $poProductData['product_quantity'] = $itemDetail['item_quantity'];
                        $poProductData['bonus_quantity'] = $itemDetail['item_bonus'];
                        $poProductData['instant_discount'] = $itemDetail['item_cash_discount'];
                        $poProductData['net_total'] = $itemDetail['item_net_total'];
                        $poProductData['created_by'] = $user['id'];
                        $poProductData['created_date'] = $time;
                        $poProducts = $this->PoProducts->patchEntity($poProducts, $poProductData);
                        $this->PoProducts->save($poProducts);
                    endforeach;

                    // Event Creation
                    $poEvent = $this->PoEvents->newEntity();
                    $customerLevel = $data['customer_level_no'];
                    $customerLevelDepotCoverage = $this->DepotCoverages->find('all', ['conditions'=>['level_no'=>$customerLevel]])->first();
                    $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotCoverage['depot_id']]])->first();

                    if($depotInCharge['id']):
                        $recipient_id = $depotInCharge['id'];
                    else:
                        $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$customerLevel]])->first();
                        $customerLevelDepotId = $customerLevelDepot['id'];
                        if($customerLevelDepotId):
                            $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotId]])->first();
                            $recipient_id = $depotInCharge['id'];
                        else:
                            for($i=$customerLevel; $i>=0; $i--):
                                $customerLevelDepot = $this->Depots->find('all', ['conditions'=>['status !='=>99, 'level_no'=>$i]])->first();
                                $customerLevelDepotId = $customerLevelDepot['id'];
                                $depotInCharge = $this->Users->find('all', ['conditions'=>['depot_id'=>$customerLevelDepotId]])->first();
                                $recipient_id = $depotInCharge['id'];
                                if($recipient_id>0):
                                    break;
                                else:
                                    continue;
                                endif;
                            endfor;
                        endif;
                    endif;

                    if($recipient_id && $recipient_id>0):
                        $poEventData['po_id'] = $result['id'];
                        $poEventData['recipient_id'] = $recipient_id;
                        $poEventData['created_by'] = $user['id'];
                        $poEventData['created_date'] = $time;
                        $poEvent = $this->PoEvents->patchEntity($poEvent, $poEventData);
                        $this->PoEvents->save($poEvent);
                    else:
                        $this->Flash->error('No Depot In Charge. Please try again!');
                        throw new \Exception('error');
                    endif;
                });

                $this->Flash->success('PO done successfully. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('PO not possible. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('AdministrativeLevels');
        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }
        $customers = $this->Pos->Customers->find('list', ['conditions' => ['status' => 1]]);
        $this->loadModel('Items');
        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';
        }
        $this->set(compact('po', 'customers', 'administrativeLevels', 'itemArray'));
        $this->set('_serialize', ['po']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Po id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $this->loadModel('Pos');
        $this->loadModel('AdministrativeUnits');
        $this->loadModel('AdministrativeLevels');
        $this->loadModel('Customers');
        $this->loadModel('PoProducts');
        $this->loadModel('DepotCoverages');
        $this->loadModel('PoEvents');
        $this->loadModel('Users');
        $this->loadModel('Depots');
        $this->loadModel('Items');

        $event = $this->PoEvents->get($id, [
            'contain' => ['Pos'=>['PoProducts', 'Customers']]
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($event, $user, $time, &$saveStatus)
                {
                    $data = $this->request->data;
                    $poData['customer_level_no'] = $data['customer_level_no'];
                    $customerUnitInfo = $this->AdministrativeUnits->get($data['customer_unit']);
                    $poData['customer_unit_global_id'] = $customerUnitInfo['global_id'];
                    $poData['customer_id'] = $data['customer_id'];
                    $customerInfo = $this->Customers->get($data['customer_id']);
                    if($customerInfo['is_mango']==1):
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['mango'];
                    else:
                        $poData['customer_type'] = array_flip(Configure::read('po_customer_type'))['general'];
                    endif;
                    $poData['po_date'] = strtotime($data['po_date']);
                    $poData['delivery_date'] = strtotime($data['delivery_date']);
                    $poData['invoice_type'] = $data['invoice_type'];
                    $poData['net_total'] = $data['total_amount_hidden'];

                    $poData['created_by'] = $user['id'];
                    $poData['created_date'] = $time;
                    $po = $this->Pos->patchEntity($po, $poData);
                    $result = $this->Pos->save($po);
                    // PO Products table insert
                    foreach($data['detail'] as $item_id=>$itemDetail):
                        $poProducts = $this->PoProducts->newEntity();
                        $poProductData['po_id'] = $result['id'];
                        $poProductData['product_id'] = $item_id;
                        $poProductData['product_quantity'] = $itemDetail['item_quantity'];
                        $poProductData['bonus_quantity'] = $itemDetail['item_bonus'];
                        $poProductData['instant_discount'] = $itemDetail['item_cash_discount'];
                        $poProductData['net_total'] = $itemDetail['item_net_total'];
                        $poProductData['created_by'] = $user['id'];
                        $poProductData['created_date'] = $time;
                        $poProducts = $this->PoProducts->patchEntity($poProducts, $poProductData);
                        $this->PoProducts->save($poProducts);
                    endforeach;
                });

                $this->Flash->success('PO done successfully. Thank you!');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('PO not possible. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $administrativeLevelsData = $this->AdministrativeLevels->find('all', ['conditions' => ['status' => 1]]);
        $administrativeLevels = [];
        foreach($administrativeLevelsData as $administrativeLevelsDatum)
        {
            $administrativeLevels[$administrativeLevelsDatum['level_no']] = $administrativeLevelsDatum['level_name'];
        }
        $customers = $this->Customers->find('list', ['conditions' => ['unit_global_id'=>$event['po']['customer_unit_global_id']]]);

        $administrativeUnits = $this->AdministrativeUnits->find('list', ['conditions'=>['level_no'=>$event['po']['customer_level_no']]]);
        $customerAdministrativeUnitInfo = $this->AdministrativeUnits->find('all', ['conditions'=>['global_id'=>$event['po']['customer_unit_global_id']]])->first();
        $customerAdministrativeUnit = $customerAdministrativeUnitInfo['id'];

        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemArray = [];
        $itemUnitPriceArray = [];
        foreach($items as $item) {
            $itemArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';

            if($event['po']['invoice_type']==1):
                $itemUnitPriceArray[$item['id']] = $item['cash_sales_price'];
            elseif($event['po']['invoice_type']==2):
                $itemUnitPriceArray[$item['id']] = $item['credit_sales_price'];
            endif;
        }
        $this->set(compact('itemUnitPriceArray', 'event', 'customers', 'administrativeLevels', 'itemArray', 'administrativeUnits', 'customerAdministrativeUnit'));
        $this->set('_serialize', ['event']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Po id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $po = $this->Pos->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $po = $this->Pos->patchEntity($po, $data);
        if ($this->Pos->save($po)) {
            $this->Flash->success('The po has been deleted.');
        } else {
            $this->Flash->error('The po could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function getUnit()
    {
        $data = $this->request->data;
        $level = $data['level'];
        $units = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['level_no' => $level], 'fields'=>['id', 'unit_name']])->hydrate(false)->toArray();

        $dropArray = [];
        foreach($units as $unit):
            $dropArray[$unit['id']] = $unit['unit_name'];
        endforeach;

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function getCustomer()
    {
        $data = $this->request->data;
        $unit = $data['unit'];
        $customers = TableRegistry::get('customers')->find('all', ['conditions' => ['administrative_unit_id' => $unit], 'fields'=>['id', 'name']])->hydrate(false)->toArray();

        $dropArray = [];
        foreach($customers as $customer):
            $dropArray[$customer['id']] = $customer['name'];
        endforeach;

        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function getCustomerDetail()
    {
        $this->autoRender = false;
        $data = $this->request->data;
        $customer_id = $data['customer_id'];
        $customer = TableRegistry::get('customers')->find('all', ['conditions' => ['id' => $customer_id]])->first();

        $arr = [];
        $arr['credit_limit'] = $customer->credit_limit?$customer->credit_limit:0;
        $arr['available_credit'] = $customer->credit_limit?$customer->credit_limit:0;
        $arr['cash_invoice_days'] = $customer->cash_invoice_days?$customer->cash_invoice_days:0;
        $arr['credit_invoice_days'] = $customer->credit_invoice_days?$customer->credit_invoice_days:0;

        $arr = json_encode($arr);
        $this->response->body($arr);
        return $this->response;
    }

   public function loadItem()
   {
       $data = $this->request->data;
       $item_id = $data['item_id'];
       $invoice_type = $data['invoice_type'];

       $this->loadModel('Items');
       $item = $this->Items->find('all', ['conditions' => ['id'=>$item_id, 'status' => 1]])->first()->toArray();

       if($invoice_type==1) {
           $unit_price = $item['cash_sales_price'];
       } elseif($invoice_type==2) {
           $unit_price = $item['credit_sales_price'];
       } else {
           $unit_price = 0;
       }

//       App::import('Helper', 'SystemHelper');
//       $SystemHelper = new SystemHelper(new View());
//       $offers = $SystemHelper->item_offers($item_id);

       $itemName = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';
       $this->viewBuilder()->layout('ajax');
       $this->set(compact('itemName', 'item_id', 'unit_price'));
   }
}
