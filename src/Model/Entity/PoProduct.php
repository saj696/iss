<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PoProduct Entity
 *
 * @property int $id
 * @property int $po_id
 * @property int $product_id
 * @property float $product_quantity
 * @property float $bonus_quantity
 * @property int $instant_discount
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Po $po
 * @property \App\Model\Entity\Product $product
 */
class PoProduct extends Entity
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
