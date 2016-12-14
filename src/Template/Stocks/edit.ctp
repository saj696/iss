<?php
use Cake\Core\Configure;

$config_stock_types = Configure::read('stock_log_types');
unset($config_stock_types[0], $config_stock_types[1], $config_stock_types[2]);
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
        <li><?= __('Edit Stock') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Stock') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($stock, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('warehouse_id', ['options' => $warehouses, 'empty' => __('Select')]);
                        echo $this->Form->input('item_id', ['options' => $items, 'empty' => __('Select')]);
                        echo $this->Form->input('manufacture_unit_id', ['options' => $units, 'label' => 'Unit', 'empty' => __('Select')]);
                        echo $this->Form->input('type', ['class' => 'form-control', 'options' => $config_stock_types, 'label' => 'Stock Type']);
                        echo $this->Form->input('quantity', ['class' => 'form-control numbersOnly', 'label' => 'Quantity (Pcs)']);
                        echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
    });
</script>
