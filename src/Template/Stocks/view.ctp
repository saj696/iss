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
            <?= $this->Html->link(__('Stocks'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Stock') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Stock Details') ?>
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
                            <td><?= $stock->has('warehouse') ? $this->Html->link($stock->warehouse->name, ['controller' => 'Warehouses', 'action' => 'view', $stock->warehouse->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Item') ?></th>
                            <td><?= $stock->has('item') ? $this->Html->link($stock->item->name, ['controller' => 'Items', 'action' => 'view', $stock->item->id]) : '' ?></td>
                        </tr>
                        <tr><th><?= __('Unit') ?></th>
                        <td><?=
                            $stock->has('unit') ? $this->Html->link(
                                $stock->unit->unit_display_name, ['controller' => 'Units', 'action' => 'view', $stock->unit->id])
                                : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Quantity') ?></th>
                            <td><?= $this->Number->format($stock->quantity) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$stock->status]) ?></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

