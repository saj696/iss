<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InvoicedProduct Entity
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $customer_level_no
 * @property int $customer_unit_global_id
 * @property int $customer_type
 * @property int $customer_id
 * @property int $invoice_date
 * @property int $delivery_date
 * @property int $depot_level_no
 * @property int $depot_unit_global_id
 * @property int $depot_id
 * @property int $product_id
 * @property float $product_quantity
 * @property float $bonus_quantity
 * @property float $instant_discount
 * @property float $item_net_total
 * @property float $due_amount
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Invoice $invoice
 * @property \App\Model\Entity\CustomerUnitGlobal $customer_unit_global
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\DepotUnitGlobal $depot_unit_global
 * @property \App\Model\Entity\Depot $depot
 * @property \App\Model\Entity\Product $product
 */
class InvoicedProduct extends Entity
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
