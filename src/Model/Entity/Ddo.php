<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Ddo Entity
 *
 * @property int $id
 * @property int $date
 * @property int $do_delivering_warehouse
 * @property int $do_receiving_warehouse
 * @property int $do_ds_serial_number
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class Ddo extends Entity
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
