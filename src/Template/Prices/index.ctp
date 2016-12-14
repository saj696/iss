<?php
$status = \Cake\Core\Configure::read('status_options');
$unit_type = \Cake\Core\Configure::read('pack_size_units');
$unit_levels = \Cake\Core\Configure::read('unit_levels');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Prices'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Price List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Price'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
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
                            <th><?= __('Cash Sales Price') ?></th>
                            <th><?= __('Credit Sales Price') ?></th>
                            <th><?= __('Retail Price') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($prices as $key => $price) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $price->has('item') ?
                                        $this->Html->link($price->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $price->item
                                                ->id]) : '' ?></td>
                                <td><?=
                                    $price->has('unit') ? $this->Html->link(
                                     $price->unit->unit_display_name, ['controller' => 'Units', 'action' => 'view', $price->unit->id])
                                        : '' ?></td>
                                <td><?= h($price->unit_display_name) ?></td>
                                <td><?= $this->Number->format($price->cash_sales_price) ?></td>
                                <td><?= $this->Number->format($price->credit_sales_price) ?></td>
                                <td><?= $this->Number->format($price->retail_price) ?></td>

                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $price->id], ['class' => 'btn btn-sm btn-info']);

                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $price->id], ['class' => 'btn btn-sm btn-warning']);

                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $price->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $price->id)]);

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

