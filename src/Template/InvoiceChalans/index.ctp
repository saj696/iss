<?php
$status = \Cake\Core\Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Invoice Chalans'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <form class="form-horizontal" id="chalanForm" role="form" action="<?= $this->Url->build("/InvoiceChalans/chalanInvoice")?>" method="post">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Invoice List') ?>
                </div>
                <div class="pull-right">
                    <button type="submit" style="margin-top: 6px;" class="btn btn-sm grey-gallery">Make Chalan</button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th></th>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer') ?></th>
                            <th><?= __('Invoice Date') ?></th>
                            <th><?= __('Net Total') ?></th>
                            <th><?= __('Delivery Date') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoices as $key => $invoice) { ?>
                            <tr>
                                <td style="width: 4%">
                                    <input type="checkbox" <?php if($invoice->is_action_taken==1){echo 'disabled';} ?> name="invoice_ids[]" value="<?=$invoice->invoice->id?>" />
                                </td>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $invoice->invoice->customer->name?></td>
                                <td><?= date('d-m-Y', $invoice->invoice->invoice_date)?></td>
                                <td><?= $invoice->invoice->net_total?></td>
                                <td><?= date('d-m-Y', $invoice->invoice->delivery_date)?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <ul class="pagination">
                    <?php
                    echo $this->Paginator->prev('<<');
                    echo $this->Paginator->numbers();
                    echo $this->Paginator->next('>>');
                    ?>
                </ul>
            </div>
            </form>
        </div>
    </div>
</div>

