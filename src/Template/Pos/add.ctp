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
            <?= $this->Html->link(__('POs'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New PO') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New PO') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($po, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('customer_level_no', ['label'=>'Customer Level', 'empty'=>'Select', 'options'=>$administrativeLevels, 'class'=>'form-control level_no', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer_unit', ['type'=>'select', 'label'=>'Customer Location', 'empty'=>'Select', 'class'=>'form-control customer_unit', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 unit_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('credit_limit', ['label'=>'Credit Limit', 'type'=>'text', 'readonly', 'class'=>'form-control credit_limit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('cash_invoice_days', ['label'=>'Cash Invoice Days', 'type'=>'text', 'readonly', 'class'=>'numbersOnly form-control cash_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('invoice_type', ['type'=>'select', 'options'=>[1=>'Cash', 2=>'Credit'], 'label'=>'Invoice Type', 'empty'=>'Select', 'class'=>'form-control invoice_type', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('po_date', ['label'=>'PO date', 'type'=>'text', 'class'=>'form-control datepicker', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('customer_id', ['label'=>'Customer', 'empty'=>'Select', 'options'=>[], 'class'=>'form-control customer_id', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 customer_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('available_credit', ['label'=>'Available Credit', 'type'=>'text', 'readonly', 'class'=>'form-control available_credit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_invoice_days', ['label'=>'Credit Invoice Days', 'readonly', 'type'=>'text', 'class'=>'numbersOnly form-control credit_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('field_po_no', ['label'=>'Field PO No.', 'type'=>'text', 'class'=>'form-control', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
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
                                    <th>Cash Discount</th>
                                    <th>Item Net Total</th>
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
                <div class="row text-center">
                    <?= $this->Form->button(__('Save'), ['name'=>'save', 'class' => 'btn default green-stripe', 'style' => 'margin-top:20px; margin-bottom:20px']) ?>
                    <?= $this->Form->button(__('Forward'), ['name'=>'forward', 'class' => 'btn default yellow-stripe', 'style' => 'margin-top:20px; margin-bottom:20px']) ?>
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

        $(document).on('change', '.level_no', function () {
            var obj = $(this);
            var level = obj.val();
            $('.customer_level_no').html('<option>Select</option>');

            if(level>0 || level==0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Pos/getUnit")?>',
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
                    url: '<?= $this->Url->build("/Pos/getCustomer")?>',
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
                    url: '<?= $this->Url->build("/Pos/getCustomerDetail")?>',
                    data: {customer_id: customer_id},
                    success: function (data, status) {
                        var data = JSON.parse(data);
                        $('.credit_limit').val(data.credit_limit);
                        $('.available_credit').val(data.available_credit);
                        $('.cash_invoice_days').val(data.cash_invoice_days);
                        $('.credit_invoice_days').val(data.credit_invoice_days);
                    }
                });
            }else{
                $('.credit_limit').val(0);
                $('.available_credit').val(0);
                $('.cash_invoice_days').val(0);
                $('.credit_invoice_days').val(0);
            }
        });

        $(document).on('change', '.item', function () {
            var obj = $(this);
            var item_unit_id = obj.val();
            var invoice_type = $('.invoice_type').val();

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
                        url: '<?= $this->Url->build("/Pos/loadItem")?>',
                        data: {item_unit_id: item_unit_id, invoice_type:invoice_type},
                        success: function (data, status) {
                            $('.appendTr').append(data);
                        }
                    });
                } else {
                    toastr.info('Select Item & Invoice Type and try again!');
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

        $(document).on('keyup', '.item_quantity', function(){
            var item_quantity = parseFloat($(this).val());
            var unit_price = parseFloat($(this).closest('.itemTr').find('.unit_price').val());
            var item_cash_discount = parseFloat($(this).closest('.itemTr').find('.item_cash_discount').val());
            var item_net_total = item_quantity*unit_price-item_cash_discount;

            $(this).closest('.itemTr').find('.item_net_total').val(item_net_total);

            var total_amount = 0;
            $( ".item_net_total" ).each(function( index ) {
                total_amount = total_amount + parseFloat($(this).val());
            });
            $('.total_amount').html(total_amount);
            $('.total_amount_hidden').val(total_amount);
        });
    });

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