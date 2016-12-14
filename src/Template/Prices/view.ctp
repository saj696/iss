<?php
$status = \Cake\Core\Configure::read('status_options');
$unit_type = \Cake\Core\Configure::read('pack_size_units');
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
            <?= $this->Html->link(__('Prices'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Price') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Price Details') ?>
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
                            <td><?= $price->has('item') ? $this->Html->link($price->item->name, ['controller' => 'Items', 'action' => 'view', $price->item->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Unit') ?></th>
                            <td><?=
                                $price->has('unit') ? $this->Html->link($price->unit->unit_display_name
                                ,['controller' => 'Units', 'action' => 'view', $price->unit->id])
                                    : '' ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Cash Sales Price') ?></th>
                            <td><?= $this->Number->format($price->cash_sales_price) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Credit Sales Price') ?></th>
                            <td><?= $this->Number->format($price->credit_sales_price) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Retail Price') ?></th>
                            <td><?= $this->Number->format($price->retail_price) ?></td>
                        </tr>


                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$price->status]) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

