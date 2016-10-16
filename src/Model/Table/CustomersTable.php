<?php
namespace App\Model\Table;

use App\Model\Entity\Customer;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Customers Model
 */
class CustomersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('customers');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->belongsTo('AdministrativeUnits', [
            'foreignKey' => 'administrative_unit_id',
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
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code');
            
        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');
            
        $validator
            ->allowEmpty('address');
            
        $validator
            ->allowEmpty('proprietor');
            
        $validator
            ->allowEmpty('contact_person');
            
        $validator
            ->add('business_type', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('business_type');
            
        $validator
            ->allowEmpty('mobile');
            
        $validator
            ->allowEmpty('telephone');
            
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->allowEmpty('email');
            
        $validator
            ->add('credit_limit', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('credit_limit');
            
        $validator
            ->add('credit_invoice_days', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('credit_invoice_days');

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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['administrative_unit_id'], 'AdministrativeUnits'));
        return $rules;
    }
}
