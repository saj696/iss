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
            <?= $this->Html->link(__('Customer Award Givings'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
                    <li><?= __('Edit Customer Award Giving') ?></li>
        
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Customer Award Giving') ?>
                                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
                
            </div>
            <div class="portlet-body">
                <?= $this->Form->create($customerAwardGiving,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                                                                    echo $this->Form->input('customer_award_id', ['options' => $customerAwards, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('customer_id', ['options' => $customers, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('parent_global_id');
                                                                    echo $this->Form->input('award_account_code');
                                                                    echo $this->Form->input('award_id', ['options' => $awards, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('amount');
                                                                    echo $this->Form->input('giving_mode');
                                                                    echo $this->Form->input('award_giving_date');
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

