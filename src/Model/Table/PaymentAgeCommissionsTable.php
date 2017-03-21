<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PaymentAgeCommissions Model
 *
 * @method \App\Model\Entity\PaymentAgeCommission get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentAgeCommission findOrCreate($search, callable $callback = null)
 */
class PaymentAgeCommissionsTable extends Table
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

        $this->table('payment_age_commissions');
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
            ->integer('age_start')
            ->requirePresence('age_start', 'create')
            ->notEmpty('age_start');

        $validator
            ->integer('age_end')
            ->requirePresence('age_end', 'create')
            ->notEmpty('age_end');

        $validator
            ->numeric('commission')
            ->requirePresence('commission', 'create')
            ->notEmpty('commission');

        return $validator;
    }
}
