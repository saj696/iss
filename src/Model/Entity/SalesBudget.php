<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SalesBudget Entity.
 *
 * @property int $id
 * @property int $sales_budget_configuration_id
 * @property \Cake\I18n\Time $budget_period_start
 * @property \Cake\I18n\Time $budget_period_end
 * @property int $level_no
 * @property int $administrative_unit_id
 * @property int $product_scope
 * @property int $item_id
 * @property int $sales_measure_unit
 * @property int $sales_amount
 * @property int $status
 * @property int $created_by
 * @property int $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class SalesBudget extends Entity
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
