<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoiceChalanDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $InvoiceChalans
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\InvoiceChalanDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceChalanDetail findOrCreate($search, callable $callback = null)
 */
class InvoiceChalanDetailsTable extends Table
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

        $this->table('invoice_chalan_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('InvoiceChalans', [
            'foreignKey' => 'invoice_chalan_id',
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
            ->numeric('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

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
        $rules->add($rules->existsIn(['invoice_chalan_id'], 'InvoiceChalans'));
        return $rules;
    }
}
