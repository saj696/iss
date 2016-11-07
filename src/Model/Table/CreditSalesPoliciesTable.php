<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditSalesPolicies Model
 *
 * @method \App\Model\Entity\CreditSalesPolicy get($primaryKey, $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CreditSalesPolicy findOrCreate($search, callable $callback = null)
 */
class CreditSalesPoliciesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('credit_sales_policies');
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('policy_start_date')
            ->requirePresence('policy_start_date', 'create')
            ->notEmpty('policy_start_date');

        $validator
            ->integer('policy_expected_end_date')
            ->requirePresence('policy_expected_end_date', 'create')
            ->notEmpty('policy_expected_end_date');

        $validator
            ->requirePresence('policy_detail', 'create')
            ->notEmpty('policy_detail');

        return $validator;
    }
}
