<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DtoEventItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $DtoEvents
 * @property \Cake\ORM\Association\BelongsTo $Items
 * @property \Cake\ORM\Association\BelongsTo $Units
 *
 * @method \App\Model\Entity\DtoEventItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\DtoEventItem newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DtoEventItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DtoEventItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DtoEventItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DtoEventItem[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DtoEventItem findOrCreate($search, callable $callback = null)
 */
class DtoEventItemsTable extends Table
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

        $this->table('dto_event_items');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('DtoEvents', [
            'foreignKey' => 'dto_event_id',
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

        $validator
            ->numeric('approved_quantity')
            ->allowEmpty('approved_quantity');

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
        $rules->add($rules->existsIn(['dto_event_id'], 'DtoEvents'));
        $rules->add($rules->existsIn(['item_id'], 'Items'));
        $rules->add($rules->existsIn(['unit_id'], 'Units'));

        return $rules;
    }
}
