<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PaymentCommissions Model
 *
 * @method \App\Model\Entity\PaymentCommission get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentCommission newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\PaymentCommission[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentCommission|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentCommission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentCommission[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentCommission findOrCreate($search, callable $callback = null)
 */
class PaymentCommissionsTable extends Table
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

        $this->table('payment_commissions');
        $this->displayField('id');
        $this->primaryKey('id');
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
            ->integer('payment_age_from')
            ->requirePresence('payment_age_from', 'create')
            ->notEmpty('payment_age_from');

        $validator
            ->integer('payment_age_to')
            ->requirePresence('payment_age_to', 'create')
            ->notEmpty('payment_age_to');

        $validator
            ->numeric('commission')
            ->requirePresence('commission', 'create')
            ->notEmpty('commission');

        $validator
            ->integer('policy_period_start')
            ->allowEmpty('policy_period_start');

        $validator
            ->integer('policy_period_end')
            ->allowEmpty('policy_period_end');

        return $validator;
    }
}
