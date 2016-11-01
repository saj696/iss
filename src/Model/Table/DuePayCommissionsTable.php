<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DuePayCommissions Model
 *
 * @method \App\Model\Entity\DuePayCommission get($primaryKey, $options = [])
 * @method \App\Model\Entity\DuePayCommission newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DuePayCommission[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DuePayCommission|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DuePayCommission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DuePayCommission[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DuePayCommission findOrCreate($search, callable $callback = null)
 */
class DuePayCommissionsTable extends Table
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

        $this->table('due_pay_commissions');
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
            ->numeric('due_paid_percentage')
            ->requirePresence('due_paid_percentage', 'create')
            ->notEmpty('due_paid_percentage');

        $validator
            ->numeric('commission')
            ->requirePresence('commission', 'create')
            ->notEmpty('commission');

        $validator
            ->integer('start_date')
            ->allowEmpty('start_date');

        $validator
            ->integer('end_date')
            ->allowEmpty('end_date');

        return $validator;
    }
}
