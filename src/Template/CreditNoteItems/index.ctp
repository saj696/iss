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
        <li><?= $this->Html->link(__('Credit Note Items'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Credit Note Items') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'created_credit_notes'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Invoice No.') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Unit') ?></th>
                            <th><?= __('quantity') ?></th>
                            <th><?= __('Total') ?></th>
                            <th><?= __('status') ?></th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($creditNoteItems as $key => $creditNoteItem) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $creditNoteItem->has('invoice') ?
                                        $this->Html->link($creditNoteItem->invoice
                                            ->id, ['controller' => 'Invoices',
                                            'action' => 'view', $creditNoteItem->invoice
                                                ->id]) : '' ?></td>
                                <td><?= $creditNoteItem->has('item') ?
                                        $this->Html->link($creditNoteItem->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $creditNoteItem->item
                                                ->id]) : '' ?></td>
                                <td><?= $creditNoteItem->has('unit') ?
                                        $this->Html->link($creditNoteItem->unit
                                            ->unit_display_name, ['controller' => 'Units',
                                            'action' => 'view', $creditNoteItem->unit
                                                ->manufacture_unit_id]) : '' ?></td>
                                <td><?= $this->Number->format($creditNoteItem->quantity) ?></td>
                                <td><?= $this->Number->format($creditNoteItem->net_total) ?></td>
                                <td><?= __($status[$creditNoteItem->status]) ?></td>
<!--                                <td class="actions">-->
                                    <?php
                                  //  echo $this->Html->link(__('View'), ['action' => 'view', $creditNoteItem->id], ['class' => 'btn btn-sm btn-info']);

//                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $creditNoteItem->id], ['class' => 'btn btn-sm btn-warning']);

                                   // echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $creditNoteItem->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $creditNoteItem->id)]);

                                    ?>

<!--                                </td>-->
                            </tr>

                        <?php } ?>
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
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

