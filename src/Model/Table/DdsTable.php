<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dds Model
 *
 * @method \App\Model\Entity\Dd get($primaryKey, $options = [])
 * @method \App\Model\Entity\Dd newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Dd[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dd|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Dd patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Dd[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dd findOrCreate($search, callable $callback = null)
 */
class DdsTable extends Table
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

        $this->table('dds');
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
            ->integer('date')
            ->allowEmpty('date');

        $validator
            ->allowEmpty('pi_ids');

        $validator
            ->allowEmpty('do_ids');

        $validator
            ->integer('do_ds_serial_number')
            ->allowEmpty('do_ds_serial_number');

        return $validator;
    }
}
