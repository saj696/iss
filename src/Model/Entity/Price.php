<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Price Entity.
 *
 * @property int $id
 * @property int $item_id
 * @property string $item_name
 * @property int $manufacture_unit_id
 * @property string $unit_name
 * @property string $unit_display_name
 * @property float $converted_quantity
 * @property float $cash_sales_price
 * @property float $credit_sales_price
 * @property float $retail_price
 * @property int $status
 */
class Price extends Entity
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
        'id' => false,
    ];
}
