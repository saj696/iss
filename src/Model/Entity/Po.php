<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Po Entity.
 *
 * @property int $id
 * @property int $customer_level_no
 * @property int $customer_unit_global_id
 * @property int $customer_type
 * @property int $customer_id
 * @property int $po_date
 * @property int $delivery_date
 * @property int $invoice_type
 * @property float $net_total
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class Po extends Entity
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
