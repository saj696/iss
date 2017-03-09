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
            <?= $this->Html->link(__('Invoices'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Invoice') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('New Invoice') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($invoice, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('customer_level_no', ['label'=>'Customer Level', 'empty'=>'Select', 'options'=>$administrativeLevels, 'class'=>'form-control level_no', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer_unit', ['type'=>'select', 'label'=>'Customer Location', 'empty'=>'Select', 'class'=>'form-control customer_unit', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 unit_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer_id', ['label'=>'Customer', 'empty'=>'Select', 'options'=>[], 'class'=>'form-control customer_id', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 customer_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('address', ['label'=>'Customer Address', 'disabled', 'type'=>'textarea', 'rows'=>2,'class'=>'form-control address', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'textarea' => '<div class="col-sm-6"><textarea class="form-control address" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);
                        echo $this->Form->input('invoice_type', ['type'=>'select', 'options'=>[1=>'Cash', 2=>'Credit'], 'label'=>'Invoice Type', 'empty'=>'Select', 'class'=>'form-control invoice_type', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('invoice_date', ['label'=>'Invoice Date', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_limit', ['label'=>'Credit Limit', 'type'=>'text', 'readonly', 'class'=>'form-control credit_limit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('total_due', ['label'=>'Total Due', 'type'=>'text', 'disabled', 'class'=>'form-control total_due', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('available_credit', ['label'=>'Available Credit', 'type'=>'text', 'readonly', 'class'=>'form-control available_credit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);


                        echo $this->Form->input('cash_invoice_days', ['label'=>'Cash Invoice Days', 'type'=>'text', 'readonly', 'class'=>'numbersOnly form-control cash_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_invoice_days', ['label'=>'Credit Invoice Days', 'readonly', 'type'=>'text', 'class'=>'numbersOnly form-control credit_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        //echo $this->Form->input('field_po_no', ['label'=>'Field PO No.', 'type'=>'text', 'class'=>'form-control', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
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
                            </tbody>
                        </table>
                        <table class="table">
                            <tr>
                                <td colspan="1">Delivery Date: </td>
                                <td colspan="1"><input type="text" style="width: 120px" class="form-control delivery_date datepicker" name="delivery_date" value="" /></td>
                                <td colspan="1">Total Amount:</td>
                                <td colspan="1"><label class="label label-danger total_amount">0</label><input type="hidden" name="total_amount_hidden" class="total_amount_hidden" value=""></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row popContainer" style="display: none; width: 500px; max-height: 500px; overflow: auto;">
                </div>

                <div class="row text-center">
                    <span class="btn default red-stripe check_offer" style="margin-top:20px; margin-bottom:20px">Check Offer</span>
                    <?= $this->Form->button(__('Save'), ['name'=>'save', 'class' => 'btn default green-stripe', 'style' => 'margin-top:20px; margin-bottom:20px']) ?>
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
                    "item_quantity":$(this).closest('.itemTr').find('.item_quantity').val()
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
                url: '<?= $this->Url->build("/Invoices/checkOffer")?>',
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

        // close offer modal and apply discount
        $(document).on("click",".closeAndApply",function() {
            var check_offer_value = parseFloat($('.check_offer_value').val());
            if(check_offer_value>0){
                var sum_item_net_total = 0;
                $('.item_net_total').each(function(index) {
                    sum_item_net_total += parseFloat($(this).val());
                });

                $('.item_net_total').each(function(index) {
                    var proportion =  (parseFloat($(this).val())/sum_item_net_total*check_offer_value).toFixed(2);
                    $(this).closest('.itemTr').find('.item_cash_discount').val(proportion);
                    var final_net_total = parseFloat($(this).val())-proportion;
                    $(this).val(final_net_total);
                    calculateTotalAmount();
                });
            }
            $(".popContainer").hide();
        });

        $(document).on('change', '.level_no', function () {
            var obj = $(this);
            var level = obj.val();
            $('.customer_level_no').html('<option>Select</option>');

            if(level>0 || level==0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Invoices/getUnit")?>',
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
                    url: '<?= $this->Url->build("/Invoices/getCustomer")?>',
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
                        $('.total_due').val(data.total_due);
                        $('.credit_limit').val(data.credit_limit);
                        $('.available_credit').val(data.available_credit);
                        $('.cash_invoice_days').val(data.cash_invoice_days);
                        $('.credit_invoice_days').val(data.credit_invoice_days);
                        $('.address').html(data.address);
                    }
                });
            }else{
                $('.total_due').val(0);
                $('.credit_limit').val(0);
                $('.available_credit').val(0);
                $('.cash_invoice_days').val(0);
                $('.credit_invoice_days').val(0);
                $('.address').html('');
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
                        url: '<?= $this->Url->build("/Invoices/loadItem")?>',
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

            if(item_quantity>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Invoices/loadOffer")?>',
                    data: {
                        item_unit_id: itemUnitId,
                        invoice_type:invoice_type,
                        customer_id:customer_id,
                        item_quantity: item_quantity,
                        level_no: level_no,
                        customer_unit: customer_unit
                    },
                    success: function (data, status) {
//                        console.log(data);
                        if(data.length>0){
                            var res = JSON.parse(data);
                            console.log(res);
                            if(res.is_only_bonus==true){
                                return obj.closest('.itemTr').find('.item_bonus').val(res.bonus_quantity);
                            }else {
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

                                    // other calculations
                                    calculateNetTotal(item_quantity, obj);
                                    calculateTotalAmount();
                                }else{
                                    obj.closest('.itemTr').find('.item_cash_discount').val(0);
                                    obj.closest('.itemTr').find('.item_bonus').val(0);
                                    calculateNetTotal(item_quantity, obj);
                                    calculateTotalAmount();
                                }
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
                    url: '<?= $this->Url->build("/Invoices/checkInvoiceTypeEligibility")?>',
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