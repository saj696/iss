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
        <li><?= __('New Offer') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Offer') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body main_div" style="margin-bottom: 20px;">
                <?= $this->Form->create($offer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('program_name', ['templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_start', ['label'=>'Period Start', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_end', ['label'=>'Period End', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label'=>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('offer_payment_mode', ['empty'=>'Select', 'options'=>Configure::read('offer_payment_mode'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-7"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('invoicing', ['default'=>1, 'type' => 'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_invoicing'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <div class="general condition_div">
                            <table class="table table-bordered magic_table">
                                <tr>
                                    <td class="text-center">
                                        <span class="label label-warning">General Conditions</span>
                                        <span class="pull-right"><input type="checkbox" class="condition_check" name="general_check" value="1"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= $this->Form->input('general_conditions', ['type'=>'textarea', 'style'=>'width:100%', 'rows'=>8, 'class'=>'form-control condition', 'templates'=>['label'=>'', 'textarea' => '<div class="col-sm-12"><textarea class="form-control condition" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="general condition_div">
                            <div class="list" data-index_no="0">
                                <div class="conditionWrapper">
                                    <div class="moreTable">
                                        <div class="specific_condition_div single_list">
                                            <table class="table table-bordered magic_table">
                                                <tr>
                                                    <td colspan="2" class="text-center">
                                                        <span style="padding: 0 6px;" class="pull-left btn btn-sm btn-circle btn-danger remove">X</span>
                                                        <span class="label label-success">Specific Conditions</span>
                                                        <span class="pull-right"><input type="checkbox" style="height: 14px; border: 1px solid lightgrey" class="form-control condition_check noUniform" name="general_check" value="1"></span>
                                                    </td>
                                                </tr>
                                                <tr class="ctr">
                                                    <td width="60%"><?= $this->Form->input('specific.0.conditions', ['type'=>'textarea', 'placeholder'=>'Conditions', 'rows'=>13, 'templates'=>['label'=>'', 'textarea' => '<div class="col-sm-12"><textarea class="multi form-control textareaStyle condition spec_condition noUniform" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);?></td>
                                                    <td width="40%">
                                                        <?= $this->Form->input('specific.0.offer_type', ['type'=>'text', 'style'=>'width:100%', 'placeholder'=>'Offer Type', 'templates'=>['label'=>'', 'input' => '<div class="col-sm-12 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);?>
                                                        <?= $this->Form->input('specific.0.offer_name', ['type'=>'text', 'style'=>'width:100%', 'placeholder'=>'Offer Name', 'templates'=>['label'=>'', 'input' => '<div class="col-sm-12 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);?>
                                                        <?= $this->Form->input('specific.0.offer_unit_name', ['type'=>'text', 'style'=>'width:100%', 'placeholder'=>'Offer Unit Name', 'templates'=>['label'=>'', 'input' => '<div class="col-sm-12 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);?>
                                                        <?= $this->Form->input('specific.0.amount_type', ['type'=>'text', 'style'=>'width:100%', 'placeholder'=>'Amount Type', 'templates'=>['label'=>'', 'input' => '<div class="col-sm-12 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);?>
                                                        <?= $this->Form->input('specific.0.payment_mode', ['type'=>'text', 'style'=>'width:100%', 'placeholder'=>'Payment Mode', 'templates'=>['label'=>'', 'input' => '<div class="col-sm-12 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);?>
                                                        <textarea name="specific[0][amount]" placeholder="Amount" rows="1" class="multi form-control" style="width: 100%"></textarea>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-left: 2px;">
                            <input type="button" class="btn btn-circle default yellow-stripe add_more" value="Add" />
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="tabbable-custom ">
                            <ul class="nav nav-tabs ">
                                <li class="active">
                                    <a data-toggle="tab" href="#tab_5_1" aria-expanded="true">Functions</a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#tab_5_2" aria-expanded="false">Items</a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#tab_5_3" aria-expanded="false">Awards</a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#tab_5_4" aria-expanded="false">Accounts</a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#tab_5_5" aria-expanded="false">Recipients</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab_5_1" class="tab-pane active">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Key Functions</span></td></tr>
                                        <?php foreach($functionArray as $func):?>
                                            <tr class="func_tr">
                                                <td width="2%"><input type="checkbox" class="func_check common_check" name="func_check" value="<?= $func?>"></td>
                                                <td class="func_td"><?= $func?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="tab_5_2" class="tab-pane">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Item Units</span></td></tr>
                                        <?php foreach($items as $item):?>
                                            <tr class="item_tr">
                                                <td width="2%"><input type="checkbox" class="item_check common_check" name="item_check" value="<?= $item?>"></td>
                                                <td class="award_td"><?= $item?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="tab_5_3" class="tab-pane">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Awards</span></td></tr>
                                        <?php foreach($awards as $award):?>
                                            <tr class="award_tr">
                                                <td width="2%"><input type="checkbox" class="award_check common_check" name="award_check" value="<?= $award->name?>"></td>
                                                <td class="award_td"><?= $award->name?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="tab_5_4" class="tab-pane">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Account Heads</span></td></tr>
                                        <?php foreach($accounts as $account):?>
                                            <tr class="acc_tr">
                                                <td width="2%"><input type="checkbox" class="acc_check common_check" name="acc_check" value="<?= $account?>"></td>
                                                <td class="acc_td"><?= $account?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="tab_5_5" class="tab-pane">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Recipients</span></td></tr>
                                        <?php foreach($recipients as $recipient):?>
                                            <tr class="acc_tr">
                                                <td width="2%"><input type="checkbox" class="recipient_check common_check" name="recipient_check" value="<?= $recipient?>"></td>
                                                <td class="recipient_td"><?= $recipient?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-center">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn yellow submit', 'style' => 'margin:20px']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<style>
    .textareaStyle{
        width:100%; border: 1px solid lightgrey; padding:7px 7px 6px 11px; font-size:14px; border-radius:4px;
    }
    .nav > li > a {
        display: block;
        padding: 10px 6px;
        position: relative;
    }
</style>

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

        $(document).on('click', '.send_to', function(){
            var obj = $(this);
            var mainArr = [];

            obj.closest('.tab-pane').find('.common_check').each(function( index ) {
                if($(this).prop('checked')){
                    mainArr.push($(this).val());
                }
            });

            mainArr.forEach(function(entry, k) {
                $('.condition_check').each(function(){
                    if($(this).is(":checked")){
                        if(k>0) {
                            $(this).closest('.magic_table').find('.condition').append(' ');
                        }
                        $(this).closest('.magic_table').find('.condition').append(entry);
                    }
                });
            });
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.conditionWrapper .specific_condition_div:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index+1);
                this.id = this.id.replace(/\d+/, index+1);
                this.value = '';
            }).end();
            $('.moreTable').append(html);
        });

        $(document).on('click', '.remove', function () {
            var obj=$(this);
            var count= $('.single_list').length;
            if(count > 1){
                obj.closest('.single_list').remove();
            }
        });
    });
</script>

