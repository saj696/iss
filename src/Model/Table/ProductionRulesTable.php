<?php
namespace App\Model\Table;

use App\Model\Entity\ProductionRule;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\Rule\IsUnique;

/**
 * ProductionRules Model
 */
class ProductionRulesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('production_rules');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('InputItems', [
            'className' => 'Items',
            'foreignKey' => 'input_item_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('InputUnits', [
            'className' => 'Units',
            'foreignKey' => 'input_unit_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('OutputItems', [
            'className' => 'Items',
            'foreignKey' => 'output_item_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('OutputUnits', [
            'className' => 'Units',
            'foreignKey' => 'output_unit_id',
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
            ->requirePresence('input_quantity', 'create')
            ->notEmpty('input_quantity');

        $validator
            ->requirePresence('output_quantity', 'create')
            ->notEmpty('output_quantity');

        $validator
            ->add('status', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('status');

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

        $rules->add($rules->existsIn(['input_item_id'], 'InputItems'));
        $rules->add($rules->existsIn(['input_unit_id'], 'InputUnits'));
        $rules->add($rules->existsIn(['output_item_id'], 'OutputItems'));
        $rules->add($rules->existsIn(['output_unit_id'], 'OutputUnits'));

        $rules->add($rules->isUnique(
            ['input_item_id'],
            'Given Input Item  Already Used in Production Rule'
        ));

        $rules->add($rules->isUnique(
            ['output_item_id'],
            'Given  Output Item Already Used in Production Rule'
        ));

        return $rules;
    }
}


