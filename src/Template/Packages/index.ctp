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
        <li><?= $this->Html->link(__('Packages'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Package List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Package'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('warehouse_id') ?></th>
                                                                                                                                                <th><?= __('item_id') ?></th>
                                                                                                                                                <th><?= __('manufacture_unit_id') ?></th>
                                                                                                                                                <th><?= __('quantity') ?></th>
                                                                                                                                                <th><?= __('status') ?></th>
                                                                                                                                                <th><?= __('created_by') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packages as $key => $package) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                                <td><?= $package->has('warehouse') ?
                                                    $this->Html->link($package->warehouse
                                                    ->name, ['controller' => 'Warehouses',
                                                    'action' => 'view', $package->warehouse
                                                    ->id]) : '' ?></td>
                                                                                                        <td><?= $package->has('item') ?
                                                    $this->Html->link($package->item
                                                    ->name, ['controller' => 'Items',
                                                    'action' => 'view', $package->item
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($package->manufacture_unit_id) ?></td>
                                                                                            <td><?= $this->Number->format($package->quantity) ?></td>
                                                                                            <td><?= __($status[$package->status]) ?></td>
                                                                                            <td><?= $this->Number->format($package->created_by) ?></td>
                                                                                <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $package->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $package->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $package->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $package->id)]);
                                            
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

