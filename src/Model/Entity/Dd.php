<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dd Entity
 *
 * @property int $id
 * @property int $date
 * @property string $pi_ids
 * @property string $do_ids
 * @property int $do_ds_serial_number
 */
class Dd extends Entity
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