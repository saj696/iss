<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SalesBudgetConfiguration Entity.
 *
 * @property int $id
 * @property int $level_no
 * @property int $sales_measure
 * @property int $product_scope
 * @property int $sales_measure_unit
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class SalesBudgetConfiguration extends Entity
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
