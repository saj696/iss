<?php
namespace App\Model\Table;

use App\Model\Entity\InvoiceCycleConfiguration;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoiceCycleConfigurations Model
 */
class InvoiceCycleConfigurationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('invoice_cycle_configurations');
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
            ->add('invoice_approved_at', 'valid', ['rule' => 'numeric'])
            ->requirePresence('invoice_approved_at', 'create')
            ->notEmpty('invoice_approved_at');
            
        $validator
            ->add('approving_user_group', 'valid', ['rule' => 'numeric'])
            ->requirePresence('approving_user_group', 'create')
            ->notEmpty('approving_user_group');
            
        $validator
            ->add('allow_delivery_before_approval', 'valid', ['rule' => 'numeric'])
            ->requirePresence('allow_delivery_before_approval', 'create')
            ->notEmpty('allow_delivery_before_approval');

        return $validator;
    }
}
