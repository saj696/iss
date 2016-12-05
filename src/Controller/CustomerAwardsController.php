<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * CustomerAwards Controller
 *
 * @property \App\Model\Table\CustomerAwardsTable $CustomerAwards
 */
class CustomerAwardsController extends AppController
{

	public $paginate = [
        'limit' => 15,
        'order' => [
            'CustomerAwards.id' => 'desc'
        ]
    ];

/**
* Index method
*
* @return void
*/
public function index()
{
			$customerAwards = $this->CustomerAwards->find('all', [
	//'conditions' =>['CustomerAwards.status !=' => 99],
	'contain' => ['Customers', 'Awards', 'CustomerOffers']
	]);
		$this->set('customerAwards', $this->paginate($customerAwards) );
	$this->set('_serialize', ['customerAwards']);
	}

    /**
     * View method
     *
     * @param string|null $id Customer Award id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user=$this->Auth->user();
        $customerAward = $this->CustomerAwards->get($id, [
            'contain' => ['Customers','Awards', 'CustomerOffers']
        ]);
        $this->set('customerAward', $customerAward);
        $this->set('_serialize', ['customerAward']);
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
        $customerAward = $this->CustomerAwards->newEntity();
        if ($this->request->is('post'))
        {

            $data=$this->request->data;
            $data['create_by']=$user['id'];
            $data['create_date']=$time;
            $customerAward = $this->CustomerAwards->patchEntity($customerAward, $data);
            if ($this->CustomerAwards->save($customerAward))
            {
                $this->Flash->success('The customer award has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The customer award could not be saved. Please, try again.');
            }
        }
        $customers = $this->CustomerAwards->Customers->find('list', ['conditions'=>['status'=>1]]);
       // $parentGlobals = $this->CustomerAwards->ParentGlobals->find('list', ['conditions'=>['status'=>1]]);
        $awards = $this->CustomerAwards->Awards->find('list', ['conditions'=>['status'=>1]]);
        $customerOffers = $this->CustomerAwards->CustomerOffers->find('list', ['conditions'=>['status'=>1]]);
        $this->set(compact('customerAward', 'customers', 'parentGlobals', 'awards', 'customerOffers'));
        $this->set('_serialize', ['customerAward']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Award id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user=$this->Auth->user();
        $time=time();
        $customerAward = $this->CustomerAwards->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put']))
        {
            $data=$this->request->data;
            $data['update_by']=$user['id'];
            $data['update_date']=$time;
            $customerAward = $this->CustomerAwards->patchEntity($customerAward, $data);
            if ($this->CustomerAwards->save($customerAward))
            {
                $this->Flash->success('The customer award has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Flash->error('The customer award could not be saved. Please, try again.');
            }
        }
        $customers = $this->CustomerAwards->Customers->find('list', ['conditions'=>['status'=>1]]);
      //  $parentGlobals = $this->CustomerAwards->ParentGlobals->find('list', ['conditions'=>['status'=>1]]);
        $awards = $this->CustomerAwards->Awards->find('list', ['conditions'=>['status'=>1]]);
        $customerOffers = $this->CustomerAwards->CustomerOffers->find('list', ['conditions'=>['status'=>1]]);
        $this->set(compact('customerAward', 'customers', 'parentGlobals', 'awards', 'customerOffers'));
        $this->set('_serialize', ['customerAward']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Award id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $customerAward = $this->CustomerAwards->get($id);

        $user=$this->Auth->user();
        $data=$this->request->data;
        $data['updated_by']=$user['id'];
        $data['updated_date']=time();
        $data['status']=99;
        $customerAward = $this->CustomerAwards->patchEntity($customerAward, $data);
        if ($this->CustomerAwards->save($customerAward))
        {
            $this->Flash->success('The customer award has been deleted.');
        }
        else
        {
            $this->Flash->error('The customer award could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
