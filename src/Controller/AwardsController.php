<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Awards Controller
 *
 * @property \App\Model\Table\AwardsTable $Awards
 */
class AwardsController extends AppController
{

	public $paginate = [
        'limit' => 15,
        'order' => [
            'Awards.id' => 'desc'
        ]
    ];

/**
* Index method
*
* @return void
*/
public function index()
{
			$awards = $this->Awards->find('all', [
	'conditions' =>['Awards.status !=' => 99]
	]);
		$this->set('awards', $this->paginate($awards) );
	$this->set('_serialize', ['awards']);
	}

    /**
     * View method
     *
     * @param string|null $id Award id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user=$this->Auth->user();
        $award = $this->Awards->get($id, [
            'contain' => []
        ]);
        $this->set('award', $award);
        $this->set('_serialize', ['award']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user=$this->Auth->user();
        $time=time();
        $award = $this->Awards->newEntity();
        if ($this->request->is('post'))
        {

            $data=$this->request->data;
            $data['create_by']=$user['id'];
            $data['create_date']=$time;
            $award = $this->Awards->patchEntity($award, $data);
            if ($this->Awards->save($award))
            {
                $this->Flash->success('The award has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The award could not be saved. Please, try again.');
            }
        }

        $this->loadModel('account_heads');
        $account_heads = $this->account_heads->find('list', ['keyField' => 'code', 'keyValue' => 'name'])
            ->where(['parent' => 9])
            ->toArray();
        $this->set(compact('award','account_heads'));
        $this->set('_serialize', ['award']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Award id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user=$this->Auth->user();
        $time=time();
        $award = $this->Awards->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $data=$this->request->data;

            $data['update_by']=$user['id'];
            $data['update_date']=$time;
            $award = $this->Awards->patchEntity($award, $data);
            if ($this->Awards->save($award))
            {
                $this->Flash->success('The award has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The award could not be saved. Please, try again.');
            }
        }
        $this->loadModel('account_heads');
        $account_heads = $this->account_heads->find('list', ['keyField' => 'code', 'keyValue' => 'name'])
            ->where(['parent' => 9])
            ->toArray();
        $this->set(compact('award','account_heads'));
        $this->set('_serialize', ['award']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Award id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $award = $this->Awards->get($id);

        $user=$this->Auth->user();
        $data=$this->request->data;
        $data['updated_by']=$user['id'];
        $data['updated_date']=time();
        $data['status']=99;
        $award = $this->Awards->patchEntity($award, $data);
        if ($this->Awards->save($award))
        {
            $this->Flash->success('The award has been deleted.');
        }
        else
        {
            $this->Flash->error('The award could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
