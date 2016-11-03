<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 02-Oct-16
 * Time: 11:05 AM
 */
?>

<select name="credit_approved_by" class="form-control credit_approved_by">
    <option value="">Select</option>
    <?php
    foreach($dropArray as $key=>$drop):
    ?>
        <option value="<?= $key?>"><?= $drop?></option>
    <?php endforeach;?>
</select>