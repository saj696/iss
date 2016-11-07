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
            <?= $this->Html->link(__('Special Offers'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Special Offer') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box yellow-casablanca">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Special Offer') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($specialOffer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('program_name', ['templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_start', ['label'=>'Period Start', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('program_period_end', ['label'=>'Period End', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label'=>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('invoice_type', ['empty'=>'Select', 'options'=>Configure::read('special_offer_invoice_types'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-7"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('invoicing', ['type' => 'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_invoicing'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('product_bonus_in_cash_sales', ['type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_product_bonus_in_cash_sales'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_note', ['type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_credit_note'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('previous_additional_circular', ['type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_previous_additional_circular'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>

                    <div class="col-lg-12">
                        <div class="portlet yellow box">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-cogs"></i>Product Wise Offers
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="row text-center">
                                    <div class="col-md-5" style="margin-left: 20px;">
                                        <?php echo $this->Form->input('item', ['options' => $itemsArray, 'style'=>'max-width: 100%', 'class'=>'form-control warehouse select2me', 'multiple', 'empty' => __('-- Select Item --'), 'templates'=>['label' => '']]);?>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="list" data-index_no="0">
                                                    <div class="itemWrapper">
                                                        <table class="table moreTable" style="margin-bottom: 0px;">
                                                            <tr class="item_tr single_list" style="border: none !important;">
                                                                <td><?php echo $this->Form->input('quantity', ['type' => 'text', 'style'=>'width: 100%', 'placeholder'=>'Qty', 'class'=>'form-control quantity numbersOnly', 'templates'=>['label' => '']]);?></td>
                                                                <td><button style="margin-top: 0px;" class="btn default green-stripe">Set Offer</button></td>
                                                                <td width="50px;"><span class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="button" class="btn btn-circle btn-success add_more" value="Add" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .table{
        border: 0px;
    }
</style>
<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on("focus",".datepicker", function() {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
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
    })
</script>