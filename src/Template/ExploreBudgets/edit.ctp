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
            <?= $this->Html->link(__('Sales Budgets'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
                    <li><?= __('Edit Sales Budget') ?></li>
        
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Sales Budget') ?>
                                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
                
            </div>
            <div class="portlet-body">
                <?= $this->Form->create($salesBudget,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                                                                    echo $this->Form->input('sales_budget_configuration_id', ['options' => $salesBudgetConfigurations, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('budget_period_start');
                                                                    echo $this->Form->input('budget_period_end');
                                                                    echo $this->Form->input('level_no');
                                                                    echo $this->Form->input('administrative_unit_id', ['options' => $administrativeUnits, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('product_scope');
                                                                    echo $this->Form->input('item_id');
                                                                    echo $this->Form->input('sales_measure_unit');
                                                                    echo $this->Form->input('sales_amount');
                                                                echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                                                                    echo $this->Form->input('created_by');
                                                                    echo $this->Form->input('created_date');
                                                                    echo $this->Form->input('updated_by');
                                                                    echo $this->Form->input('updated_date');
                                                    ?>
                        <?= $this->Form->button(__('Submit'),['class'=>'btn blue pull-right','style'=>'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

