<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

/**
 * Units Controller
 *
 * @property \App\Model\Table\UnitsTable $Units
 */
class UnitsController extends AppController
{

    public $paginate = [
        'limit' => 15,
        'order' => [
            'Units.id' => 'desc'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $units = $this->Units->find('all', [
            'conditions' => ['Units.status !=' => 99],
            'contain' => []
        ]);
        $this->set('units', $this->paginate($units));
        $this->set('_serialize', ['units']);
    }

    /**
     * View method
     *
     * @param string|null $id Unit id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Auth->user();
        $unit = $this->Units->get($id, [
            'contain' => []
        ]);
        $this->set('unit', $unit);
        $this->set('_serialize', ['unit']);
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
        $unit = $this->Units->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $data['status'] = 1;
            if ($data['unit_size'] == 0) {
                $data['unit_size'] = '';
            }
            if ($data['unit_level'] == '1') {
                $data['converted_quantity'] = $data['unit_size'];
                $unit_type = Configure::read('pack_size_units');
                $data['unit_display_name'] = $data['unit_name'] . '-' . $data['unit_size'] . ' ' . __($unit_type[$data['unit_type']]);
            } else {
                $prev_level = $this->Units->find('all', ['conditions' => ['id' => $data['constituent_unit_id']]])->hydrate(false)->first();
                $data['converted_quantity'] = $data['unit_size'] * $prev_level['converted_quantity'];
                $data['unit_display_name'] = $data['unit_name'] . '-' . $data['unit_size'] . 'X(' . $prev_level['unit_display_name'] . ')';
            }
            $unit = $this->Units->patchEntity($unit, $data);
            if ($this->Units->save($unit)) {
                $this->Flash->success('The unit has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The unit could not be saved. Please, try again.');
            }
        }
        $this->set(compact('unit'));
        $this->set('_serialize', ['unit']);
    }


    /**
     * Edit method
     *
     * @param string|null $id Unit id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Auth->user();
        $time = time();
        $unit = $this->Units->get($id, [
            'contain' => []
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['updated_by'] = $user['id'];
            $data['updated_date'] = $time;
            $unit_type = Configure::read('pack_size_units');
            if ($data['unit_size'] == 0) {
                $data['unit_size'] = '';
            }
            $unit_type = Configure::read('pack_size_units');

            if ($data['unit_level'] == '1') {
                $data['converted_quantity'] = $data['unit_size'];
                $data['unit_display_name'] = $data['unit_name'] . '-' . $data['unit_size'] . ' ' . __($unit_type[$unit['unit_type']]);
            } else {
                $prev_level = $this->Units->find('all', ['conditions' => ['id' => $data['constituent_unit_id']]])->hydrate(false)->first();
                $data['converted_quantity'] = $data['unit_size'] * $prev_level['converted_quantity'];
                $data['unit_display_name'] = $data['unit_name'] . '-' . $data['unit_size'] . 'X(' . $prev_level['unit_display_name'] . ')';
            }
            $unit = $this->Units->patchEntity($unit, $data);
            if ($this->Units->save($unit)) {
                $this->Flash->success('The unit has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The unit could not be saved. Please, try again.');
            }
        }
        $this->set(compact('unit', 'constituentUnits'));
        $this->set('_serialize', ['unit']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Unit id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */

    public function ajax()
    {
        $data = $this->request->data;
        $unit_level = $data['level'];

        $constituent_unit = TableRegistry::get('units')->find('all', ['conditions' => ['unit_level' => $unit_level - 1, 'unit_size >' => 0, 'status' => 1], 'fields' => ['id', 'unit_name', 'unit_level', 'unit_size']])->hydrate(false)->toArray();
        $unit_level = Configure::read("unit_levels");
        $dropArray = [];
        foreach ($constituent_unit as $unit):
            $dropArray[$unit['id']] = __($unit_level[$unit['unit_level']]) . '__' . $unit['unit_name'] . '__' . $unit['unit_size'];
        endforeach;
        $this->viewBuilder()->layout('ajax');
        $this->set(compact('dropArray'));
    }

    public function delete($id = null)
    {

        $unit = $this->Units->get($id);

        $user = $this->Auth->user();
        $data = $this->request->data;
        $data['updated_by'] = $user['id'];
        $data['updated_date'] = time();
        $data['status'] = 99;
        $unit = $this->Units->patchEntity($unit, $data);
        if ($this->Units->save($unit)) {
            $this->Flash->success('The unit has been deleted.');
        } else {
            $this->Flash->error('The unit could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
