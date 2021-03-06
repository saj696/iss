<?php
use Cake\Core\Configure;

$invoice_type = \Cake\Core\Configure::read('invoice_type');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Item Bonuses'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Item Bonus') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Item Bonus') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($itemBonus, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('item_id', ['options' => $items, 'required', 'empty' => __('Select')]);
                        echo $this->Form->input('manufacture_unit_id', ['options' => $units, 'required', 'empty' => __('Select')]);
                        echo $this->Form->input('order_quantity_from', ['class' => 'numbersOnly form-control', 'required']);
                        echo $this->Form->input('order_quantity_to', ['class' => 'numbersOnly form-control', 'required']);
                        echo $this->Form->input('bonus_quantity', ['class' => 'numbersOnly form-control', 'required']);
                        echo $this->Form->input('invoice_type', ['options' => Configure::read('invoice_type'), 'required']);
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
