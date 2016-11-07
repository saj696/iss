<?php
namespace App\Model\Table;

use App\Model\Entity\SpecialOffer;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SpecialOffers Model
 */
class SpecialOffersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('special_offers');
        $this->displayField('id');
        $this->primaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->requirePresence('program_name', 'create')
            ->notEmpty('program_name');
            
        $validator
            ->add('program_period_start', 'valid', ['rule' => 'numeric'])
            ->requirePresence('program_period_start', 'create')
            ->notEmpty('program_period_start');
            
        $validator
            ->add('program_period_end', 'valid', ['rule' => 'numeric'])
            ->requirePresence('program_period_end', 'create')
            ->notEmpty('program_period_end');
            
        $validator
            ->add('invoice_type', 'valid', ['rule' => 'numeric'])
            ->requirePresence('invoice_type', 'create')
            ->notEmpty('invoice_type');
            
        $validator
            ->requirePresence('offer_detail', 'create')
            ->notEmpty('offer_detail');

        return $validator;
    }
}
