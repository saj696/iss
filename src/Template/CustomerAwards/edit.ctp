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
            <?= $this->Html->link(__('Customer Awards'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
                    <li><?= __('Edit Customer Award') ?></li>
        
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Customer Award') ?>
                                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
                
            </div>
            <div class="portlet-body">
                <?= $this->Form->create($customerAward,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                                                                    echo $this->Form->input('customer_id', ['options' => $customers, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('parent_global_id');
                                                                    echo $this->Form->input('award_account_code');
                                                                    echo $this->Form->input('award_id', ['options' => $awards, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('amount');
                                                                    echo $this->Form->input('customer_offer_id', ['options' => $customerOffers, 'empty' => __('Select')]);
                                                                    echo $this->Form->input('offer_period_start');
                                                                    echo $this->Form->input('offer_period_end');
                                                                    echo $this->Form->input('action_status');
                                                                    echo $this->Form->input('action_taken_at');
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

