<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Ddos Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $Items
 *
 * @method \App\Model\Entity\Ddo get($primaryKey, $options = [])
 * @method \App\Model\Entity\Ddo newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Ddo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Ddo|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Ddo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Ddo[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Ddo findOrCreate($search, callable $callback = null)
 */
class DdosTable extends Table
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

        $this->table('ddos');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsToMany('Items', [
            'foreignKey' => 'ddo_id',
            'targetForeignKey' => 'item_id',
            'joinTable' => 'ddos_items'
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
            ->integer('date')
            ->allowEmpty('date');

        $validator
            ->integer('do_delivering_warehouse')
            ->allowEmpty('do_delivering_warehouse');

        $validator
            ->integer('do_receiving_warehouse')
            ->allowEmpty('do_receiving_warehouse');

        $validator
            ->integer('do_ds_serial_number')
            ->allowEmpty('do_ds_serial_number');

        $validator
            ->integer('created_by')
            ->allowEmpty('created_by');

        $validator
            ->integer('created_date')
            ->allowEmpty('created_date');

        $validator
            ->integer('updated_by')
            ->allowEmpty('updated_by');

        $validator
            ->integer('updated_date')
            ->allowEmpty('updated_date');

        return $validator;
    }
}
