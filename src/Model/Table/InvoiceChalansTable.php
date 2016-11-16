<?php
namespace App\Model\Table;

use App\Model\Entity\InvoiceChalan;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoiceChalans Model
 */
class InvoiceChalansTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('invoice_chalans');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->hasMany('InvoiceChalanDetails', [
            'foreignKey' => 'invoice_chalan_id'
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
            ->requirePresence('reference_invoices', 'create')
            ->notEmpty('reference_invoices');
            
        $validator
            ->add('chalan_no', 'valid', ['rule' => 'numeric'])
            ->requirePresence('chalan_no', 'create')
            ->notEmpty('chalan_no');
            
        $validator
            ->add('chalan_status', 'valid', ['rule' => 'numeric'])
            ->requirePresence('chalan_status', 'create')
            ->notEmpty('chalan_status');

        return $validator;
    }
}
