<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DoObject Entity.
 *
 * @property int $id
 * @property int $serial_no
 * @property int $date
 * @property int $object_type
 * @property int $target_type
 * @property int $target_id
 * @property int $action_status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class DoObject extends Entity
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
