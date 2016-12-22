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
            <?= $this->Html->link(__('Formulation'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Formulation') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Formulation') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($stockLog,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('warehouse_id',['options' => $warehouseNames, 'empty'=>__('Select'), 'class' => 'form-control wareHouseTrigger']);
                        echo $this->Form->input('Item',['options' => [], 'empty'=>__('Select'), 'class' =>'form-control item', 'required']);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <h3 class="forInput">Input</h3>
                                <table class="table table-bordered">
                                    <tr>
                                        <th><?= __('Item Unit') ?></th>
                                        <th><?= __('Stock') ?></th>
                                        <th><?= __('Input Amount') ?></th>
                                        <th><?= __('Amount (KG/L)') ?></th>
                                        <th><?= __('Remove') ?></th>
                                    </tr>
                                    <tr class="item_tr single_list" data-formulation-id="">
                                        <td style="width:25%"> <?php echo $this->Form->input('details.0.item_unit', ['options' => [],  'class' => 'form-control itemUnit', 'empty' => __('Select'),  'required','templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('details.0.stock', ['type' => 'text', 'readonly' ,'class' => 'form-control stock','templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('details.0.amount', ['type' => 'text', 'class' => 'form-control numbersOnly inputAmount', 'unitType'=> 0, 'required', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('details.0.amount_unit', ['type' => 'text','class' => 'form-control amount', 'readonly', 'templates' => ['label' => '']]); ?></td>
                                        <td><span class="btn btn-sm btn-circle btn-danger remove">X</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <?php echo $this->Form->input('total_amount',['type' => 'text', 'label'=>' ' ,'class' =>'form-control sumResult', 'readonly']); ?>
                        <input type="button" class="btn btn-circle btn-warning add_more addBtnFormulation" value="Add"/>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="formulateArrow"><span class="formulateOutpute"><i class="fa fa-arrow-down"></i></span></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h3>Output</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <?php echo $this->Form->input('item_name',['type' => 'text', 'class' => 'form-control outputInputName','readonly', 'label' => 'Item']); ?>
                            <input type="hidden" name="item_id" class="output_item_id" value="">
                        </div>
                        <div class="col-md-4">
                            <?php echo $this->Form->input('bulk_name',['type' => 'text', 'class' => 'form-control outputbulk','readonly', 'label' => ' ']); ?>
                            <input type="hidden" name="manufacture_unit_id" class="output_manufacture_unit_id" value="">
                        </div>
                        <div class="col-md-4">
                            <?php echo $this->Form->input('output_result',['type' => 'text', 'class' => 'form-control outputresult','readonly', 'label' => 'Result']); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-8">
                        <?php    echo $this->Form->input('output_gain',['type' => 'text', 'class' => 'form-control outputgain', 'label' => 'Gain']); ?>
                        <?= $this->Form->button(__('Submit'),['class'=>'btn blue pull-right submitCheck','style'=>'margin:20px']) ?>
                    </div>

                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function (){
        //      item load when warehouse trigger
        $(document).on('change', '.wareHouseTrigger', function(){
            var obj = $(this);
            var warehouse = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/FormulationLogs/wareHouseTrigger")?>',
                data: {warehouse: warehouse},
                dataType: 'json',
                success: function (data, status) {
                    //   Clear inputitem Container
                    var $el = $('.item');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   append inputitem Container
                    $.each(data, function(key, value) {
                        $('.item')
                            .append($("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });
                }
            });
        });


//      show item unit based on item
        $(document).on('change', '.item', function(){
            var obj = $(this);
            var item = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/FormulationLogs/item")?>',
                data: {item: item},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear inputitem Container
                    var $el = $('.itemUnit');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   append inputitem Container
                    $.each(data, function(key, value) {
                        $('.itemUnit')
                            .append($("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });
                }
            });
        });

//      allow only float value
        $(document).on('keyup','.numbersOnly',function(event){
            this.value = this.value.replace(/[^0-9]/g, '');
        });


//      Add more function
        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
            }).end();

            $('.table').append(html);
        });

//        Remove single row
        $(document).on('click', '.remove', function () {
            var obj = $(this);
            var count = $('.single_list').length;
            if (count > 1) {
                obj.closest('.single_list').remove();
            }
        });

//        Add stock quantity value
        $(document).on('change','.itemUnit', function() {
            var obj = $(this);
            var itemUnit = obj.val();

            if (itemUnit)
            {
                var myArr = [];
                $( ".itemUnit" ).each(function( index ) {
                    myArr.push($(this).val());
                });

                var uniqueArr = uniqueArray(myArr);
                uniqueArr.push(itemUnit);
                var uniqueArrAfterSelection = uniqueArray(uniqueArr);

                if(myArr.length != uniqueArrAfterSelection.length){
                   // toastr.info('Duplicate Item Not Allowed!');
                   alert('Duplicate Item Not Allowed!');
                }
                else{
                    $(this).closest('.item_tr').find('.stock').val('');
                    $(this).closest('.item_tr').find('.amount').val('');
                    $(this).closest('.item_tr').find('.inputAmount').val('');
                    $.ajax({
                        type: 'POST',
                        url: '<?= $this->Url->build("/FormulationLogs/stock")?>',
                        data: {itemUnit: itemUnit},
                        success: function (data, status) {
                            var quantity = JSON.parse(data);
                            obj.closest('.item_tr').find('.stock').val(quantity.quantity);
                            obj.closest('.item_tr').find('.stock').attr('unitType',quantity.unitType);
                            obj.closest('.item_tr').find('.stock').attr('convertQuantity',quantity.convertedQuantity);
                        }
                    });
                }
            }
        });

//        input amount value
        $(document).on('keyup','.inputAmount',function(){
            var obj = $(this);
            var amount = obj.val();
            var stockVal = obj.closest('.item_tr').find('.stock').val();
            var unitType = obj.closest('.item_tr').find('.stock').attr("unitType");
            var convertQuantity = obj.closest('.item_tr').find('.stock').attr("convertQuantity");

            if(parseFloat(amount)>parseFloat(stockVal)){
                alert('Out Of Stock!');
                return false;
            }

            if( unitType == 1 && convertQuantity == 0){
//                gram converted quantity 0
                var final = amount/1000;
                obj.closest('.item_tr').find('.amount').val(final);
            }
            else if( unitType == 1 && convertQuantity != 0){
//                gram converted quantity !0
                var final = (amount*convertQuantity)/1000;
                obj.closest('.item_tr').find('.amount').val(final);
            }

            else if (unitType == 2 && convertQuantity == 0 || unitType == 2 && convertQuantity == null) {
//                kg converted quantity 0
	 	        var final = amount * 1;
                obj.closest('.item_tr').find('.amount').val(final);
            }
            else if(unitType == 2 && convertQuantity != 0){
 //                kg converted quantity !0
                var final = amount*convertQuantity;
                obj.closest('.item_tr').find('.amount').val(final);
            }

            else if( unitType == 3 && convertQuantity == 0){
//                ml converted quantity 0
                var final = amount/1000;
                obj.closest('.item_tr').find('.amount').val(final);
            }
            else if( unitType == 3 && convertQuantity != 0){
//                ml converted quantity not 0
                var final = (amount*convertQuantity)/1000;
                obj.closest('.item_tr').find('.amount').val(final);
            }

            else if(unitType == 4 && convertQuantity == 0){
//                liter converted quantity 0
		        var final = amount * 1;
                obj.closest('.item_tr').find('.amount').val(final);
            }
            else if(unitType == 4 && convertQuantity != 0){
//                liter converted quantity !0
                var final = amount*convertQuantity;
                obj.closest('.item_tr').find('.amount').val(final);
            }

            else {
//                each
            }

//

            //        Sum Result
            var sumResult = 0;
            $('.item_tr').each(function(){
                var eachRowValue = parseFloat($(this).find('.amount').val());

                sumResult += eachRowValue;

            });
            $('.sumResult').val(sumResult);
        });

//        output generation
        $(document).on('click','.formulateOutpute',function(){

            var itemVal = $('.item').val();
            var totalAmount = $('.sumResult').val();
            $.ajax({
                type: 'POST', 
                url: '<?= $this->Url->build("/FormulationLogs/outputGeneration")?>',
                data: {itemVal: itemVal, totalAmount: totalAmount},
                success: function (data, status) {

                	if(data == 0){
                		alert("First Produce A production Rules");
                	}
                	else{
                  		var result = JSON.parse(data);
                   		$('.outputInputName').val(result.itemName);
                  		$('.output_item_id').val(result.itemId);
                   		$('.outputbulk').val(result.bulkName);
                   		$('.output_manufacture_unit_id').val(result.bulkid);
                   		$('.outputresult').val(result.resultName);
            		}
                }
            });
        });

        $(document).on('click','.submitCheck',function(){
            var outputItemCheck = $('.outputInputName').val();
            if(outputItemCheck == '')
            {
            	alert('You Have To Formulate First!');
            	return false;
               // toastr.info('You Have To Formulate First!');
               // return false;
            }

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