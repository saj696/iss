<?php
namespace App\Model\Table;

use App\Model\Entity\CreditNoteItem;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditNoteItems Model
 */
class CreditNoteItemsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('credit_note_items');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Invoices', [
            'foreignKey' => 'invoice_id'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'manufacture_unit_id'
        ]);
        $this->belongsTo('CreditNoteCreators', [
            'className' => 'Users',
            'foreignKey' => 'created_by'
        ]);
        $this->belongsTo('CreditNotes', [
            'className' => 'CreditNotes',
            'foreignKey' => 'credit_note_id'
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
            ->add('quantity', 'valid', ['rule' => 'numeric'])
            ->notEmpty('quantity');

        $validator
            ->add('net_total', 'valid', ['rule' => 'numeric'])
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
        $rules->add($rules->existsIn(['invoice_id'], 'Invoices'));
        $rules->add($rules->existsIn(['item_id'], 'Items'));
        $rules->add($rules->existsIn(['manufacture_unit_id'], 'Units'));
        return $rules;
    }
}
