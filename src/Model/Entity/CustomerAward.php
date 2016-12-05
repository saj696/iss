<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CustomerAward Entity.
 *
 * @property int $id
 * @property int $customer_id
 * @property int $parent_global_id
 * @property int $award_account_code
 * @property int $award_id
 * @property float $amount
 * @property int $customer_offer_id
 * @property int $offer_period_start
 * @property int $offer_period_end
 * @property int $action_status
 * @property int $action_taken_at
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class CustomerAward extends Entity
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
