<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InvoicePayment Entity
 *
 * @property int $id
 * @property int $customer_type
 * @property int $customer_id
 * @property string $parent_global_id
 * @property int $invoice_id
 * @property int $invoice_date
 * @property int $invoice_delivery_date
 * @property int $payment_id
 * @property int $payment_collection_date
 * @property float $invoice_wise_payment_amount
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 *
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\ParentGlobal $parent_global
 * @property \App\Model\Entity\Invoice $invoice
 * @property \App\Model\Entity\Payment $payment
 * @property \App\Model\Entity\InvoicedProductsPayment[] $invoiced_products_payments
 */
class InvoicePayment extends Entity
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
