<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\SystemHelper;

/**
 * Prices Controller
 *
 * @property \App\Model\Table\PricesTable $Prices
 */
class PricesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Prices.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $prices = $this->Prices->find('all', [
            'conditions' => ['Prices.status !=' => 99],
            'contain' => ['Items', 'Units']
        ]);
        $this->set('prices', $this->paginate($prices));
        $this->set('_serialize', ['prices']);
    }

    /**
     * View method
     *
     * @param string|null $id Price id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $price = $this->Prices->get($id, [
            'contain' => ['Items', 'Units']
        ]);
        $this->set('price', $price);
        $this->set('_serialize', ['price']);
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
        $price = "";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data['create_by'] = $user['id'];
            $data['create_date'] = $time;
            $data['status'] = 1;
            foreach ($data['ItemUnits'] as $item_units):
                $price = $this->Prices->newEntity();

                $data_of_item_units = $this->Prices->ItemUnits->find('all', ['conditions' => ['ItemUnits.id' => $item_units['item_unit_id']]])->first();

                $data['cash_sales_price'] = $item_units['cash_sales_price'];
                $data['item_unit_id'] = $item_units['item_unit_id'];
                $data['credit_sales_price'] = $item_units['credit_sales_price'];
                $data['retail_price'] = $item_units['retail_price'];
                $data['unit_name'] = $data_of_item_units['unit_name'];
                $data['manufacture_unit_id'] = $data_of_item_units['manufacture_unit_id'];
                $data['item_name'] = $data_of_item_units['item_name'];
                $data['item_id'] = $data_of_item_units['item_id'];
                $data['converted_quantity'] = $data_of_item_units['converted_quantity'];
                $data['unit_display_name'] = $data_of_item_units['unit_display_name'];

                $price = $this->Prices->patchEntity($price, $data);

                if ($this->Prices->save($price)) {
                    $this->Flash->success('The price has been saved.');

                } else {
                    $this->Flash->error('The price could not be saved. Please, try again.');
                }

            endforeach;
            return $this->redirect(['action' => 'index']);
        }

        $item_unit = SystemHelper::get_item_unit_array();
        $this->set(compact('price', 'item_unit'));
        $this->set('_serialize', ['price']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Price id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $price = $this->Prices->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $data_of_item_units = $this->Prices->ItemUnits->find('all', ['conditions' => ['ItemUnits.id' => $data['item_unit_id']]])->first();
            $data['item_unit_id'] = $data['item_unit_id'];
            $data['unit_name'] = $data_of_item_units['unit_name'];
            $data['manufacture_unit_id'] = $data_of_item_units['manufacture_unit_id'];
            $data['item_name'] = $data_of_item_units['item_name'];
            $data['item_id'] = $data_of_item_units['item_id'];
            $data['converted_quantity'] = $data_of_item_units['converted_quantity'];
            $data['unit_display_name'] = $data_of_item_units['unit_display_name'];

            $price = $this->Prices->patchEntity($price, $data);
            if ($this->Prices->save($price)) {
                $this->Flash->success('The price has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The price could not be saved. Please, try again.');
            }
        }
        $item_unit = SystemHelper::get_item_unit_array();

        $this->set(compact('price', 'item_unit'));
        $this->set('_serialize', ['price']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Price id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $price = $this->Prices->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $price = $this->Prices->patchEntity($price, $data);
        if ($this->Prices->save($price)) {
            $this->Flash->success('The price has been deleted.');
        } else {
            $this->Flash->error('The price could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
