<?php
namespace App\Model\Table;

use App\Model\Entity\Price;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Prices Model
 */
class PricesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('prices');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Units', [
            'foreignKey' => 'manufacture_unit_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ItemUnits', [
            'foreignKey' => 'item_unit_id',
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
            ->requirePresence('item_name', 'create')
            ->notEmpty('item_name');

        $validator
            ->requirePresence('unit_name', 'create')
            ->notEmpty('unit_name');

        $validator
            ->requirePresence('unit_display_name', 'create')
            ->notEmpty('unit_display_name');

        $validator
            ->add('converted_quantity', 'valid', ['rule' => 'numeric'])
            ->requirePresence('converted_quantity', 'create')
            ->notEmpty('converted_quantity');

        $validator
            ->add('cash_sales_price', 'valid', ['rule' => 'numeric'])
            ->requirePresence('cash_sales_price', 'create')
            ->notEmpty('cash_sales_price');

        $validator
            ->add('credit_sales_price', 'valid', ['rule' => 'numeric'])
            ->requirePresence('credit_sales_price', 'create')
            ->notEmpty('credit_sales_price');

        $validator
            ->add('retail_price', 'valid', ['rule' => 'numeric'])
            ->requirePresence('retail_price', 'create')
            ->notEmpty('retail_price');

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
        $rules->add($rules->existsIn(['item_id'], 'Items'));
        $rules->add($rules->existsIn(['manufacture_unit_id'], 'Units'));
        return $rules;
    }
}
