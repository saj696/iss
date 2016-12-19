<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OfferItems Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Offers
 * @property \Cake\ORM\Association\BelongsTo $Items
 * @property \Cake\ORM\Association\BelongsTo $ManufactureUnits
 * @property \Cake\ORM\Association\BelongsTo $ItemUnits
 *
 * @method \App\Model\Entity\OfferItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\OfferItem newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OfferItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OfferItem|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OfferItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OfferItem[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OfferItem findOrCreate($search, callable $callback = null)
 */
class OfferItemsTable extends Table
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

        $this->table('offer_items');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Offers', [
            'foreignKey' => 'offer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Items', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ItemUnits', [
            'foreignKey' => 'item_unit_id',
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
            ->integer('program_period_start')
            ->requirePresence('program_period_start', 'create')
            ->notEmpty('program_period_start');

        $validator
            ->integer('program_period_end')
            ->requirePresence('program_period_end', 'create')
            ->notEmpty('program_period_end');

        $validator
            ->integer('offer_payment_mode')
            ->requirePresence('offer_payment_mode', 'create')
            ->notEmpty('offer_payment_mode');

        $validator
            ->integer('invoicing')
            ->requirePresence('invoicing', 'create')
            ->notEmpty('invoicing');
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
        $rules->add($rules->existsIn(['item_unit_id'], 'ItemUnits'));
        return $rules;
    }
}
