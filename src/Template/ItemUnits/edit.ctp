<?php
use Cake\Core\Configure;

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
        <li><?= __('Edit Item Unit') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Item Unit') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($itemUnit, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('item_id', ['options' => $items, 'class' => 'form-control items', 'empty' => __('Select')]);
                        echo $this->Form->input('manufacture_unit_id', ['label' => 'Unit','class'=>'form-control units', 'options' => $units, 'empty' => __('Select')]);
                        echo $this->Form->input('code', ['label' => 'Code', 'class' => 'form-control codeItem', 'readonly' => true]);
                        echo $this->Form->input('unit_name', ['label' => 'Unit Name', 'disabled' => true]);
                        echo $this->Form->input('unit_size', ['label' => 'Unit Size', 'disabled' => true]);
                        echo $this->Form->input('unit_display_name', ['label' => 'Unit Display Name', 'disabled' => true]);
                        echo $this->Form->input('converted_quantity', ['label' => 'Converted Quantity', 'disabled' => true]);
                        echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
    });
</script>