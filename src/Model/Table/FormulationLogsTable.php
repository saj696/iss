<?php
namespace App\Model\Table;

use App\Model\Entity\FormulationLog;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FormulationLogs Model
 */
class FormulationLogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('formulation_logs');
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
            ->add('id', 'valid', ['rule' => 'integer'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->allowEmpty('body');
            
        $validator
            ->add('status', 'valid', ['rule' => 'integer'])
            ->requirePresence('status', 'create')
            ->notEmpty('status');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'integer'])
            ->allowEmpty('created_by');
            
        $validator
            ->add('created_date', 'valid', ['rule' => 'integer'])
            ->allowEmpty('created_date');
            
        $validator
            ->add('updated_by', 'valid', ['rule' => 'integer'])
            ->allowEmpty('updated_by');
            
        $validator
            ->add('updated_date', 'valid', ['rule' => 'integer'])
            ->allowEmpty('updated_date');

        return $validator;
    }
}
