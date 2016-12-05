<?php
namespace App\Model\Table;

use App\Model\Entity\CustomerAwardGiving;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CustomerAwardGivings Model
 */
class CustomerAwardGivingsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('customer_award_givings');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('CustomerAwards', [
            'foreignKey' => 'customer_award_id',
            'joinType' => 'INNER'
        ]);
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
            ->add('id', 'valid', ['rule' => 'integer'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->add('award_account_code', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('award_account_code', 'create')
            ->notEmpty('award_account_code');
            

            
        $validator
            ->add('giving_mode', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('giving_mode', 'create')
            ->notEmpty('giving_mode');
            
        $validator
            ->add('award_giving_date', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('award_giving_date', 'create')
            ->notEmpty('award_giving_date');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('created_by', 'create')
            ->notEmpty('created_by');
            
        $validator
            ->add('created_date', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('created_date', 'create')
            ->notEmpty('created_date');
            
        $validator
            ->add('updated_by', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('updated_by');
            
        $validator
            ->add('updated_date', 'valid', ['rule' => 'isInteger'])
         ->allowEmpty('updated_date');

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
        $rules->add($rules->existsIn(['customer_award_id'], 'CustomerAwards'));
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
     //   $rules->add($rules->existsIn(['parent_global_id'], 'ParentGlobals'));
        $rules->add($rules->existsIn(['award_id'], 'Awards'));
        return $rules;
    }
}
