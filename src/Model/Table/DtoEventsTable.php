<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DtoEvents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Senders
 * @property \Cake\ORM\Association\BelongsTo $Recipients
 * @property \Cake\ORM\Association\HasMany $DtoEventItems
 *
 * @method \App\Model\Entity\DtoEvent get($primaryKey, $options = [])
 * @method \App\Model\Entity\DtoEvent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DtoEvent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DtoEvent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DtoEvent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DtoEvent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DtoEvent findOrCreate($search, callable $callback = null)
 */
class DtoEventsTable extends Table
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

        $this->table('dto_events');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'sender_id',
            'joinType' => 'INNER'
        ]);
//        $this->belongsTo('Recipients', [
//            'foreignKey' => 'recipient_id',
//            'joinType' => 'INNER'
//        ]);
//        $this->hasMany('DtoEventItems', [
//            'foreignKey' => 'dto_event_id'
//        ]);
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
            ->integer('action_status')
            ->allowEmpty('action_status');

        $validator
            ->integer('created_by')
            ->allowEmpty('created_by');

        $validator
            ->integer('created_date')
            ->allowEmpty('created_date');

        $validator
            ->integer('updated_by')
            ->allowEmpty('updated_by');

        $validator
            ->integer('updated_date')
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
//    public function buildRules(RulesChecker $rules)
//    {
//        $rules->add($rules->existsIn(['sender_id'], 'Senders'));
//        $rules->add($rules->existsIn(['recipient_id'], 'Recipients'));
//
//        return $rules;
//    }
}
