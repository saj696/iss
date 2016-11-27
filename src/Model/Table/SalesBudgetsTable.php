<?php
namespace App\Model\Table;

use App\Model\Entity\SalesBudget;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SalesBudgets Model
 */
class SalesBudgetsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('sales_budgets');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('SalesBudgetConfigurations', [
            'foreignKey' => 'sales_budget_configuration_id'
        ]);
        $this->belongsTo('AdministrativeUnits', [
            'foreignKey' => 'administrative_unit_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id'
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
            ->add('budget_period_start', 'valid', ['rule' => 'numeric'])
            ->requirePresence('budget_period_start', 'create')
            ->notEmpty('budget_period_start');
            
        $validator
            ->add('budget_period_end', 'valid', ['rule' => 'numeric'])
            ->requirePresence('budget_period_end', 'create')
            ->notEmpty('budget_period_end');
            
        $validator
            ->add('level_no', 'valid', ['rule' => 'numeric'])
            ->requirePresence('level_no', 'create')
            ->notEmpty('level_no');
            
        $validator
            ->add('product_scope', 'valid', ['rule' => 'numeric'])
            ->requirePresence('product_scope', 'create')
            ->notEmpty('product_scope');
            
        $validator
            ->add('sales_measure_unit', 'valid', ['rule' => 'numeric'])
            ->requirePresence('sales_measure_unit', 'create')
            ->notEmpty('sales_measure_unit');
            
        $validator
            ->add('sales_amount', 'valid', ['rule' => 'numeric'])
            ->requirePresence('sales_amount', 'create')
            ->notEmpty('sales_amount');

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
        $rules->add($rules->existsIn(['sales_budget_configuration_id'], 'SalesBudgetConfigurations'));
        return $rules;
    }
}
