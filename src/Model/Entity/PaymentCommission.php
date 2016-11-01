<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PaymentCommission Entity
 *
 * @property int $id
 * @property int $payment_age_from
 * @property int $payment_age_to
 * @property float $commission
 * @property int $policy_period_start
 * @property int $policy_period_end
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class PaymentCommission extends Entity
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
