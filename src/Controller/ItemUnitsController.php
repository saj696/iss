<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use App\View\Helper\SystemHelper;
use Cake\View\View;


/**
 * ItemUnits Controller
 *
 * @property \App\Model\Table\ItemUnitsTable $ItemUnits
 */
class ItemUnitsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'ItemUnits.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $itemUnits = $this->ItemUnits->find('all', [
            'conditions' => ['ItemUnits.status !=' => 99],
            'contain' => ['Units', 'Items']
        ]);
        $this->set('itemUnits', $this->paginate($itemUnits));
        $this->set('_serialize', ['itemUnits']);
    }

    /**
     * View method
     *
     * @param string|null $id Item Unit id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $itemUnit = $this->ItemUnits->get($id, [
            'contain' => ['Items', 'Units']
        ]);
        $this->set('itemUnit', $itemUnit);
        $this->set('_serialize', ['itemUnit']);
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
        $itemUnit = $this->ItemUnits->newEntity();

        //$conn = ConnectionManager::get('default');
        if ($this->request->is('post')) {

            $data = $this->request->data;
            foreach ($data['ItemUnits'] as $items):
                $itemUnit = $this->ItemUnits->newEntity();
                $item_unit_data['item_id'] = $items['item_id'];

                $get_item_name =
                    $this->ItemUnits->Items->find('all', ['conditions' => ['id' =>
                        $item_unit_data['item_id']]])->hydrate(false)->select('name')->first();

                $item_unit_data['item_name'] = $get_item_name['name'];
                $item_unit_data['manufacture_unit_id'] = $items['manufacture_unit_id'];
                $get_unit_info =
                    $this->ItemUnits->Units->find('all', ['conditions' => ['id' =>
                        $item_unit_data['manufacture_unit_id']]])->first();

                $item_unit_data['unit_name'] = $get_unit_info['unit_name'];
                $item_unit_data['unit_size'] = $get_unit_info['unit_size'];
                $item_unit_data['unit_display_name'] = $get_unit_info['unit_display_name'];
                $item_unit_data['converted_quantity'] = $get_unit_info['converted_quantity'];
                $item_unit_data['code'] = $this->generateCode($items['item_id'], $items['manufacture_unit_id']);
                $item_unit_data['created_by'] = $user['id'];
                $item_unit_data['created_date'] = $time;
                $itemUnit = $this->ItemUnits->patchEntity($itemUnit, $item_unit_data);
                if ($this->ItemUnits->save($itemUnit)) {
                    $this->Flash->success('Item and Unit mapping has been saved.');
                } else {
                    $errors = $itemUnit->errors();
                    foreach ($errors as $error):
                        foreach ($error as $er):
                            $this->Flash->error($er);
                        endforeach;
                    endforeach;

                }
            endforeach;

            return $this->redirect(['action' => 'index']);

        }

        $units = $this->ItemUnits->Units->find('list', [
            'keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);
        $items = $this->ItemUnits->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('itemUnit', 'items', 'units'));
        $this->set('_serialize', ['itemUnit']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Item Unit id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $itemUnit = $this->ItemUnits->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['update_by'] = $user['id'];
            $data['update_date'] = $time;
            $get_item_name =
                $this->ItemUnits->Items->find('all', ['conditions' => ['id' =>
                    $data['item_id']]])->hydrate(false)->select('name')->first();

            $data['item_name'] = $get_item_name['name'];
            $get_unit_info =
                $this->ItemUnits->Units->find('all', ['conditions' => ['id' =>
                    $data['manufacture_unit_id']]])->first();
            $data['code'] = $this->generateCode($data['item_id'], $data['manufacture_unit_id']);
            $data['unit_name'] = $get_unit_info['unit_name'];
            $data['unit_size'] = $get_unit_info['unit_size'];
            $data['unit_display_name'] = $get_unit_info['unit_display_name'];
            $data['converted_quantity'] = $get_unit_info['converted_quantity'];

            $itemUnit = $this->ItemUnits->patchEntity($itemUnit, $data);
            if ($this->ItemUnits->save($itemUnit)) {
                $this->Flash->success('The item unit has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The item unit could not be saved. Please, try again.');
            }
        }
        $item_unit_level = Configure::read('unit_levels');
        $units = $this->ItemUnits->Units->find('list', [
            'keyField' => 'id',
            'valueField' => 'unit_display_name',
            'conditions' => ['status' => 1]]);
        $items = $this->ItemUnits->Items->find('list', ['conditions' => ['status' => 1]]);
        $this->set(compact('itemUnit', 'items', 'units'));
        $this->set('_serialize', ['itemUnit']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Item Unit id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $itemUnit = $this->ItemUnits->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $itemUnit = $this->ItemUnits->patchEntity($itemUnit, $data);
        if ($this->ItemUnits->save($itemUnit)) {
            $this->Flash->success('The item unit has been deleted.');
        } else {
            $this->Flash->error('The item unit could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function generateCode($item_id, $manufacture_unit_id)
    {
        $this->autoRender = false;
        $itemPadding = Configure::read('item_generation_padding');
        $category = $this->ItemUnits->Items->find('all', ['conditions' => ['id' => $item_id]])->first();
        $itemPrefix = TableRegistry::get('categories')->find('all', ['conditions' => ['id' => $category['category_id']], 'fields' => ['prefix']])->first()->toArray();
        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $itemCode = $SystemHelper->generate_code($itemPrefix['prefix'], 'item', $itemPadding, $item_id, $manufacture_unit_id);
        return $itemCode;
    }

}
