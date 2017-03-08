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
        <li><?= $this->Html->link(__('Invoices'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Invoice List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Invoice'), ['action' => 'add'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer') ?></th>
                            <th><?= __('Invoice Date') ?></th>
                            <th><?= __('Delivery Date') ?></th>
                            <th><?= __('Total Amount') ?></th>
                            <th><?= __('Action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoices as $key => $invoice) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $invoice->customer->name ?></td>
                                <td><?= $this->System->display_date($invoice->invoice_date) ?></td>
                                <td><?= $this->System->display_date($invoice->delivery_date) ?></td>
                                <td><?= $invoice->net_total ?></td>
                                <td><?= $this->Html->link(__('Preview'), ['action' => 'printInvoice', $invoice->id], ['class' => 'btn btn-circle default yellow-stripe']);?></td>
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
        </div>
    </div>
</div>

