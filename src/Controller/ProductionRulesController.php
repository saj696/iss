<?php
namespace App\Controller;

use App\Controller\AppController;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * ProductionRules Controller
 *
 * @property \App\Model\Table\ProductionRulesTable $ProductionRules
 */
class ProductionRulesController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'ProductionRules.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $productionRules = $this->ProductionRules->find('all', [
            'conditions' => ['ProductionRules.status !=' => 99],
            'contain' => ['InputItems', 'InputUnits', 'OutputItems', 'OutputUnits']
        ]);
        // debug($productionRules->toArray());
        // die;
        $this->set('productionRules', $this->paginate($productionRules));
        $this->set('_serialize', ['productionRules']);
    }

    /**
     * View method
     *
     * @param string|null $id Production Rule id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $productionRule = $this->ProductionRules->get($id, [
            'contain' => ['InputItems', 'InputUnits', 'OutputItems', 'OutputUnits']
        ]);
        $this->set('productionRule', $productionRule);
        $this->set('_serialize', ['productionRule']);
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
        $productionRule = "";
        if ($this->request->is('post')) {

            $data = $this->request->data;
            try {
                foreach ($data['ProductionRules'] as $data['ProductionRules']):
                    $data['ProductionRules']['created_by'] = $user['id'];
                    $data['ProductionRules']['created_date'] = $time;
                    $productionRule = $this->ProductionRules->newEntity();
                    $productionRule = $this->ProductionRules->patchEntity($productionRule, $data['ProductionRules']);
                    if ($this->ProductionRules->save($productionRule)) {
                        $this->Flash->success('The production rule has been saved.');
                    } else {
                        $errors = $productionRule->errors();
                        foreach ($errors as $error):
                            foreach ($error as $er):
                                $this->Flash->error($er);
                            endforeach;
                        endforeach;
                    }
                endforeach;
                return $this->redirect(['action' => 'index']);
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->Flash->error('The production rule could not be saved. Please, try again.');
                die;
            }

        }
        $this->loadModel('Items');
        $this->loadModel('Units');
        $inputItems = $this->Items->find('list', ['conditions' => ['status' => 1]]);
        //$inputUnits = $this->Units->find('list', ['conditions' => ['status' => 1, 'unit_size' => 0]]);
        $outputItems = $inputItems;
        $outputUnits = '';
        $this->set(compact('productionRule', 'inputItems', 'inputUnits', 'outputItems', 'outputUnits'));
        $this->set('_serialize', ['productionRule']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Production Rule id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $productionRule = $this->ProductionRules->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $productionRule = $this->ProductionRules->patchEntity($productionRule, $data);
            if ($this->ProductionRules->save($productionRule)) {
                $this->Flash->success('The production rule has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $errors = $productionRule->errors();
                foreach ($errors as $error):
                    foreach ($error as $er):
                        $this->Flash->error($er);
                    endforeach;
                endforeach;
                return $this->redirect(['action' => 'index']);
            }
        }
        $this->loadModel('Items');
        $this->loadModel('Units');
        $inputItems = $this->Items->find('list', ['conditions' => ['status' => 1]]);
        $inputUnits = $this->Units->find('list', ['conditions' => ['status' => 1, 'unit_size' => 0]]);
        $outputItems = $inputItems;
        $this->set(compact('productionRule', 'inputItems', 'outputItems'));
        $this->set('_serialize', ['productionRule']);
    }


    public function getBulkUnit($item_id)
    {
        $this->autoRender = false;
        $this->loadModel('Items');
        $this->loadModel('ItemUnits');
        $result = $this->ItemUnits->find('all', ['conditions' => ['item_id' => $item_id, 'unit_size' => 0]])->first();
        $response = [];
        $response[$result['manufacture_unit_id']] = $result['unit_display_name'];
        $this->response->body(json_encode($response));
    }

    /**
     * Delete method
     *
     * @param string|null $id Production Rule id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $productionRule = $this->ProductionRules->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $productionRule = $this->ProductionRules->patchEntity($productionRule, $data);
        if ($this->ProductionRules->save($productionRule)) {
            $this->Flash->success('The production rule has been deleted.');
        } else {
            $this->Flash->error('The production rule could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
