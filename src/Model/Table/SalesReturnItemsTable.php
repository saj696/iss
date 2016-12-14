<?php
namespace App\Model\Table;

use App\Model\Entity\SalesReturnItem;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SalesReturnItems Model
 */
class SalesReturnItemsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('sales_return_items');
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
        $this->belongsTo('SalesReturns', [
            'foreignKey' => 'sales_return_id',
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
            ->add('expire_date', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('expire_date');

        $validator
            ->add('unit_price', 'valid', ['rule' => 'numeric'])
            ->requirePresence('unit_price', 'create')
            ->notEmpty('unit_price');

        $validator
            ->add('quantity', 'valid', ['rule' => 'numeric'])
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->add('net_total', 'valid', ['rule' => 'numeric'])
            ->requirePresence('net_total', 'create')
            ->notEmpty('net_total');

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
        $rules->add($rules->existsIn(['sales_return_id'], 'SalesReturns'));
        return $rules;
    }
}
