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
            <?= $this->Html->link(__('Approve POs'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Approve PO') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Approve PO') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($event['po'], ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <input type="hidden" name="po_id" value="<?= $event['po']['id']?>">

                <div class="row">
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('customer_level_no', ['label'=>'Customer Level', 'empty'=>'Select', 'options'=>$administrativeLevels, 'class'=>'form-control level_no', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer_unit', ['options'=>$administrativeUnits, 'value'=>$customerAdministrativeUnit, 'type'=>'select', 'label'=>'Customer Location', 'empty'=>'Select', 'class'=>'form-control customer_unit', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 unit_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('credit_limit', ['value'=>$event['po']['customer']['credit_limit']?$event['po']['customer']['credit_limit']:0, 'label'=>'Credit Limit', 'type'=>'text', 'readonly', 'class'=>'form-control credit_limit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        //echo $this->Form->input('total_due', ['label'=>'Total Due', 'type'=>'text', 'disabled', 'class'=>'form-control total_due', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('available_credit', ['value'=>0, 'label'=>'Available Credit', 'type'=>'text', 'readonly', 'class'=>'form-control available_credit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('invoice_type', ['type'=>'select', 'options'=>[1=>'Cash', 2=>'Credit'], 'label'=>'Invoice Type', 'empty'=>'Select', 'class'=>'form-control invoice_type', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('field_po_no', ['label'=>'Field PO No.', 'type'=>'text', 'class'=>'form-control', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('po_date', ['value'=>date('d-m-Y', $event['po']['po_date']), 'label'=>'PO date', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('customer_id', ['options'=>$customers, 'label'=>'Customer', 'empty'=>'Select', 'class'=>'form-control customer_id', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 customer_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        //echo $this->Form->input('address', ['label'=>'Customer Address', 'disabled', 'type'=>'textarea', 'rows'=>1,'class'=>'form-control address', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'textarea' => '<div class="col-sm-6"><textarea class="form-control address" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);
                        echo $this->Form->input('cash_invoice_days', ['value'=>$event['po']['customer']['cash_invoice_days']?$event['po']['customer']['cash_invoice_days']:0, 'label'=>'Cash Invoice Days', 'type'=>'text', 'readonly', 'class'=>'numbersOnly form-control cash_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_invoice_days', ['value'=>$event['po']['customer']['credit_invoice_days']?$event['po']['customer']['credit_invoice_days']:0, 'label'=>'Credit Invoice Days', 'readonly', 'type'=>'text', 'class'=>'numbersOnly form-control credit_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-md-11" style="margin: 2% 0 2% 0">
                        <?php
                        echo $this->Form->input('item_unit_id', ['label'=>'', 'empty'=>'Select', 'options'=>$itemArray, 'class'=>'select2me form-control item', 'templates'=>['label' =>'', 'select' => '<div id="container_{{name}}" class="col-sm-7 col-md-offset-3"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tbody class="appendTr">
                            <tr class="portlet box grey-silver" style="color: white">
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Bonus</th>
                                <th>Special Offer Bonus</th>
                                <th>Cash Discount</th>
                                <th>Item Net Total</th>
                                <th style="width: 2%;">Cancel</th>
                            </tr>
                            <?php
                            $total_amount = 0;
                            if(sizeof($event['po']['po_products'])>0):
                                foreach($event['po']['po_products'] as $item):
                                    $total_amount+=($item['product_quantity']*$itemUnitPriceArray[$item['item_unit_id']])-$item['instant_discount'];
                                    ?>
                                    <tr class="itemTr">
                                        <td><?= $itemArray[$item['item_unit_id']]?></td>
                                        <td><input type="text" name="detail[<?= $item['item_unit_id']?>][item_quantity]" class="form-control item_quantity" value="<?= $item['product_quantity']?>" /><input type="hidden" class="itemUnitId" name="itemUnitId[]" value="<?=$item['item_unit_id']?>"></td>
                                        <td><input type="text" name="detail[<?= $item['item_unit_id']?>][unit_price]" class="form-control unit_price" readonly value="<?= $itemUnitPriceArray[$item['item_unit_id']]?>" /></td>
                                        <td><input type="text" name="detail[<?= $item['item_unit_id']?>][item_bonus]" class="form-control item_bonus" readonly value="<?= $item['bonus_quantity']?>" /></td>
                                        <td><input type="text" name="detail[<?= $item['item_unit_id']?>][special_offer_item_bonus]" class="form-control special_offer_item_bonus" readonly value="<?= $item['special_offer_bonus_quantity']?>" /></td>
                                        <td><input type="text" name="detail[<?= $item['item_unit_id']?>][item_cash_discount]" class="form-control item_cash_discount" value="<?= $item['instant_discount']?>" /></td>
                                        <td>
                                            <input type="text" name="detail[<?= $item['item_unit_id']?>][item_net_total]" class="form-control item_net_total" readonly value="<?= $item['product_quantity']*$itemUnitPriceArray[$item['item_unit_id']]-$item['instant_discount']?>" />
                                            <input type="hidden" name="detail[<?= $item['item_unit_id']?>][offer_id]" class="form-control offer_id" readonly value="<?=$item['offer_id']?>" />
                                        </td>
                                        <td width="50px;"><span class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                <?php
                                endforeach;
                            endif;
                            ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tr>
                                <td colspan="1">Delivery Date: </td>
                                <td colspan="1"><input type="text" style="width: 120px" class="form-control delivery_date datepicker" name="delivery_date" value="<?= date('d-m-Y', $event['po']['delivery_date'])?>" /></td>
                                <td colspan="1">Total Amount:</td>
                                <td colspan="1"><label class="label label-danger total_amount"><?= $total_amount?></label><input type="hidden" name="total_amount_hidden" class="total_amount_hidden" value="<?= $total_amount?>"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row popContainer" style="display: none; width: 500px; max-height: 500px; overflow: auto;">
                </div>

                <div class="row text-center">
                    <span class="btn default red-stripe check_offer" style="margin-top:20px; margin-bottom:20px">Check Offer</span>
                    <?= $this->Form->button(__('Approve'), ['class' => 'btn default yellow-stripe', 'style' => 'margin-top:20px; margin-bottom:20px']) ?>
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

        $(document).on('click', '.check_offer', function(){
            var myArr = {};
            var i = 0;
            $( ".itemUnitId" ).each(function(index) {
                myArr[i] = {
                    "item_unit_id":$(this).val(),
                    "item_quantity":$(this).closest('.itemTr').find('.item_quantity').val(),
                    "net_total":$(this).closest('.itemTr').find('.item_net_total').val()
                }
                i++;
            });

            // check offer ajax
            var level_no = $('.level_no').val();
            var customer_unit = $('.customer_unit').val();
            var invoice_type = $('.invoice_type').val();
            var customer_id = $('.customer_id').val();

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ApprovePos/checkOffer")?>',
                data: {
                    item_array: myArr,
                    invoice_type:invoice_type,
                    customer_id:customer_id,
                    level_no: level_no,
                    customer_unit: customer_unit,
                },
                success: function (data, status) {
                    //console.log(data);
                    $(".popContainer").hide();
                    $('.popContainer').html('');
                    $('.popContainer').html(data);
                    $('.popContainer').show();
                }
            });
        });

        // close offer modal
        $(document).on("click",".crossSpan",function() {
            $(".offer_items").each(function(index){
                var offer_id = $(this).attr('data-offer');
                var offer_item_unit_id = $(this).val();

                $( ".itemUnitId" ).each(function(index) {
                    if($(this).val()==offer_item_unit_id){
                        $(this).closest('.itemTr').find('.offer_id').val(offer_id);
                    }
                });
            });

            $(".popContainer").hide();
        });

        $(document).on('change', '.level_no', function () {
            var obj = $(this);
            var level = obj.val();
            $('.customer_level_no').html('<option>Select</option>');

            if(level>0 || level==0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ApprovePos/getUnit")?>',
                    data: {level: level},
                    success: function (data, status) {
                        //console.log(data);
                        $('.unit_select').html('');
                        $('.unit_select').html(data);
                    }
                });
            }else{
                $('.customer_level_no').html('<option>Select</option>');
            }
        });

        $(document).on('change', '.customer_unit', function () {
            var obj = $(this);
            var unit = obj.val();
            $('.customer_id').html('<option>Select</option>');

            if(unit>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ApprovePos/getCustomer")?>',
                    data: {unit: unit},
                    success: function (data, status) {
                        //console.log(data);
                        $('.customer_select').html('');
                        $('.customer_select').html(data);
                    }
                });
            }else{
                $('.customer_id').html('<option>Select</option>');
            }
        });

        $(document).on('change', '.customer_id', function () {
            var obj = $(this);
            var customer_id = obj.val();

            if(customer_id>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Invoices/getCustomerDetail")?>',
                    data: {customer_id: customer_id},
                    success: function (data, status) {
                        var data = JSON.parse(data);
                        $('.credit_limit').val(data.credit_limit);
                        $('.available_credit').val(data.available_credit);
                        $('.cash_invoice_days').val(data.cash_invoice_days);
                        $('.credit_invoice_days').val(data.credit_invoice_days);
                        $('.address').html(data.address);
                        $('.address').show();
                    }
                });
            }else{
                $('.credit_limit').val(0);
                $('.available_credit').val(0);
                $('.cash_invoice_days').val(0);
                $('.credit_invoice_days').val(0);
                $('.address').html('');
                $('.address').hide();
            }
        });

        $(document).on('change', '.item', function () {
            var obj = $(this);
            var item_unit_id = obj.val();
            var invoice_type = $('.invoice_type').val();
            var customer_id = $('.customer_id').val();

            var myArr = [];
            $( ".itemUnitId" ).each(function( index ) {
                myArr.push($(this).val());
            });

            var uniqueArr = uniqueArray(myArr);
            uniqueArr.push(item_unit_id);
            var uniqueArrAfterSelection = uniqueArray(uniqueArr);

            if(uniqueArr.length != uniqueArrAfterSelection.length){
                toastr.info('Duplicate Item Not Allowed!');
            }else{
                if(item_unit_id>0 && invoice_type>0){
                    $.ajax({
                        type: 'POST',
                        url: '<?= $this->Url->build("/ApprovePos/loadItem")?>',
                        data: {item_unit_id: item_unit_id, invoice_type:invoice_type, customer_id:customer_id},
                        success: function (data, status) {
                            $('.appendTr').append(data);
                        }
                    });
                } else {
                    toastr.warning('Select Item, Invoice Type then try again!');
                }
            }
        });

        $(document).on('change', '.invoice_type', function(){
            var invoice_type = $(this).val();
            var available_credit = parseInt($('.available_credit').val());
            if(available_credit==0 && invoice_type==2){
                toastr.info('Available credit is 0');
                $(this).val(1);
            }
        });

        $(document).on('blur', '.item_quantity', function(){
            var obj = $(this);
            // bonus check
            var level_no = $('.level_no').val();
            var customer_unit = $('.customer_unit').val();
            var invoice_type = $('.invoice_type').val();
            var customer_id = $('.customer_id').val();
            var itemUnitId = $(this).closest('.itemTr').find('.itemUnitId').val();
            var item_quantity = parseFloat($(this).val());
            var unit_price = parseFloat($(this).closest('.itemTr').find('.unit_price').val());
            var net_total = item_quantity*unit_price;

            if(item_quantity>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ApprovePos/loadOffer")?>',
                    data: {
                        item_unit_id: itemUnitId,
                        invoice_type:invoice_type,
                        customer_id:customer_id,
                        item_quantity: item_quantity,
                        level_no: level_no,
                        customer_unit: customer_unit,
                        net_total: net_total
                    },
                    success: function (data, status) {
                        if(data.length>0){
                            var res = JSON.parse(data);
                            if(res.value>0){
                                if(res.offer_type=='product bonus'){
                                    obj.closest('.itemTr').find('.special_offer_item_bonus').val(res.value);
                                    obj.closest('.itemTr').find('.item_cash_discount').val(0);
                                }else{
                                    obj.closest('.itemTr').find('.item_cash_discount').val(res.value);
                                    obj.closest('.itemTr').find('.special_offer_item_bonus').val(0);
                                }

                                obj.closest('.itemTr').find('.item_bonus').val(res.bonus_quantity);
                                obj.closest('.itemTr').find('.offer_id').val(res.offer_id);

                                // redo
                                calculateNetTotal(item_quantity, obj);
                                calculateTotalAmount();
                            }else{
                                obj.closest('.itemTr').find('.item_cash_discount').val(0);
                                obj.closest('.itemTr').find('.item_bonus').val(0);
                                calculateNetTotal(item_quantity, obj);
                                calculateTotalAmount();
                            }
                        }else{
                            obj.closest('.itemTr').find('.item_cash_discount').val(0);
                            obj.closest('.itemTr').find('.item_bonus').val(0);
                            calculateNetTotal(item_quantity, obj);
                            calculateTotalAmount();
                        }
                    }
                });
            }

            // other calculations
            calculateNetTotal(item_quantity, obj);
            calculateTotalAmount();
        });

        $(document).on('change', '.invoice_type', function(){
            var obj = $(this);
            var invoice_type = obj.val();
            var cash_invoice_days = $('.cash_invoice_days').val();
            var credit_invoice_days = $('.credit_invoice_days').val();
            var customer_id = $('.customer_id').val();

            if(customer_id>0 && invoice_type>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ApprovePos/checkInvoiceTypeEligibility")?>',
                    data: {invoice_type:invoice_type, cash_invoice_days:cash_invoice_days, credit_invoice_days:credit_invoice_days, customer_id:customer_id},
                    success: function (data, status) {
                        if(data==0){
                            obj.val('');
                            toastr.error('Customer is not eligible for PO!');
                        }
                    }
                });
            }
        });

        $(document).on('click', '.remove', function () {
            var obj = $(this);
            var count = $('.itemTr').length;
            if (count > 1) {
                obj.closest('.itemTr').remove();
                // total amount recalculate
                calculateTotalAmount();
            }
        });
    });

    function calculateNetTotal(item_quantity, obj){
        var unit_price = parseFloat(obj.closest('.itemTr').find('.unit_price').val());
        var item_cash_discount = parseFloat(obj.closest('.itemTr').find('.item_cash_discount').val());
        var item_net_total = item_quantity*unit_price-item_cash_discount;

        if(item_net_total){
            obj.closest('.itemTr').find('.item_net_total').val(item_net_total);
        }else{
            obj.closest('.itemTr').find('.item_net_total').val(0);
        }
    }

    function calculateTotalAmount(){
        var total_amount = 0;
        $( ".item_net_total" ).each(function( index ) {
            if(parseFloat($(this).val())>0){
                total_amount += parseFloat($(this).val());
            }
        });
        if(total_amount){
            $('.total_amount').html(total_amount);
            $('.total_amount_hidden').val(total_amount);
        }else{
            $('.total_amount').html(0);
            $('.total_amount_hidden').val(0);
        }
    }

    function uniqueArray(arr) {
        var i,
            len = arr.length,
            out = [],
            obj = { };

        for (i = 0; i < len; i++) {
            obj[arr[i]] = 0;
        }
        for (i in obj) {
            out.push(i);
        }
        return out;
    }
</script>