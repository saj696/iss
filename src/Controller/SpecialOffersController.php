<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;

/**
 * SpecialOffers Controller
 *
 * @property \App\Model\Table\SpecialOffersTable $SpecialOffers
 */
class SpecialOffersController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'SpecialOffers.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $specialOffers = $this->SpecialOffers->find('all', [
            'conditions' => ['SpecialOffers.status !=' => 99]
        ]);
        $this->set('specialOffers', $this->paginate($specialOffers));
        $this->set('_serialize', ['specialOffers']);
    }

    /**
     * View method
     *
     * @param string|null $id Special Offer id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $specialOffer = $this->SpecialOffers->get($id, [
            'contain' => []
        ]);
        $this->set('specialOffer', $specialOffer);
        $this->set('_serialize', ['specialOffer']);
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
        $specialOffer = $this->SpecialOffers->newEntity();
        if ($this->request->is('post')) {
            try {
                $saveStatus = 0;
                $conn = ConnectionManager::get('default');
                $conn->transactional(function () use ($time, $user, &$saveStatus)
                {
                    $specialOffer = $this->SpecialOffers->newEntity();
                    $input = $this->request->data;
                    $data['program_name'] = $input['program_name'];
                    $data['program_period_start'] = strtotime($input['program_period_start']);
                    $data['program_period_end'] = strtotime($input['program_period_end']);
                    $data['invoice_type'] = $input['invoice_type'];
                    $offerDetail['invoicing'] = $input['invoicing'];
                    $offerDetail['product_bonus_in_cash_sales'] = $input['product_bonus_in_cash_sales'];
                    $offerDetail['credit_note'] = $input['credit_note'];
                    $offerDetail['previous_additional_circular'] = $input['previous_additional_circular'];
                    $offerDetail['detailOffer'] = json_encode($input['main']);
                    $data['offer_detail'] = $offerDetail;
                    $data['created_by'] = $user['id'];
                    $data['created_date'] = $time;
                    $specialOffer = $this->SpecialOffers->patchEntity($specialOffer, $data);
                    $result = $this->SpecialOffers->save($specialOffer);

                    // ProductWise Special Offer Table
                    $this->loadModel('ProductwiseSpecialOffers');
                    foreach($input['main'] as $info){
                        foreach($info['items'] as $item){
                            if($item>0){
                                $productWise = $this->ProductwiseSpecialOffers->newEntity();
                                $insert['item_id'] = $item;
                                $insert['offer_id'] = $result['id'];
                                $insert['created_by'] = $user['id'];
                                $insert['created_date'] = $time;
                                $productWise = $this->ProductwiseSpecialOffers->patchEntity($productWise, $insert);
                                $this->ProductwiseSpecialOffers->save($productWise);
                            }
                        }
                    }
                    $this->Flash->success('The special offer has been saved.');
                    return $this->redirect(['action' => 'index']);
                });
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
                exit;
                $this->Flash->error('The special offer has not been saved. Please try again!');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->loadModel('Items');
        $items = $this->Items->find('all', ['conditions' => ['status' => 1]]);
        $itemsArray = [];
        foreach($items as $item) {
            $itemsArray[$item['id']] = $item['name'].' - '.$item['pack_size'].' '.Configure::read('pack_size_units')[$item['unit']].' ('.$item['code'].')';
        }

        $this->loadModel('Trips');
        $trips = $this->Trips->find('list', ['conditions'=>['status !='=>99]]);

        $this->set(compact('specialOffer', 'itemsArray', 'trips'));
        $this->set('_serialize', ['specialOffer']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Special Offer id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $specialOffer = $this->SpecialOffers->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $specialOffer = $this->SpecialOffers->patchEntity($specialOffer, $data);
            if ($this->SpecialOffers->save($specialOffer)) {
                $this->Flash->success('The special offer has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The special offer could not be saved. Please, try again.');
            }
        }
        $this->set(compact('specialOffer'));
        $this->set('_serialize', ['specialOffer']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Special Offer id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $specialOffer = $this->SpecialOffers->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $specialOffer = $this->SpecialOffers->patchEntity($specialOffer, $data);
        if ($this->SpecialOffers->save($specialOffer)) {
            $this->Flash->success('The special offer has been deleted.');
        } else {
            $this->Flash->error('The special offer could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
