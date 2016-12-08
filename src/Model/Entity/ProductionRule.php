<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductionRule Entity
 *
 * @property int $id
 * @property int $input_item_id
 * @property int $input_unit_id
 * @property int $input_quantity
 * @property int $output_item_id
 * @property int $output_unit_id
 * @property int $output_quantity
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Item $input_item
 * @property \App\Model\Entity\Unit $input_unit
 * @property \App\Model\Entity\Item $output_item
 * @property \App\Model\Entity\Unit $output_unit
 */
class ProductionRule extends Entity
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
