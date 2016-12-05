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
        <li>
            <?= $this->Html->link(__('Credit Note Items'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Credit Note Item') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Credit Note Item Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th><?= __('Invoice') ?></th>
                            <td><?= $creditNoteItem->has('invoice') ? $this->Html->link($creditNoteItem->invoice->id, ['controller' => 'Invoices', 'action' => 'view', $creditNoteItem->invoice->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Item') ?></th>
                            <td><?= $creditNoteItem->has('item') ? $this->Html->link($creditNoteItem->item->name, ['controller' => 'Items', 'action' => 'view', $creditNoteItem->item->id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Unit') ?></th>
                            <td><?= $creditNoteItem->has('unit') ?
                                    $this->Html->link($creditNoteItem->unit
                                        ->unit_display_name, ['controller' => 'Units',
                                        'action' => 'view', $creditNoteItem->unit
                                            ->manufacture_unit_id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Quantity') ?></th>
                            <td><?= $this->Number->format($creditNoteItem->quantity) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Net Total') ?></th>
                            <td><?= $this->Number->format($creditNoteItem->net_total) ?></td>
                        </tr>


                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$creditNoteItem->status]) ?></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

