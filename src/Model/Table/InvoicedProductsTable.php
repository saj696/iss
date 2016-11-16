<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoicedProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Invoices
 * @property \Cake\ORM\Association\BelongsTo $CustomerUnitGlobals
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $DepotUnitGlobals
 * @property \Cake\ORM\Association\BelongsTo $Depots
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\InvoicedProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\InvoicedProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\InvoicedProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\InvoicedProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\InvoicedProduct findOrCreate($search, callable $callback = null)
 */
class InvoicedProductsTable extends Table
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

        $this->table('invoiced_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Invoices', [
            'foreignKey' => 'invoice_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Depots', [
            'foreignKey' => 'depot_id',
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
            ->integer('customer_level_no')
            ->requirePresence('customer_level_no', 'create')
            ->notEmpty('customer_level_no');

        $validator
            ->integer('customer_type')
            ->requirePresence('customer_type', 'create')
            ->notEmpty('customer_type');

        $validator
            ->integer('invoice_date')
            ->requirePresence('invoice_date', 'create')
            ->notEmpty('invoice_date');

        $validator
            ->integer('delivery_date')
            ->requirePresence('delivery_date', 'create')
            ->notEmpty('delivery_date');

        $validator
            ->integer('depot_level_no')
            ->requirePresence('depot_level_no', 'create')
            ->notEmpty('depot_level_no');

        $validator
            ->numeric('product_quantity')
            ->requirePresence('product_quantity', 'create')
            ->notEmpty('product_quantity');

        $validator
            ->numeric('bonus_quantity')
            ->allowEmpty('bonus_quantity');

        $validator
            ->numeric('instant_discount')
            ->allowEmpty('instant_discount');

        $validator
            ->numeric('net_total')
            ->requirePresence('net_total', 'create')
            ->notEmpty('net_total');

        $validator
            ->numeric('due')
            ->allowEmpty('due');

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
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
        $rules->add($rules->existsIn(['depot_id'], 'Depots'));
        return $rules;
    }
}
