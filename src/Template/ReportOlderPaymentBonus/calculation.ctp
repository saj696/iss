<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 24-Jan-17
 * Time: 1:19 PM
 */
?>
<table class="table table-bordered">
    <?php
    if (sizeof($returnArray) > 0):
        ?>
        <tr><td colspan="12" class="text-center"><span class="label label-success crossSpan">Bonus on Full Payment of Older Dues</span></td></tr>
        <tr>
            <th>Customer Name</th>
            <th>Code</th>
            <th>Address</th>
            <th>Payment</th>
            <th>Bonus</th>
            <th>Action</th>
        </tr>
        <?php
        foreach ($returnArray as $customer_id=>$returnData):
            ?>
            <tr class="customerTr">
                <td>
                    <?= $customerDetailArray[$customer_id]['name']?>
                    <div class="customer_id" style="display: none;"><?= $customer_id?></div>
                    <div class="amount" style="display: none;"><?= $returnData['bonus']?></div>
                    <div class="start_date" style="display: none;"><?= $data['dues_upto_date']?></div>
                    <div class="end_date" style="display: none;"><?= $data['payment_date']?></div>
                    <div class="unit_id" style="display: none;"><?= $data['unit_id']?></div>
                </td>
                <td><?= $customerDetailArray[$customer_id]['code']?></td>
                <td><?= $customerDetailArray[$customer_id]['address']?></td>
                <td><?= $returnData['total_payment']?></td>
                <td><?= $returnData['bonus']?></td>
                <td><span style="cursor: pointer;" class="btn btn-warning save">Save</span></td>
            </tr>
            <?php
        endforeach;
    else:
        ?>
        <tr>
            <td colspan="12" class="text-center"><span class="label label-warning crossSpan">No Bonus</span></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td colspan="12" class="text-center">
            <span style="cursor: pointer;" class="btn btn-danger crossSpan">Close</span>
        </td>
    </tr>
</table>
