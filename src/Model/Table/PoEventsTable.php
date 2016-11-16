<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PoEvents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Pos
 * @property \Cake\ORM\Association\BelongsTo $Recipients
 *
 * @method \App\Model\Entity\PoEvent get($primaryKey, $options = [])
 * @method \App\Model\Entity\PoEvent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\PoEvent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PoEvent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PoEvent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PoEvent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\PoEvent findOrCreate($search, callable $callback = null)
 */
class PoEventsTable extends Table
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

        $this->table('po_events');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Pos', [
            'foreignKey' => 'reference_id',
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
        $rules->add($rules->existsIn(['po_id'], 'Pos'));
        return $rules;
    }
}
