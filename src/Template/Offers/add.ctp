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

            <div class="portlet-body main_div">
                <?= $this->Form->create($offer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7">
                        <div class="general condition_div">
                            <table class="table table-bordered">
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
                                <div class="itemWrapper">
                                    <div class="moreTable">
                                        <div class="specific_condition_div single_list">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td colspan="2" class="text-center">
                                                        <span style="padding: 0 6px;" class="pull-left btn btn-sm btn-circle btn-danger remove">X</span>
                                                        <span class="label label-success">Specific Conditions</span>
                                                        <span class="pull-right"><input type="checkbox" style="height: 14px;" class="form-control condition_check noUniform" name="general_check" value="1"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="70%"><?= $this->Form->input('general_conditions', ['type'=>'textarea', 'placeholder'=>'Conditions', 'style'=>'width:100%', 'rows'=>6, 'class'=>'form-control condition', 'templates'=>['label'=>'', 'textarea' => '<div class="col-sm-12"><textarea class="form-control condition" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);?></td>
                                                    <td width="30%"><?= $this->Form->input('general_conditions', ['type'=>'textarea', 'placeholder'=>'Offers', 'style'=>'width:100%', 'rows'=>6, 'class'=>'form-control offers', 'templates'=>['label'=>'', 'textarea' => '<div class="col-sm-12"><textarea class="form-control" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);?></td>
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
                            </ul>
                            <div class="tab-content">
                                <div id="tab_5_1" class="tab-pane active">
                                    <table class="table table-bordered">
                                        <tr><td colspan="3" class="text-center"><span class="label label-info">Key Functions</span></td></tr>
                                        <?php foreach($functionArray as $func):?>
                                            <tr class="func_tr">
                                                <td width="2%"><input type="checkbox" class="func_check" name="func_check" value="<?= $func?>"></td>
                                                <td class="func_td"><?= $func?></td>
                                            </tr>
                                        <?php endforeach;?>
                                        <tr>
                                            <td colspan="2" class="text-center"><span class="btn btn-circle default yellow-stripe send_to">Send</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="tab_5_2" class="tab-pane">

                                </div>
                                <div id="tab_5_3" class="tab-pane">

                                </div>
                                <div id="tab_5_4" class="tab-pane">

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

<script>
    $(document).ready(function(){
        $(document).on('keyup', '.condition', function(){
//            var str = $('textarea.condition').val();
//            toastr.info('credit_closing_percentage (period, payment)');
        });

        $(document).on('click', '.send_to', function(){
            var obj = $(this);
            var funcArr = [];

            $('.func_check').each(function( index ) {
                if($(this).prop('checked')){
                    funcArr.push($(this).val());
                }
            });

            $('.condition_check').each(function(){
                if($(this).prop('checked')){
                    $(this).closest('.condition_div').find('.condition').html('');
                }
            });

            funcArr.forEach(function(entry, k) {
                $('.condition_check').each(function(){
                    if($(this).prop('checked')){
                        if(k>0) {
                            $(this).closest('.condition_div').find('.condition').append(' ');
                        }
                        $(this).closest('.condition_div').find('.condition').append(entry);
                    }
                });
            });
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .specific_condition_div:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index+1);
                this.id = this.id.replace(/\d+/, index+1);
                this.value = '';
            }).end();
            $.uniform.update();
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

