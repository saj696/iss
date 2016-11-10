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
                        echo $this->Form->input('invoicing', ['default'=>1, 'type' => 'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_invoicing'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('product_bonus_in_cash_sales', ['default'=>1, 'type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_product_bonus_in_cash_sales'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_note', ['default'=>1, 'type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_credit_note'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('previous_additional_circular', ['default'=>1, 'type'=>'radio', 'class'=>'radio-inline form-control', 'options' => Configure::read('special_offer_previous_additional_circular'), 'templates'=>['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>

                    <div class="productWise_list" data-index_no="0">
                        <div class="productWiseWrapper">
                            <table class="moreProductWiseTable" style="margin-bottom: 0px; width: 100%">
                                <tr class="productWise_tr single_productWise_list" style="border: none !important;">
                                    <td>
                                        <div class="col-lg-12">
                                            <div class="portlet yellow box">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <i class="fa fa-cogs"></i>Product Wise Offers
                                                    </div>
                                                    <div class="pull-right">
                                                        <span style="margin-top: 20%" class="btn btn-sm btn-circle btn-danger remove_productWise pull-right">X</span>
                                                    </div>
                                                </div>
                                                <div class="portlet-body">
                                                    <div class="row text-center">
                                                        <div class="col-md-5" style="margin-left: 20px;">
                                                            <?php echo $this->Form->input('main[0][items][]', ['options' => $itemsArray, 'style'=>'max-width: 100%', 'class'=>'form-control parent', 'multiple', 'empty' => __('-- Select Item --'), 'templates'=>['label' => '']]);?>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="list" data-index_no="0">
                                                                        <div class="itemWrapper">
                                                                            <table class="moreTable" style="margin-bottom: 0px;">
                                                                                <tr class="offer_tr single_list" style="border: none !important;">
                                                                                    <td><?php echo $this->Form->input('main[0][offer][0][quantity]', ['type' => 'text', 'style'=>'width: 100%;', 'placeholder'=>'Qty', 'class'=>'parent offer_add form-control quantity numbersOnly', 'templates'=>['label' => '']]);?></td>
                                                                                    <td>
                                                                                        <span style="margin-top: -20%;" class="btn default purple-stripe load_offer">Set Offer</span>
                                                                                        <div class="row popContainerOffer" style="display: none; width: 500px;">
                                                                                            <table class="table table-bordered" style="margin-bottom: 0px;">
                                                                                                <tr><td><span class="label label-info">Offer Detail</span></td></tr>
                                                                                                <tr class="offer_detail_tr">
                                                                                                    <td style="padding: 0px;">
                                                                                                        <div class="offer_list" data-index_no="0">
                                                                                                            <div class="offerWrapper">
                                                                                                                <table class="moreOfferTable" style="margin-bottom: 0px; width: 100%">
                                                                                                                    <tr class="offer_detail_tr single_offer_list" style="border: none !important;">
                                                                                                                        <td>
                                                                                                                            <?php
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_type]', ['label'=>'Offer Type', 'options'=>Configure::read('special_offer_types'), 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add special_offer_types', 'empty' => __('Select'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','select' => '<div id="container_{{name}}" class="col-sm-5"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][payment_age]', ['label'=>'Payment Age', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden cash_discount input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][payment_deadline]', ['label'=>'Payment Deadline', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden cash_discount input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][amount]', ['label'=>'Amount', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden cash_discount input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_product]', ['label'=>'Offer Product', 'options'=>$itemsArray, 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'empty' => __('Select'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','select' => '<div id="container_{{name}}" class="col-sm-5"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden product input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_product_quantity]', ['label'=>'Product Quantity', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden product input {{type}}{{required}}">{{content}}</div>']]);

                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_trip]', ['label'=>'Offer Trip', 'options'=>$trips, 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'empty' => __('Select'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','select' => '<div id="container_{{name}}" class="col-sm-5"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden trip input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][cash_equivalent]', ['label'=>'Cash Equivalent', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden trip input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][credit_equivalent]', ['label'=>'Credit Equivalent', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group hidden trip input {{type}}{{required}}">{{content}}</div>']]);

                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][season_start]', ['label'=>'Season Start', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="hidden seasonal form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][season_end]', ['label'=>'Season End', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="hidden seasonal form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][payment_start]', ['label'=>'Payment Start', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="hidden seasonal form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][payment_end]', ['label'=>'Payment End', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="hidden seasonal form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][bonus]', ['label'=>'Bonus (%)', 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="hidden seasonal form-group input {{type}}{{required}}">{{content}}</div>']]);

                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_customer_type]', ['label'=>'Offer Customer Type', 'options'=>Configure::read('special_offer_detail_customer_type'), 'style'=>'margin-top:6px;', 'class'=>'form-control parent offer_add offer_detail_add', 'empty' => __('Select'), 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','select' => '<div id="container_{{name}}" class="col-sm-5"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_adjusted_from]', ['label'=>'Adjust From', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 0px;" class="form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            echo $this->Form->input('main[0][offer][0][detail][0][offer_adjusted_to]', ['label'=>'Adjust To', 'style'=>'margin-top:6px;', 'class'=>'datepicker form-control parent offer_add offer_detail_add', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-5 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>', 'inputContainer' => '<div style="margin-bottom: 10px;" class="form-group input {{type}}{{required}}">{{content}}</div>']]);
                                                                                                                            ?>
                                                                                                                            <span style="margin-top: -25%; margin-right: 5%" class="btn btn-sm btn-circle btn-danger remove_offer pull-right">X</span>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </div>
                                                                                                            <div class="row text-center" style="margin-bottom: 15px;">
                                                                                                                <input type="button" class="btn btn-circle btn-success add_more_offer" value="Add" />
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr style="margin-top: 10px; border-top: 0px;">
                                                                                                    <td colspan="2" class="text-center" style="border: 0px;">
                                                                                                        <label class="btn default red-stripe crossSpan"><?= __('Ok')?></label>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td width="50px;"><span style="margin-top: -25%;" class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <input type="button" class="btn btn-circle default green-stripe add_more" value="Add" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="row text-center" style="margin-bottom: 15px;">
                                <input type="button" class="btn btn-circle btn-success add_more_productWise" value="Add" />
                            </div>
                        </div>
                        <div class="row text-center" style="margin-bottom: 20px;">
                            <?= $this->Form->button(__('Submit'), ['class' => 'btn green', 'style' => 'margin-top:20px']) ?>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

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

        $(document).on("click", ".load_offer", function(event)
        {
            $(".popContainerOffer").hide();
            $(this).closest('td').find('.popContainerOffer').show();
        });

        $(document).on("click",".crossSpan",function()
        {
            $(this).closest('.offer_tr').find(".popContainerOffer").hide();
        });

        $(document).on("change", ".special_offer_types", function(){
            var offer_type = $(this).val();
            if(offer_type==1) {
                $(this).closest('.offer_detail_tr').find('.cash_discount').removeClass('hidden');
                $(this).closest('.offer_detail_tr').find('.product').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.trip').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.seasonal').addClass('hidden');
            } else if(offer_type==2) {
                $(this).closest('.offer_detail_tr').find('.cash_discount').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.product').removeClass('hidden');
                $(this).closest('.offer_detail_tr').find('.trip').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.seasonal').addClass('hidden');
            } else if(offer_type==3) {
                $(this).closest('.offer_detail_tr').find('.cash_discount').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.product').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.seasonal').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.trip').removeClass('hidden');
            } else if(offer_type==4) {
                $(this).closest('.offer_detail_tr').find('.cash_discount').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.product').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.trip').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.seasonal').removeClass('hidden');
            } else {
                $(this).closest('.offer_detail_tr').find('.cash_discount').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.product').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.trip').addClass('hidden');
                $(this).closest('.offer_detail_tr').find('.seasonal').addClass('hidden');
            }
        });

        $(document).on('click', '.add_more', function () {
            $(".popContainerOffer").hide();
            var obj = $(this);
            var index = obj.closest('.portlet-body').find('.list').data('index_no');
            obj.closest('.portlet-body').find('.list').data('index_no', index + 1);

            var html = $(this).closest('.portlet-body').find('.offer_tr:last').clone().find('.offer_add').each(function () {
//            var html = $('.itemWrapper .offer_tr:last').clone().find('.offer_add').each(function () {
                var new_index = index+1;
                var i = 0;

                $(this).attr('name', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 2) ? "["+new_index+"]" : match;
                }));
                $(this).attr('id', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 2) ? "["+new_index+"]" : match;
                }));
                this.value = '';

            }).end();

            obj.closest('.portlet-body').find('.moreTable').append(html);
        });

        $(document).on('click', '.remove', function () {
            var obj=$(this);
            var count= $(this).closest('.itemWrapper').find('.single_list').length;
            if(count > 1){
                obj.closest('.single_list').remove();
            }
        });

        $(document).on('click', '.add_more_offer', function () {
            var obj = $(this);
            var index = obj.closest('.offer_detail_tr').find('.offer_list').data('index_no');
            obj.closest('.offer_detail_tr').find('.offer_list').data('index_no', index + 1);

            var html = $(this).closest('.offer_list').find('tr:last').clone().find('.offer_detail_add').each(function () {
//            var html = $('.offerWrapper .offer_detail_tr:last').clone().find('.offer_detail_add').each(function () {
                var new_index = index+1;
                var i = 0;

                $(this).attr('name', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 3) ? "["+new_index+"]" : match;
                }));
                $(this).attr('id', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 3) ? "["+new_index+"]" : match;
                }));
                this.value = '';
            }).end();

            obj.closest('.offer_detail_tr').find('.moreOfferTable').append(html);
        });

        $(document).on('click', '.remove_offer', function () {
            var obj=$(this);
            var count= $(this).closest('.offerWrapper').find('.single_offer_list').length;
            if(count > 1){
                obj.closest('.single_offer_list').remove();
            }
        });

        $(document).on('click', '.add_more_productWise', function () {
            var index = $('.productWise_list').data('index_no');
            $('.productWise_list').data('index_no', index + 1);
            var html = $(this).closest('.productWise_list').find('.productWise_tr:last').clone().find('.parent').each(function () {
//            var html = $('.productWiseWrapper .productWise_tr:last').clone().find('.parent').each(function () {
                var new_index = index+1;
                var i = 0;

                $(this).attr('name', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 1) ? "["+new_index+"]" : match;
                }));
                $(this).attr('id', this.name.replace(/\[\d+\]/g,function (match, pos, original) {
                    i++;
                    return (i == 1) ? "["+new_index+"]" : match;
                }));
                this.value = '';
            }).end();

            $('.moreProductWiseTable').append(html);
        });

        $(document).on('click', '.remove_productWise', function () {
            var obj=$(this);
            var count= $('.single_productWise_list').length;
            if(count > 1){
                obj.closest('.single_productWise_list').remove();
            }
        });
    })
</script>