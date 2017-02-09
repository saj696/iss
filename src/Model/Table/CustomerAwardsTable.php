<?php
namespace App\Model\Table;

use App\Model\Entity\CustomerAward;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CustomerAwards Model
 */
class CustomerAwardsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('customer_awards');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
//        $this->belongsTo('ParentGlobals', [
//            'foreignKey' => 'parent_global_id',
//            'joinType' => 'INNER'
//        ]);
        $this->belongsTo('Awards', [
            'foreignKey' => 'award_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('CustomerOffers', [
            'foreignKey' => 'customer_offer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CustomerAwardGivings', [
            'foreignKey' => 'customer_award_id'
        ]);
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
            ->add('id', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->add('award_account_code', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('award_account_code', 'create')
            ->notEmpty('award_account_code');
            
        $validator
            ->add('amount', 'valid', ['rule' => 'numeric'])
            ->requirePresence('amount', 'create')
            ->notEmpty('amount');
            
        $validator
            ->add('offer_period_start', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('offer_period_start');
            
        $validator
            ->add('offer_period_end', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('offer_period_end');
            
        $validator
            ->add('action_status', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('action_status');
            
        $validator
            ->add('action_taken_at', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('action_taken_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
        return $rules;
    }
}
