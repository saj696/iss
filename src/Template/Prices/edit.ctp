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
            <?= $this->Html->link(__('Prices'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Price') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Price') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($price, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        //debug($price['item_unit_id']);
                        echo $this->Form->input('item_unit_id', ['label' => 'Item', 'options' => $item_unit, 'empty' => __('Select')]);
                        //echo $this->Form->input('unit_name', ['disabled' => true]);
                       // echo $this->Form->input('unit_display_name', ['disabled' => true,'label' => 'Unit']);
                      //  echo $this->Form->input('converted_quantity', ['disabled' => true]);
                        echo $this->Form->input('cash_sales_price');
                        echo $this->Form->input('credit_sales_price');
                        echo $this->Form->input('retail_price');
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

