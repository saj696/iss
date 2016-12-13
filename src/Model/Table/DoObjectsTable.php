<?php
namespace App\Model\Table;

use App\Model\Entity\DoObject;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DoObjects Model
 */
class DoObjectsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('do_objects');
        $this->displayField('id');
        $this->primaryKey('id');
//        $this->belongsTo('Targets', [
//            'foreignKey' => 'target_id'
//        ]);
        $this->hasMany('DoObjectItems', [
            'foreignKey' => 'do_object_id'
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
            ->add('id', 'valid', ['rule' => 'integer'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->add('serial_no', 'valid', ['rule' => 'integer'])
            ->requirePresence('serial_no', 'create')
            ->notEmpty('serial_no');
            
        $validator
            ->add('date', 'valid', ['rule' => 'integer'])
            ->requirePresence('date', 'create')
            ->notEmpty('date');
            
        $validator
            ->add('object_type', 'valid', ['rule' => 'integer'])
            ->requirePresence('object_type', 'create')
            ->notEmpty('object_type');
            
        $validator
            ->add('target_type', 'valid', ['rule' => 'integer'])
            ->allowEmpty('target_type');
            
        $validator
            ->add('action_status', 'valid', ['rule' => 'integer'])
            ->requirePresence('action_status', 'create')
            ->notEmpty('action_status');
            
        $validator
            ->add('created_by', 'valid', ['rule' => 'integer'])
            ->requirePresence('created_by', 'create')
            ->notEmpty('created_by');
            
        $validator
            ->add('created_date', 'valid', ['rule' => 'integer'])
            ->requirePresence('created_date', 'create')
            ->notEmpty('created_date');
            
        $validator
            ->add('updated_by', 'valid', ['rule' => 'integer'])
            ->allowEmpty('updated_by');
            
        $validator
            ->add('updated_date', 'valid', ['rule' => 'integer'])
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
//     //   $rules->add($rules->existsIn(['target_id'], 'Targets'));
//        return $rules;
//    }
}
