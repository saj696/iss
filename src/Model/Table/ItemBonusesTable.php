<?php
namespace App\Model\Table;

use App\Model\Entity\ItemBonus;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ItemBonuses Model
 */
class ItemBonusesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('item_bonuses');
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
            ->add('order_quantity_from', 'valid', ['rule' => 'numeric'])
            ->requirePresence('order_quantity_from', 'create')
            ->notEmpty('order_quantity_from');

        $validator
            ->add('order_quantity_to', 'valid', ['rule' => 'numeric'])
            ->requirePresence('order_quantity_to', 'create')
            ->notEmpty('order_quantity_to');
            
        $validator
            ->add('bonus_quantity', 'valid', ['rule' => 'numeric'])
            ->requirePresence('bonus_quantity', 'create')
            ->notEmpty('bonus_quantity');

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
