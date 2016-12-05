<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CustomerAwardGiving Entity.
 *
 * @property int $id
 * @property int $customer_award_id
 * @property int $customer_id
 * @property int $parent_global_id
 * @property int $award_account_code
 * @property int $award_id
 * @property int $amount
 * @property int $giving_mode
 * @property int $award_giving_date
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class CustomerAwardGiving extends Entity
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
