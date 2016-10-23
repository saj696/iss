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
            <?= $this->Html->link(__('Decide Storage'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Decide') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Decide Storage') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered">
                        <tr><td class="text-center" colspan="12"><label class="label label-warning">Request Detail</label> </td></tr>
                        <tr>
                            <th>Item</th>
                            <th>Warehouse</th>
                            <th>Existing</th>
                            <th>Required</th>
                        </tr>
                        <?php foreach($requestWarehouseDetails as $detail):?>
                            <tr>
                                <td><?= $itemArray[$detail['item_id']]?></td>
                                <td><?= $warehouses[$detail['warehouse_id']]?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>

                <div class="row text-center">
                    <div class="col-md-5 col-md-offset-4">
                        <?php echo $this->Form->input('warehouse', ['options' => $warehouses, 'style'=>'max-width: 100%', 'class'=>'form-control warehouse select2me', 'empty' => __('-- Select warehouse --'), 'templates'=>['label' => '']]);?>
                    </div>
                </div>

                <div class="table-scrollable">
                    <table class="table table-bordered">
                        <tr><td class="text-center" colspan="12"><label class="label label-success">Item Existence</label> </td></tr>
                        <tr>
                            <th>Item</th>
                            <th>Warehouse</th>
                            <th>Quantity</th>
                            <th>Decided Qty</th>
                        </tr>
                        <?php foreach($myWarehouseDetails as $detail):?>
                            <tr>
                                <td><?= $itemArray[$detail['item_id']]?></td>
                                <td><?= $warehouses[$detail['warehouse_id']]?></td>
                                <td><?= $detail['quantity']?></td>
                                <td></td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

