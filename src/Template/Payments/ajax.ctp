<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 02-Oct-16
 * Time: 11:05 AM
 */
?>
<?php $key = 0; ?>
<tr>
    <td><?= $this->Number->format($key + 1) ?></td>
    <td><?= date('d-m-y',$dropArray['invoice_date']) ?></td>
    <td><?= $dropArray['net_total'] ?></td>
    <td><?= $dropArray['net_total'] - $dropArray['due'] ?></td>
    <td><?= $dropArray['due'] ?></td>
    <td><input type="text" name="current_payment[<?= $dropArray['id']?>]" class="form-control"/></td>
</tr>
