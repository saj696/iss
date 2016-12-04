<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoicedProductsPayments Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $ParentGlobals
 * @property \Cake\ORM\Association\BelongsTo $Invoices
 * @property \Cake\ORM\Association\BelongsTo $InvoicePayments
 *
 * @method \App\Model\Entity\InvoicedProductsPayment get($primaryKey, $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProductsPayment findOrCreate($search, callable $callback = null)
 */
class InvoicedProductsPaymentsTable extends Table
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

        $this->table('invoiced_products_payments');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Invoices', [
            'foreignKey' => 'invoice_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('InvoicePayments', [
            'foreignKey' => 'invoice_payment_id'
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
            ->integer('customer_type')
            ->requirePresence('customer_type', 'create')
            ->notEmpty('customer_type');

        $validator
            ->allowEmpty('invoice_delivery_date');

        $validator
            ->integer('payment_collection_date')
            ->allowEmpty('payment_collection_date');

        $validator
            ->numeric('item_wise_payment_amount')
            ->allowEmpty('item_wise_payment_amount');

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
        $rules->add($rules->existsIn(['invoice_id'], 'Invoices'));
        $rules->add($rules->existsIn(['invoice_payment_id'], 'InvoicePayments'));

        return $rules;
    }
}
