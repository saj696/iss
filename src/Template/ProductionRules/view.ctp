<?php
$status = \Cake\Core\Configure::read('status_options');
$quantity = \Cake\Core\Configure::read('pack_size_units');

?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Production Rules'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Production Rule') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Production Rule Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">

                        <tr>
                            <th><?= __('Input Item') ?></th>
                            <td><?= $productionRule->has('input_item') ?
                                    $this->Html->link($productionRule->input_item
                                        ->name, ['controller' => 'Items',
                                        'action' => 'view', $productionRule->input_item
                                            ->id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Input Unit') ?></th>
                            <td><?= $productionRule->has('input_unit') ?
                                    $this->Html->link($productionRule->input_unit
                                        ->unit_display_name, ['controller' => 'Items',
                                        'action' => 'view', $productionRule->input_unit
                                            ->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Input Quantity') ?></th>
                            <td><?= __($quantity[$productionRule->input_quantity]) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Output Item') ?></th>
                            <td><?= $productionRule->has('output_item') ?
                                    $this->Html->link($productionRule->output_item
                                        ->name, ['controller' => 'Items',
                                        'action' => 'view', $productionRule->output_item
                                            ->id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Output Unit') ?></th>
                            <td><?= $productionRule->has('output_unit') ?
                                    $this->Html->link($productionRule->output_unit
                                        ->unit_display_name, ['controller' => 'Items',
                                        'action' => 'view', $productionRule->output_unit
                                            ->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Output Quantity') ?></th>
                            <td><?= __($quantity[$productionRule->output_quantity]) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$productionRule->status]) ?></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

