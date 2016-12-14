<?php
namespace App\Model\Table;

use App\Model\Entity\Unit;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Units Model
 */
class UnitsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('units');
        $this->displayField('unit_display_name');
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
            ->add('unit_level', 'valid', ['rule' => 'numeric'])
            ->requirePresence('unit_level', 'create')
            ->notEmpty('unit_level');

        $validator
            ->requirePresence('unit_name', 'create')
            ->notEmpty('unit_name');

        $validator
            ->requirePresence('unit_display_name', 'create')
            ->notEmpty('unit_display_name');

        $validator
            ->add('unit_size', 'valid', ['rule' => 'numeric'])
            //->requirePresence('unit_size', 'create')
            ->allowEmpty('unit_size');

        $validator
            ->requirePresence('unit_type', 'create')
            ->notEmpty('unit_type');

        $validator
            ->add('converted_quantity', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('converted_quantity');

        $validator
            ->add('created_by', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('created_by');

        $validator
            ->add('created_date', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('created_date');

        $validator
            ->add('updated_by', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('updated_by');

        $validator
            ->add('updated_date', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('updated_date');

        $validator
            ->add('status', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('status');

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
        return $rules;
    }
}
