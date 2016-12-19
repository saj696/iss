<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DdosItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Ddos
 * @property \Cake\ORM\Association\BelongsTo $Items
 * @property \Cake\ORM\Association\BelongsTo $Units
 *
 * @method \App\Model\Entity\DdosItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\DdosItem newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DdosItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DdosItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DdosItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DdosItem[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DdosItem findOrCreate($search, callable $callback = null)
 */
class DdosItemsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('ddos_items');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Ddos', [
            'foreignKey' => 'ddo_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
            'joinType' => 'LEFT'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id',
            'joinType' => 'LEFT'
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
//    public function buildRules(RulesChecker $rules)
//    {
//        $rules->add($rules->existsIn(['ddo_id'], 'Ddos'));
//        $rules->add($rules->existsIn(['item_id'], 'Items'));
//        $rules->add($rules->existsIn(['unit_id'], 'Units'));
//
//        return $rules;
//    }
}
