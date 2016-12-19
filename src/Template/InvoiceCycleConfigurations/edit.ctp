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
            <?= $this->Html->link(__('Invoice Cycle Configurations'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Invoice Cycle Configuration') ?></li>
    </ul>
</div>

<div class="row invoiceCycle">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Invoice Cycle Configuration') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($invoiceCycleConfiguration, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <?php
                        echo $this->Form->input('invoice_approved_at', ['default'=>0, 'type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('invoice_approved_at'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-3 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-9 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('approving_user_group', ['empty'=>'Select', 'options'=>$userGroups, 'class'=>'form-control']);
                        echo $this->Form->input('allow_delivery_before_approval', ['default'=>1, 'type'=>'radio', 'class'=>'radio-inline form-control', 'options' => [1=>'Yes', 0=>'No'], 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-3 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-9 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

