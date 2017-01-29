<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 14-Nov-16
 * Time: 11:38 AM
 */
?>

<tr class="itemTr">
    <td><?= $itemName?></td>
    <td><input type="text" name="detail[<?= $item_unit_id?>][item_quantity]" class="form-control item_quantity" value="" /><input type="hidden" class="itemUnitId" name="itemUnitId[]" value="<?=$item_unit_id?>"></td>
    <td><input type="text" name="detail[<?= $item_unit_id?>][unit_price]" class="form-control unit_price" readonly value="<?= $unit_price?>" /></td>
    <td><input type="text" name="detail[<?= $item_unit_id?>][item_bonus]" class="form-control item_bonus" readonly value="0" /></td>
    <td><input type="text" name="detail[<?= $item_unit_id?>][special_offer_item_bonus]" class="form-control special_offer_item_bonus" readonly value="0" /></td>
    <td><input type="text" name="detail[<?= $item_unit_id?>][item_cash_discount]" class="form-control item_cash_discount" value="0" /></td>
    <td>
        <input type="text" name="detail[<?= $item_unit_id?>][item_net_total]" class="form-control item_net_total" readonly value="" />
        <input type="hidden" name="detail[<?= $item_unit_id?>][offer_id]" class="form-control offer_id" readonly value="" />
    </td>
    <td width="50px;"><span class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
</tr>
