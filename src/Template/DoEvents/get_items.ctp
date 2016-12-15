<?php use App\View\Helper\SystemHelper;?>
<table class="table table-bordered">
    <tr>
        <td>SL:</td>
        <td>Item Name</td>
        <td>Unit</td>
        <td>Quantity</td>

    </tr>
    <?php foreach ($stocks as $key => $row): ?>

        <tr>
            <td><?= $key + 1 ?> </td>
            <td><input class="form-control" name="" type="text" readonly
                       value="<?php echo SystemHelper::getItemAlias($row['item']['id'], $sender_warehouse_id); ?>">
            </td>
            <td><input class="form-control" name="" type="text" readonly
                       value="<?= $row['unit']['unit_display_name']  ?>"></td>
            <td><input class="form-control" name="" type="text" readonly
                       value="<?= $row['quantity'] ?>"></td>
        </tr>
    <?php endforeach; ?>
</table>