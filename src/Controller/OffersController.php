<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
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

//        App::import('Helper', 'FunctionHelper');
//        $FunctionHelper = new FunctionHelper(new View());
//        $sales_quantity = $FunctionHelper->sales_quantity('01-11-2016', '30-11-2016', '["Krishan G (Hepta)--122- gm"]', 4, 'Kushtia Territory');;

        $offers = $this->Offers->find('all', [
            'conditions' => ['Offers.status !=' => 99]
        ]);

        $general = json_decode($offers->toArray()[0]['general_conditions'], true).'||'.'abc(x)&&bbn(y)';

        $arr = str_split($general);
        $func = [];
        $func_counter = 0;

        foreach($arr as $a){
            if(preg_match('/[a-z\s_(),]/i',$a)){
                @$func[$func_counter] .= $a;
                if($a==')'){
                    $func_counter++;
                }
            }
        }

        echo '<pre>';
        print_r($func);
        echo '</pre>';
        exit;

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
        if ($this->request->is('post')) {

            $input = $this->request->data;
            $data['program_name'] = $input['program_name'];
            $data['offer_payment_mode'] = $input['offer_payment_mode'];
            $data['invoicing'] = $input['invoicing'];
            $data['program_period_start'] = strtotime($input['program_period_start']);
            $data['program_period_end'] = strtotime($input['program_period_end']);
            $data['general_conditions'] = json_encode($input['general_conditions']);
            $data['specific_conditions'] = json_encode($input['specific']);
            $data['created_by'] = $user['id'];
            $data['created_date'] = $time;
            $offer = $this->Offers->patchEntity($offer, $data);
            if ($this->Offers->save($offer)) {
                $this->Flash->success('The offer has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The offer could not be saved. Please, try again.');
            }
        }

        $this->loadModel('OfferFunctions');
        $functions = $this->OfferFunctions->find('all', ['conditions'=>['status'=>1]]);
        $functionArray = [];
        foreach($functions as $function){
            $functionArray[$function->id] = $function->function_name.'('.$function->arguments.')';
        }

        $this->loadModel('Awards');
        $awards = $this->Awards->find('all', ['conditions'=>['status'=>1]]);

        $this->loadModel('AccountHeads');
        $accounts = $this->AccountHeads->find('list', ['conditions'=>['status'=>1, 'parent'=>9]])->orWhere(['parent'=>10]);

        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $items = $SystemHelper->get_item_unit_array();

        $this->loadModel('TaskForces');
        $forces = $this->TaskForces->find('list', ['conditions'=>['status'=>1]]);
        $recipients = [];
        foreach($forces as $k=>$force){
            $recipients[] = $force;
        }
        $recipients[sizeof($forces->toArray())] = 'Customer';

        $this->set(compact('offer', 'functionArray', 'awards', 'accounts', 'items', 'recipients'));
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
