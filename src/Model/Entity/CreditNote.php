<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CreditNote Entity.
 *
 * @property int $id
 * @property int $customer_id
 * @property int $parent_global_id
 * @property \Cake\I18n\Time $date
 * @property float $total_after_demurrage
 * @property float $demurrage_percentage
 * @property int $approval_status
 * @property int $adjustment_status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 * @property int $status
 */
class CreditNote extends Entity
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
        'id' => true,
    ];
}
