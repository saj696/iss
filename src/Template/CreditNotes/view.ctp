<?php
use Cake\Core\Configure;

$approval_status = Configure::read('approval_status');
?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Credit Notes'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Credit Note') ?></li>

    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Credit Note') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>

            <div class="portlet-body">
                <h4 style="text-align:center">Items</h4>
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Invoice No.') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Unit') ?></th>
                            <th><?= __('Quantity') ?></th>
                            <th><?= __('Net Total') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // pr($creditNote);die;
                        foreach ($creditNote['credit_note_items'] as $key => $credit_notes): ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $credit_notes->invoice_id ?></td>
                                <td><?= $credit_notes->has('item') ?
                                        $this->Html->link($credit_notes->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $credit_notes->item
                                                ->id]) : '' ?></td>
                                <td><?= $credit_notes->has('unit') ?
                                        $this->Html->link($credit_notes->unit
                                            ->unit_display_name, ['controller' => 'Items',
                                            'action' => 'view', $credit_notes->unit
                                                ->id]) : '' ?></td>
                                <td><?= $credit_notes->quantity ?></td>
                                <td><?= $credit_notes->net_total ?></td>
                            </tr>

                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
                    <thead>
                    </thead>
                    <tbody>
                    <tr>
                        <th>Customer</th>
                        <td>
                            <?= $creditNote['customer']['name'] ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>
                            <?= date('d-m-Y', $creditNote['date']) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Percentage</th>
                        <td>
                            <?= $creditNote['demurrage_percentage'] ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>
                            <?= $creditNote['total_after_demurrage'] ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Approval Status</th>
                        <td>
                            <?= __($approval_status[$creditNote['approval_status']]) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- END BORDERED TABLE PORTLET-->
</div>

</div>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
    </div>
    <script>
        var base_amount = '<?php echo $base_amount?>';
        console.log(base_amount);
        //  $(document).on('keyup', '#demurrage-percentage', function () {
        var percentage = parseFloat($("#demurrage-percentage").val());
        console.log(percentage);
        var new_a = base_amount * percentage / 100;
        console.log(new_a);
        console.log(base_amount);
        var amount_new_percentage = 0;
        amount_new_percentage = new_a + parseFloat(base_amount);
        console.log(amount_new_percentage);
        $("#total-after-demurrage").val('');
        $("#total-after-demurrage").val(amount_new_percentage);
        // });
    </script>

