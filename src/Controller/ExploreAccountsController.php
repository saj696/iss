<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;


/**
 * ExploreAccounts Controller
 *
 * @property \App\Model\Table\ExploreAccountsTable $ExploreAccounts
 */
class ExploreAccountsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $exploreAccounts = "exploreAccounts";

        $this->set(compact('exploreAccounts'));
        $this->set('_serialize', ['exploreAccounts']);
    }

    /**
     * View method
     *
     * @param string|null $id Explore Account id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $exploreAccount = $this->ExploreAccounts->get($id, [
            'contain' => []
        ]);

        $this->set('exploreAccount', $exploreAccount);
        $this->set('_serialize', ['exploreAccount']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $exploreAccount = $this->ExploreAccounts->newEntity();
        if ($this->request->is('post')) {
            $exploreAccount = $this->ExploreAccounts->patchEntity($exploreAccount, $this->request->data);
            if ($this->ExploreAccounts->save($exploreAccount)) {
                $this->Flash->success(__('The explore account has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The explore account could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('exploreAccount'));
        $this->set('_serialize', ['exploreAccount']);
    }

    public function getAccountHead()
    {
        //
        if ($this->request->is('post')) {
            $data = $this->request->data;

            $this->viewBuilder()->layout('ajax');
            $this->viewBuilder()->template('get_list');

            //1 is for Owner Type = Customer
            if ($data['owner_type'] == 1) {
                $account_heads = TableRegistry::get('account_heads')->find('all')
                    ->where(['account_selector' => 1])
                    ->orWhere(['account_selector' => 3]);
                // echo "<pre>";print_r($account_heads);die();
                // echo "ok";die();

            } else {
                $account_heads = TableRegistry::get('account_heads')->find('all')
                    ->where(['account_selector' => 1]);
            }
            $this->set(compact('account_heads'));

        }
    }

    public function getAmount()
    {
        if ($this->request->is('post')) {
            $data = $this->request->data;


            if($data['account_head_id']==311000 ||$data['account_head_id']==312000 ||$data['account_head_id']==313000 ||$data['account_head_id']==314000){
                $account_heads = TableRegistry::get('customer_awards')->find('all')
                    ->where(['award_account_code' => $data['account_head_id']]);

                $account_heads->select(['total' => $account_heads->func()->sum('amount')]);
                $account_heads=    $account_heads->first();
              //  echo "<pre>";print_r($account_heads->total);die();
                $this->response->body($account_heads->total);
                return $this->response;

            }
        //    echo "<pre>";print_r($data);die();
        }
    }

    public function getUptoAmount()
    {

    }
}
