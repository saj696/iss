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
            <?= $this->Html->link(__('Offers'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Offer') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Offer') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($offer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('program_name', ['label'=>'Program Name', 'required', 'readonly',  'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_start', ['value'=>date('d-m-Y', $offer['program_period_start']), 'label'=>'Period Start', 'required', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_end', ['value'=>date('d-m-Y', $offer['program_period_end']), 'label'=>'Period End', 'type'=>'text', 'required', 'class'=>'form-control datepicker', 'templates'=>['label'=>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('status', ['empty'=>'Select', 'options'=>[1=>'Active', 2=>'In-active'], 'required', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-7"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
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
    $(document).ready(function(){
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on("focus",".datepicker", function() {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });
    });
</script>