<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductwiseSpecialOffers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Offers
 *
 * @method \App\Model\Entity\ProductwiseSpecialOffer get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductwiseSpecialOffer findOrCreate($search, callable $callback = null)
 */
class ProductwiseSpecialOffersTable extends Table
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

        $this->table('productwise_special_offers');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Products', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Offers', [
            'foreignKey' => 'offer_id',
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
            ->notEmpty('id', 'create');

        return $validator;
    }
}
