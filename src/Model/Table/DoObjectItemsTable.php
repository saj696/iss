<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DoObjectItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $DoObjects
 * @property \Cake\ORM\Association\BelongsTo $Items
 * @property \Cake\ORM\Association\BelongsTo $Units
 *
 * @method \App\Model\Entity\DoObjectItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\DoObjectItem newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DoObjectItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DoObjectItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DoObjectItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DoObjectItem[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DoObjectItem findOrCreate($search, callable $callback = null)
 */
class DoObjectItemsTable extends Table
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

        $this->table('do_object_items');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('DoObjects', [
            'foreignKey' => 'do_object_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'unit_id',
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->numeric('quantity')
            ->allowEmpty('quantity');

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
        $rules->add($rules->existsIn(['do_object_id'], 'DoObjects'));
        $rules->add($rules->existsIn(['item_id'], 'Items'));
        $rules->add($rules->existsIn(['unit_id'], 'Units'));

        return $rules;
    }
}
