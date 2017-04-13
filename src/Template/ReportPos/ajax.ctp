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
    <select name="customer_id" class="form-control customer">
        <option value="">Select</option>
        <?php
        foreach($dropArray as $key=>$drop):
            ?>
            <option value="<?= $key?>"><?= $drop?></option>
        <?php endforeach;?>
    </select>
<?php
}
?>
