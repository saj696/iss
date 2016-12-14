<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Unit Entity.
 *
 * @property int $id
 * @property int $unit_level
 * @property int $constituent_unit_id
 * @property string $unit_name
 * @property string $unit_display_name
 * @property float $unit_size
 * @property string $unit_type
 * @property float $converted_quantity
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 * @property int $status
 */
class Unit extends Entity
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
