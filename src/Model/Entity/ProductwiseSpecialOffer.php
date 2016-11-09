<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductwiseSpecialOffer Entity
 *
 * @property int $id
 * @property int $product_id
 * @property int $offer_id
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Offer $offer
 */
class ProductwiseSpecialOffer extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
