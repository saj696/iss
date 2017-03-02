<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 02-Oct-16
 * Time: 11:05 AM
 */
?>
<?php
if($param=='explore_units'){
    ?>
    <select name="explore_unit" required="required" class="form-control explore_unit">
        <option value="">Select</option>
        <?php
        foreach($dropArray as $key=>$drop):
        ?>
            <option value="<?= $key?>"><?= $drop?></option>
        <?php endforeach;?>
    </select>
<?php
}elseif($param=='display_units') {
    ?>
    <select name="display_unit" required="required" class="form-control display_unit">
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
    <select name="customer_id" required="required" class="form-control customer">
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
