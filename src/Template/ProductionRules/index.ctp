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
        <li><?= $this->Html->link(__('Production Rules'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Production Rules') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Add New Production Rule'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Input Item') ?></th>
                            <th><?= __('Input Unit') ?></th>
                            <th><?= __('Input Quantity') ?></th>
                            <th><?= __('Output Item') ?></th>
                            <th><?= __('Output Unit') ?></th>
                            <th><?= __('Output Quantity') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($productionRules as $key => $productionRule) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $productionRule->has('input_item') ?
                                        $this->Html->link($productionRule->input_item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $productionRule->input_item
                                                ->id]) : '' ?></td>
                                <td><?= $productionRule->has('input_unit') ?
                                        $this->Html->link($productionRule->input_unit
                                            ->unit_display_name, ['controller' => 'Items',
                                            'action' => 'view', $productionRule->input_unit
                                                ->id]) : '' ?></td>
                                <td><?= $this->Number->format($productionRule->input_quantity)?></td>
                                <td><?= $productionRule->has('output_item') ?
                                        $this->Html->link($productionRule->output_item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $productionRule->output_item
                                                ->id]) : '' ?></td>
                                <td><?= $productionRule->has('output_unit') ?
                                        $this->Html->link($productionRule->output_unit
                                            ->unit_display_name, ['controller' => 'Items',
                                            'action' => 'view', $productionRule->output_unit
                                                ->id]) : '' ?></td>
                                <td><?= $this->Number->format($productionRule->output_quantity)?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $productionRule->id], ['class' => 'btn btn-sm btn-info']);

                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $productionRule->id], ['class' => 'btn btn-sm btn-warning']);

                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $productionRule->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $productionRule->id)]);

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

