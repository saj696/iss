<?php
$status = \Cake\Core\Configure::read('status_options');
$yes_no = \Cake\Core\Configure::read('yes_no');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Warehouse Items'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Warehouse Item List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Warehouse Item'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Warehouse') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Use Alias ?') ?></th>
                            <th><?= __('status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($warehouseItems as $key => $warehouseItem) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $warehouseItem->has('warehouse') ?
                                        $this->Html->link($warehouseItem->warehouse
                                            ->name, ['controller' => 'Warehouses',
                                            'action' => 'view', $warehouseItem->warehouse
                                                ->id]) : '' ?></td>
                                <td><?= $warehouseItem->has('item') ?
                                        $this->Html->link($warehouseItem->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $warehouseItem->item
                                                ->id]) : '' ?></td>
                                <td><?= __($yes_no[$warehouseItem->use_alias]) ?></td>
                                <td><?= __($status[$warehouseItem->status]) ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $warehouseItem->id], ['class' => 'btn btn-sm btn-info']);

                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $warehouseItem->id], ['class' => 'btn btn-sm btn-warning']);

                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $warehouseItem->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $warehouseItem->id)]);

                                    ?>

                                </td>
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

