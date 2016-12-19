<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OfferItem Entity
 *
 * @property int $id
 * @property int $offer_id
 * @property int $item_id
 * @property int $manufacture_unit_id
 * @property int $item_unit_id
 * @property int $program_period_start
 * @property int $program_period_end
 * @property int $offer_payment_mode
 * @property int $invoicing
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Offer $offer
 * @property \App\Model\Entity\Item $item
 * @property \App\Model\Entity\ManufactureUnit $manufacture_unit
 * @property \App\Model\Entity\ItemUnit $item_unit
 */
class OfferItem extends Entity
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
