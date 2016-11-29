<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OfferFunctions Model
 *
 * @method \App\Model\Entity\OfferFunction get($primaryKey, $options = [])
 * @method \App\Model\Entity\OfferFunction newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OfferFunction[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OfferFunction|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OfferFunction patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OfferFunction[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OfferFunction findOrCreate($search, callable $callback = null)
 */
class OfferFunctionsTable extends Table
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

        $this->table('offer_functions');
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
            ->requirePresence('function_name', 'create')
            ->notEmpty('function_name');

        $validator
            ->requirePresence('arguments', 'create')
            ->notEmpty('arguments');

        $validator
            ->integer('status')
            ->allowEmpty('status');

        return $validator;
    }
}
