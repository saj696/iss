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
        <li>
            <?= $this->Html->link(__('Warehouse Items'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Warehouse Item') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Warehouse Item Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th><?= __('Warehouse') ?></th>
                            <td><?= $warehouseItem->has('warehouse') ? $this->Html->link($warehouseItem->warehouse->name, ['controller' => 'Warehouses', 'action' => 'view', $warehouseItem->warehouse->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Item') ?></th>
                            <td><?= $warehouseItem->has('item') ? $this->Html->link($warehouseItem->item->name, ['controller' => 'Items', 'action' => 'view', $warehouseItem->item->id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Use Alias') ?></th>
                            <td><?= __($yes_no[$warehouseItem->use_alias]) ?></td>
                        </tr>


                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$warehouseItem->status]) ?></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

