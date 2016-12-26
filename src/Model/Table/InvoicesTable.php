<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Invoices Model
 *
 * @property \Cake\ORM\Association\BelongsTo $CustomerUnitGlobals
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $Pos
 * @property \Cake\ORM\Association\BelongsTo $DepotUnitGlobals
 * @property \Cake\ORM\Association\BelongsTo $Depots
 * @property \Cake\ORM\Association\HasMany $InvoicedProducts
 *
 * @method \App\Model\Entity\Invoice get($primaryKey, $options = [])
 * @method \App\Model\Entity\Invoice newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Invoice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Invoice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Invoice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Invoice[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Invoice findOrCreate($search, callable $callback = null)
 */
class InvoicesTable extends Table
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

        $this->table('invoices');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Pos', [
            'foreignKey' => 'po_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Depots', [
            'foreignKey' => 'depot_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('InvoicedProducts', [
            'foreignKey' => 'invoice_id'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id'
        ]);
        $this->belongsTo('Units', [
            'foreignKey' => 'manufacture_unit_id'
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
            ->integer('delivery_date')
            ->requirePresence('delivery_date', 'create')
            ->notEmpty('delivery_date');

        $validator
            ->integer('invoice_type')
            ->requirePresence('invoice_type', 'create')
            ->notEmpty('invoice_type');

        $validator
            ->integer('depot_level_no')
            ->requirePresence('depot_level_no', 'create')
            ->notEmpty('depot_level_no');

        $validator
            ->numeric('net_total')
            ->requirePresence('net_total', 'create')
            ->notEmpty('net_total');

        $validator
            ->numeric('due')
            ->allowEmpty('due');

        $validator
            ->integer('invoice_date')
            ->requirePresence('invoice_date', 'create')
            ->notEmpty('invoice_date');

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
        $rules->add($rules->existsIn(['po_id'], 'Pos'));
        $rules->add($rules->existsIn(['depot_id'], 'Depots'));
        return $rules;
    }
}
