<?php
$status = \Cake\Core\Configure::read('status_options');
$unit_level = \Cake\Core\Configure::read('unit_levels');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Item Units'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Item Unit List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Item Unit'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Unit') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($itemUnits as $key => $itemUnit) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $itemUnit->has('item') ?
                                        $this->Html->link($itemUnit->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $itemUnit->item
                                                ->id]) : '' ?></td>

                                <td><?= $itemUnit->has('unit') ?
                                        $this->Html->link($itemUnit->unit
                                            ->unit_display_name, ['controller' => 'Units',
                                            'action' => 'view', $itemUnit->unit
                                                ->id]) : '' ?></td>

                                <td><?= __($status[$itemUnit->status]) ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $itemUnit->id], ['class' => 'btn btn-sm btn-info']);

                                    ///echo $this->Html->link(__('Edit'), ['action' => 'edit', $itemUnit->id], ['class' => 'btn btn-sm btn-warning']);

                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $itemUnit->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $itemUnit->id)]);

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

<!--                                <td>--><?php
//                                    $itemUnit->has('unit') ? $this->Html->link(__($unit_level[$itemUnit->unit->unit_level])
//                                        . '__' . $itemUnit->unit->unit_name
//                                        . '__' . $itemUnit->unit->unit_size, ['controller' => 'Units', 'action' => 'view', $itemUnit->unit->id])
//                                        : '' ?>
<!--</td>-->