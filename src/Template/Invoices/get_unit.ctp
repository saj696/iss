<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 02-Oct-16
 * Time: 11:05 AM
 */
?>

<select name="customer_unit" required="required" class="form-control customer_unit">
    <option value="">Select</option>
    <?php
    foreach($dropArray as $key=>$drop):
    ?>
        <option value="<?= $key?>"><?= $drop?></option>
    <?php endforeach;?>
</select>