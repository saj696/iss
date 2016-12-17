<?php
namespace App\Model\Table;

use App\Model\Entity\DoEvent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DoEvents Model
 */
class DoEventsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('do_events');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Senders', [
            'className' => 'Users',
            'foreignKey' => 'sender_id',
            'joinType' => 'INNER'
        ]);
//        $this->belongsTo('Recipients', [
//            'foreignKey' => 'recipient_id',
//            'joinType' => 'INNER'
//        ]);
        $this->belongsTo('DoObjects', [
            'foreignKey' => 'do_object_id',
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
            ->add('id', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->add('events_tepe', 'valid', ['rule' => 'isInteger'])
            ->requirePresence('events_tepe', 'create')
            ->notEmpty('events_tepe');
            
        $validator
            ->add('action_status', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('action_status');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('created_by');
            
        $validator
            ->add('created_date', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('created_date');
            
        $validator
            ->add('updated_by', 'valid', ['rule' => 'isInteger'])
            ->allowEmpty('updated_by');
            
        $validator
            ->add('updated_date', 'valid', ['rule' => 'isInteger'])
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
////        $rules->add($rules->existsIn(['sender_id'], 'Senders'));
////        $rules->add($rules->existsIn(['recipient_id'], 'Recipients'));
//      //  $rules->add($rules->existsIn(['do_object_id'], 'DoObjects'));
//        return $rules;
//    }
}
