<?php
namespace App\Model\Table;

use App\Model\Entity\SalesBudgetConfiguration;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SalesBudgetConfigurations Model
 */
class SalesBudgetConfigurationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('sales_budget_configurations');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->hasMany('SalesBudgets', [
            'foreignKey' => 'sales_budget_configuration_id'
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
            ->add('level_no', 'valid', ['rule' => 'numeric'])
            ->requirePresence('level_no', 'create')
            ->notEmpty('level_no');
            
        $validator
            ->add('sales_measure', 'valid', ['rule' => 'numeric'])
            ->requirePresence('sales_measure', 'create')
            ->notEmpty('sales_measure');
            
        $validator
            ->add('product_scope', 'valid', ['rule' => 'numeric'])
            ->requirePresence('product_scope', 'create')
            ->notEmpty('product_scope');

        return $validator;
    }
}
