<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\StackHelper;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * Offers Controller
 *
 * @property \App\Model\Table\OffersTable $Offers
 */
class OffersController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Offers.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $offers = $this->Offers->find('all', [
            'conditions' => ['Offers.status !=' => 99]
        ]);

//        $offers = $this->Offers->find('all', [
//            'conditions' => ['Offers.status !=' => 99]
//        ])->toArray();

//        App::import('Helper', 'FunctionHelper');
//        $FunctionHelper = new FunctionHelper(new View());
//
//        echo '<pre>';
//        print_r(json_decode($offers[0]['conditions'], true));
//        echo '</pre>';
//        exit;

        $this->set('offers', $this->paginate($offers));
        $this->set('_serialize', ['offers']);
    }

    /**
     * View method
     *
     * @param string|null $id Offer id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $offer = $this->Offers->get($id, [
            'contain' => []
        ]);
        $this->set('offer', $offer);
        $this->set('_serialize', ['offer']);
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
        $offer = $this->Offers->newEntity();
        App::import('Helper', 'FunctionHelper');
        $FunctionHelper = new FunctionHelper(new View());

        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($offer, $FunctionHelper, $user, $time, &$saveStatus)
                {
                    $input = $this->request->data;
                    $data['program_name'] = $input['program_name'];
                    $data['offer_payment_mode'] = $input['offer_payment_mode'];
                    $data['invoicing'] = $input['invoicing'];
                    $data['program_period_start'] = strtotime($input['program_period_start']);
                    $data['program_period_end'] = strtotime($input['program_period_end']);
                    $data['conditions'] = json_encode($input['condition']);

                    $conditionPostfix = [];
                    foreach($input['condition'] as $k=>$condition):
                        $conditionPostfix[$k]['general'] = $FunctionHelper->postfix_converter($condition['general_conditions'].'$');
                        $conditionPostfix[$k]['specific'] = $FunctionHelper->postfix_converter($condition['specific'].'$');
                        $conditionPostfix[$k]['amount'] = $FunctionHelper->postfix_converter($condition['amount'].'$');
                    endforeach;

                    $data['condition_postfix'] = json_encode($conditionPostfix);

                    $data['created_by'] = $user['id'];
                    $data['created_date'] = $time;
                    $offer = $this->Offers->patchEntity($offer, $data);
                    $result = $this->Offers->save($offer);

                    // Offer Items Insertion
                    $this->loadModel('OfferItems');
                    $this->loadModel('ItemUnits');
                    foreach($input['offer_items'] as $item){
                        $offerItem = $this->OfferItems->newEntity();
                        $itemUnitInfo = $this->ItemUnits->get($item);
                        $offerItemData['offer_id'] = $result['id'];
                        $offerItemData['item_id'] = $itemUnitInfo['item_id'];
                        $offerItemData['manufacture_unit_id'] = $itemUnitInfo['manufacture_unit_id'];
                        $offerItemData['item_unit_id'] = $item;
                        $offerItemData['offer_payment_mode'] = $input['offer_payment_mode'];
                        $offerItemData['invoicing'] = $input['invoicing'];
                        $offerItemData['program_period_start'] = strtotime($input['program_period_start']);
                        $offerItemData['program_period_end'] = strtotime($input['program_period_end']);
                        $offerItemData['created_by'] = $user['id'];
                        $offerItemData['created_date'] = $time;
                        $offerItem = $this->OfferItems->patchEntity($offerItem, $offerItemData);
                        $this->OfferItems->save($offerItem);
                    }
                });

                $this->Flash->success('Offer Creation Successful');
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('Offer Creation Failed');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('OfferFunctions');
        $functions = $this->OfferFunctions->find('all', ['conditions'=>['status'=>1]]);
        $functionArray = [];
        foreach($functions as $function){
            $functionArray[$function->id] = $function->function_name.'['.$function->arguments.']';
        }

        $this->loadModel('Awards');
        $awards = $this->Awards->find('all', ['conditions'=>['status'=>1]]);

        $this->loadModel('AccountHeads');
        $accounts = $this->AccountHeads->find('list', ['conditions'=>['status'=>1, 'parent'=>300000]]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $items = $SystemHelper->get_item_unit_array();

        $this->loadModel('SalesForces');
        $forces = $this->SalesForces->find('list', ['conditions'=>['status'=>1]]);
        $recipients = [];
        foreach($forces as $k=>$force){
            $recipients[] = $force;
        }
        $recipients[sizeof($forces->toArray())] = 'Customer';

        $this->loadModel('AdministrativeLevels');
        $levels = $this->AdministrativeLevels->find('list', ['keyField' => 'level_no', 'keyValue' => 'level_name'])->toArray();
        $levels[5] = 'Customer';
        $this->set(compact('offer', 'functionArray', 'awards', 'accounts', 'items', 'recipients', 'levels'));
        $this->set('_serialize', ['offer']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Offer id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $offer = $this->Offers->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $offer = $this->Offers->patchEntity($offer, $data);
            if ($this->Offers->save($offer)) {
                $this->Flash->success('The offer has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The offer could not be saved. Please, try again.');
            }
        }
        $this->set(compact('offer'));
        $this->set('_serialize', ['offer']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Offer id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $offer = $this->Offers->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $offer = $this->Offers->patchEntity($offer, $data);
        if ($this->Offers->save($offer)) {
            $this->Flash->success('The offer has been deleted.');
        } else {
            $this->Flash->error('The offer could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    function multiExplode ($delimiters,$string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }
}
