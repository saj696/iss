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
        <li>
            <?= $this->Html->link(__('Item Units'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Item Unit') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Item Unit Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th><?= __('Item') ?></th>
                            <td><?= $itemUnit->has('item') ? $this->Html->link($itemUnit->item->name, ['controller' => 'Items', 'action' => 'view', $itemUnit->item->id]) : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Unit') ?></th>
                            <td>
                                <?=
                                $itemUnit->unit->unit_display_name;
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$itemUnit->status]) ?></td>
                        </tr>

                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

