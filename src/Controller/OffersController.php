<?php
namespace App\Controller;

use App\Controller\AppController;
use App\View\Helper\FunctionHelper;
use App\View\Helper\StackHelper;
use App\View\Helper\SystemHelper;
use Cake\Core\App;
use Cake\Core\Configure;
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
//        $amount = 15000;
//        $comArray = [
//            0=>['start'=>0, 'end'=>5000, 'commission'=>5],
//            1=>['start'=>5001, 'end'=>10000, 'commission'=>7],
//            2=>['start'=>10001, 'end'=>15000, 'commission'=>10],
//        ];
//
//        App::import('Helper', 'SystemHelper');
//        $systemHelper = new SystemHelper(new View());
//        $slab = $systemHelper->slab_computer($amount, $comArray);
//        echo $slab;
//        exit;

//        $context = [
//            'current_item_id'=>[0=>10, 1=>12, 2=>13],
//            'current_item_unit_id'=>[0=>11, 1=>12, 2=>13],
//            'current_item_quantity'=>[0=>100, 1=>200, 2=>300],
//            'invoice_date'=>'10-11-2016',
//            'payment_date'=>'10-11-2016',
//        ];
//

        $array = ['Item 1', 'Unit 1', 'Item 2', null, 'Item 3', 'Unit 3'];
        App::import('Helper', 'SystemHelper');
        $SystemHelper = new SystemHelper(new View());
        $refined = $SystemHelper->item_array_generator($array);

        App::import('Helper', 'FunctionHelper');
        $functionHelper = new FunctionHelper(new View());
        $slab = $functionHelper->sales_quantity('01-01-2016', '01-01-2017', $refined, 10, 12);

        echo '<pre>';
        print_r($slab);
        echo '</pre>';
        exit;


        echo '<pre>';
        print_r($refined);
        echo '</pre>';
        exit;


        $offers = $this->Offers->find('all', [
            'conditions' => ['Offers.status !=' => 99]
        ]);
//
//        $general = json_decode($offers->toArray()[0]['general_conditions'], true).'$';
//
//        $ca = str_split($general); // condition array
//        $fn = []; // function name
//        $fa = []; // function array
//        $cn=[];
//
//        $stack = [];
//        $stack[0] = '$';
//        $indexOfStackTop = 0;
//
//        $precedence = [];
//        $precedence['%'] = 3;
//        $precedence['*'] = 3;
//        $precedence['+'] = 2;
//        $precedence['-'] = 2;
//        $precedence['&'] = 1;
//        $precedence['|'] = 1;
//        $precedence['>'] = 1;
//        $precedence['<'] = 1;
//        $precedence['='] = 1;
//
//        $postfix = [];
//        $postfixCurrentIndex=0;
//        $functionSerial = 0;
//        $myArray = [];
//        $operators = ['+', '-', '*', '%', '&', '|', '>', '<'];
//
//        for($i=0; $i<sizeof($ca); $i++){
//            if($ca[$i]=='(') {
//                $indexOfStackTop++;
//                $stack[$indexOfStackTop] = $ca[$i];
//            } elseif(preg_match('/[a-z\s_]/i',$ca[$i])){
//                do{
//                    @$fn[$functionSerial] .= $ca[$i];
//                    $i++;
//                }while($ca[$i] != '[');
//                $i++;
//
//                do{
//                    if($ca[$i] == ']'){
//                        break;
//                    }
//                    @$fa[$functionSerial] .= $ca[$i];
//
//                    $i++;
//                }while(1);
//
//                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['function'];
//                $postfix[$postfixCurrentIndex]['name'] = $fn[$functionSerial];
//                $postfix[$postfixCurrentIndex]['arg'] = $fa[$functionSerial];
//                $postfixCurrentIndex++;
//                $functionSerial++;
//            } elseif(preg_match('/[0-9]/i',$ca[$i])){
//                unset($cn[0]);
//                do{
//                    @$cn[0] .= intval($ca[$i]);
//                    $i++;
//                }while(preg_match('/[0-9]/i',$ca[$i]));
//
//                $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['number'];
//                $postfix[$postfixCurrentIndex]['number'] = $cn[0];
//                $postfixCurrentIndex++;
//                $i--;
//            } elseif(in_array($ca[$i], $operators)){
//                if($stack[$indexOfStackTop]=='$'){
//                    $indexOfStackTop++;
//                    $stack[$indexOfStackTop] = $ca[$i];
//                }elseif(in_array($stack[$indexOfStackTop], $operators)){
//                    do{
//                        if($precedence[$ca[$i]]>$precedence[$stack[$indexOfStackTop]]){
//                            $indexOfStackTop++;
//                            $stack[$indexOfStackTop] = $ca[$i];
//                            break;
//                        }else{
//                            $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
//                            $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];
//                            $postfixCurrentIndex++;
//                            $indexOfStackTop--;
//                            if(!in_array($stack[$indexOfStackTop], $operators)) {
//                                $indexOfStackTop++;
//                                $stack[$indexOfStackTop] = $ca[$i];
//                                break;
//                            }
//                        }
//                    }while(1);
//
//                }elseif($stack[$indexOfStackTop] == '('){
//                    $indexOfStackTop++;
//                    $stack[$indexOfStackTop] = $ca[$i];
//                }
//            } elseif($ca[$i]==')'){
//                do{
//                    $stop=$stack[$indexOfStackTop];
//                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
//                    $postfix[$postfixCurrentIndex]['operator'] = $stop;
//                    $postfixCurrentIndex++;
//                    $indexOfStackTop--;
//                    $stop=$stack[$indexOfStackTop];
//                }while($stop != '(');
//
//                $indexOfStackTop--;
//            } elseif($ca[$i]=='$'){
//                while($indexOfStackTop>0){
//                    $postfix[$postfixCurrentIndex]['type'] = Configure::read('postfix_elements_types')['operator'];
//                    $postfix[$postfixCurrentIndex]['operator'] = $stack[$indexOfStackTop];
//                    $indexOfStackTop--;
//                    $postfixCurrentIndex++;
//                }
//            }
//        }
//
//        echo '<pre>';
//        print_r($postfix);
//        echo '</pre>';
//        exit;
//
//
//
//        $argExploded = explode(',', $fa[0]);
//        $argArray = [];
//        foreach($argExploded as $arg){
//            $argArray[] = trim($arg);
//        }
//
//        if($fn[0]=='sales_quantity'){
//            $itemArray = [];
//            foreach($argArray as $k=>$arg){
//                if($k==0){
//                    $period_start = $arg;
//                }elseif($k==1){
//                    $period_end = $arg;
//                }elseif($k==sizeof($argArray)-1){
//                    $unit = $arg;
//                }elseif($k==sizeof($argArray)-2){
//                    $level = $arg;
//                }else{
//                    $itemArray[] = str_replace("'", '', $arg);
//                }
//            }
//        }
//
//        App::import('Helper', 'FunctionHelper');
//        $FunctionHelper = new FunctionHelper(new View());
//        $max_due_invoice_age = $FunctionHelper->$fn[0]($period_start, $period_end, $itemArray, $level, $unit);
//
//        echo $max_due_invoice_age;
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
            $functionArray[$function->id] = $function->function_name.'['.$function->arguments.']';
        }

        $this->loadModel('Awards');
        $awards = $this->Awards->find('all', ['conditions'=>['status'=>1]]);

        $this->loadModel('AccountHeads');
        $accounts = $this->AccountHeads->find('list', ['conditions'=>['status'=>1, 'parent'=>9]])->orWhere(['parent'=>10]);

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
