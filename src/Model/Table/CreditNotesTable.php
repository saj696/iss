<?php
namespace App\Model\Table;

use App\Model\Entity\CreditNote;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditNotes Model
 */
class CreditNotesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('credit_notes');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasOne('CreditNoteEvents', [
            'foreignKey' => 'credit_note_id'
        ]);
        $this->hasMany('CreditNoteItems', [
            'foreignKey' => 'credit_note_id'
        ]);
        $this->belongsTo('CreditNoteCreators', [
            'className' => 'Users',
            'foreignKey' => 'created_by'
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
            ->add('date', 'valid', ['rule' => 'numeric'])
            ->requirePresence('date', 'create')
            ->notEmpty('date');

        $validator
            ->add('total_after_demurrage', 'valid', ['rule' => 'numeric'])
            ->requirePresence('total_after_demurrage', 'create')
            ->notEmpty('total_after_demurrage');

        $validator
            ->add('demurrage_percentage', 'valid', ['rule' => 'numeric'])
            ->requirePresence('demurrage_percentage', 'create')
            ->notEmpty('demurrage_percentage');

        $validator
            ->add('approval_status', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('approval_status');

        $validator
            ->add('adjustment_status', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('adjustment_status');
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
