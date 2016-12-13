<?php if ($account_heads) { ?>

    <table class="table table-bordered">
        <tr>
            <td>Accounts</td>
            <td>From</td>
            <td>To</td>
            <td colspan="2">Amount</td>
            <td>Upto</td>
            <td colspan="2">Amount</td>
        </tr>

        <?php foreach ($account_heads as $row): ?>
            <tr class="tbl_row ">
                <td><input type="hidden" value="<?= $row['code'] ?>" class="account_head"><strong><?= $row['name'] ?> </strong></td>
                <td><input class="form-control datepicker frome_date" id="" name="" type="text"></td>
                <td><input class="form-control datepicker to_date " id="" name="" type="text"></td>
                <td><input type="text" class="form-control total_amount" id="total_amount" readonly></td>
                <td>
                    <button class="button btn btn-danger btn-sm add_total_amount" title="Add Record"><strong>+</strong>
                    </button>
                </td>
                <td><input class="form-control datepicker upto_date" id="" name="" type="text"></td>
                <td><input type="text" class="form-control" id="upto_total_amount" readonly></td>
                <td>
                    <button class="button btn btn-danger btn-sm add_upto_total_amount" title="Add Record">
                        <strong>+</strong></button>
                </td>


            </tr>


        <?php endforeach; ?>
    </table>


<?php } ?>
