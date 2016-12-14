<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ItemUnit Entity.
 *
 * @property int $id
 * @property int $item_id
 * @property int $item_unit_id
 * @property int $status
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 * @property int $created_by
 */
class ItemUnit extends Entity
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
