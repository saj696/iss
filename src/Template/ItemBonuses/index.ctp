<?php
$status = \Cake\Core\Configure::read('status_options');
$invoice_type = \Cake\Core\Configure::read('invoice_type');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Item Bonuses'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Item Bonus List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Item Bonus'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
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
                            <th><?= __('Order Quantity From') ?></th>
                            <th><?= __('Order Quantity To') ?></th>
                            <th><?= __('Bonus Quantity') ?></th>
                            <th><?= __('Invoice Type') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($itemBonuses as $key => $itemBonus) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $itemBonus->has('item') ?
                                        $this->Html->link($itemBonus->item
                                            ->name, ['controller' => 'Items',
                                            'action' => 'view', $itemBonus->item
                                                ->id]) : '' ?></td>
                                <td><?=
                                    $itemBonus->has('unit') ? $this->Html->link(__($itemBonus->unit->unit_display_name)
                                        , ['controller' => 'Units', 'action' => 'view', $itemBonus->unit->id])
                                        : '' ?></td>
                                <td><?= $this->Number->format($itemBonus->order_quantity_from) ?></td>
                                <td><?= $this->Number->format($itemBonus->order_quantity_to) ?></td>
                                <td><?= $this->Number->format($itemBonus->bonus_quantity) ?></td>
                                <td><?= __($invoice_type[$itemBonus->invoice_type]) ?></td>
                                <td><?= __($status[$itemBonus->status]) ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $itemBonus->id], ['class' => 'btn btn-sm btn-info']);
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $itemBonus->id], ['class' => 'btn btn-sm btn-warning']);
                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $itemBonus->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $itemBonus->id)]);
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
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
    </div>
</div>

