<?php
namespace App\Model\Table;

use App\Model\Entity\Po;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Pos Model
 */
class PosTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('pos');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Invoices', [
            'foreignKey' => 'po_id'
        ]);
        $this->hasMany('PoProducts', [
            'foreignKey' => 'po_id'
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
            ->add('customer_level_no', 'valid', ['rule' => 'numeric'])
            ->requirePresence('customer_level_no', 'create')
            ->notEmpty('customer_level_no');
            
        $validator
            ->add('customer_type', 'valid', ['rule' => 'numeric'])
            ->requirePresence('customer_type', 'create')
            ->notEmpty('customer_type');
            
        $validator
            ->add('po_date', 'valid', ['rule' => 'numeric'])
            ->requirePresence('po_date', 'create')
            ->notEmpty('po_date');
            
        $validator
            ->add('invoice_type', 'valid', ['rule' => 'numeric'])
            ->requirePresence('invoice_type', 'create')
            ->notEmpty('invoice_type');
            
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
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
        return $rules;
    }
}
