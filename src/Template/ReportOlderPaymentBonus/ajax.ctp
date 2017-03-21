<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 02-Oct-16
 * Time: 11:05 AM
 */
?>
<?php
if($param=='parent_units'){
    ?>
    <select name="unit_id" required="required" class="form-control parent_unit">
        <option value="">Select</option>
        <?php
        foreach($dropArray as $key=>$drop):
        ?>
            <option value="<?= $key?>"><?= $drop?></option>
        <?php endforeach;?>
    </select>
<?php
}elseif($param=='units') {
    ?>
    <select name="unit_id" required="required" class="form-control unit">
        <option value="">Select</option>
        <?php
        foreach($dropArray as $key=>$drop):
            ?>
            <option value="<?= $key?>"><?= $drop?></option>
        <?php endforeach;?>
    </select>
    <?php
}elseif($param=='customers') {
    ?>
    <div class="col-md-6 col-md-offset-3">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i>Customers
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-bordered">
                    <?php foreach($dropArray as $customer_id=>$customer):?>
                        <tr class="customerTr">
                            <td><?= $customer?></td>
                            <td><span data-customer="<?=$customer_id?>" data-max="<?=$maxCalDateArr[$customer_id]?>" class="btn btn-success calculate">Calculate</span></td>
                        </tr>
                    <?php endforeach;?>
                </table>
            </div>
        </div>
    </div>
<?php
}
?>
