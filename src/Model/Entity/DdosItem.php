<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DdosItem Entity
 *
 * @property int $id
 * @property int $ddo_id
 * @property int $item_id
 * @property int $unit_id
 * @property int $quantity
 *
 * @property \App\Model\Entity\Ddo $ddo
 * @property \App\Model\Entity\Item $item
 * @property \App\Model\Entity\Unit $unit
 */
class DdosItem extends Entity
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
