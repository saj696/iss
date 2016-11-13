<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PoProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Pos
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\PoProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\PoProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\PoProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PoProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PoProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PoProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\PoProduct findOrCreate($search, callable $callback = null)
 */
class PoProductsTable extends Table
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

        $this->table('po_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Pos', [
            'foreignKey' => 'po_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
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
            ->numeric('product_quantity')
            ->requirePresence('product_quantity', 'create')
            ->notEmpty('product_quantity');

        $validator
            ->numeric('bonus_quantity')
            ->allowEmpty('bonus_quantity');

        $validator
            ->integer('instant_discount')
            ->allowEmpty('instant_discount');

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
        $rules->add($rules->existsIn(['po_id'], 'Pos'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
