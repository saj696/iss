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
            ->add('id', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->allowEmpty('body');
            
        $validator
            ->add('status', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('status', 'create')
            ->notEmpty('status');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('created_by');
            
        $validator
            ->add('created_date', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('created_date');
            
        $validator
            ->add('updated_by', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('updated_by');
            
        $validator
            ->add('updated_date', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('updated_date');

        return $validator;
    }
}
