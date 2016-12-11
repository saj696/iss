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
            <?= $this->Html->link(__('Formulation Logs'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Formulation Log') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Formulation Log') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($formulationLog,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('warehouse_id',['options' => $warehouseNames, 'empty'=>__('Select')]);
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
                                        <td style="width:25%"><?php echo $this->Form->input('details.0.item_unit', ['options' => $itemUnit,  'class' => 'form-control itemUnit', 'empty' => __('Select'),  'required','templates' => ['label' => '']]); ?></td>
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
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
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
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function (){

//      allow only float value
        $(document).on('keyup','.numbersOnly',function(event){
            this.value = this.value.replace(/[^0-9\.]/g, '');
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

            var myArr = [];
            $( ".itemUnit" ).each(function( index ) {
                myArr.push($(this).val());
            });

            var uniqueArr = uniqueArray(myArr);
            uniqueArr.push(itemUnit);
            var uniqueArrAfterSelection = uniqueArray(uniqueArr);

console.log(uniqueArr.length);
console.log(uniqueArrAfterSelection.length);

            if(uniqueArr.length != uniqueArrAfterSelection.length){
                toastr.info('Duplicate Item Not Allowed!');
            }
            else{
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/FormulationLogs/stock")?>',
                    data: {itemUnit: itemUnit},
                    success: function (data, status) {
                        var quantity = JSON.parse(data);
                        $('.stock').val(quantity.quantity);
                        $('.stock').attr('unitType',quantity.unitType);
                        $('.stock').attr('convertQuantity',quantity.convertedQuantity);
                    }
                });
            }
        });

//        input amount value
        $(document).on('keyup','.inputAmount',function(){
            var obj = $(this);
            var amount = obj.val();
            var unitType = $('.stock').attr("unitType");
            var convertQuantity = $('.stock').attr("convertQuantity");

            if( unitType == 1 && convertQuantity == 0){
//                gram
                var final = amount/1000;
                $('.amount').val(final);
                console.log(final);
            }
            else if( unitType == 1 && convertQuantity != 0){
//                gram
                var final = (amount*convertQuantity)/1000;
                $('.amount').val(final);
                console.log(final);
            }

            else if( (unitType == 2 && convertQuantity == 0) || (unitType == 2 && convertQuantity != 0)){
//                kg
//                var final = amount
                $('.amount').val(amount);
                console.log(amount);
            }
//            else if( unitType == 2 && convertQuantity != 0){
////                kg
//                console.log(final);
//            }

            else if( unitType == 3 && convertQuantity == 0){
//                ml
                var final = amount/1000;
                $('.amount').val(final);
                console.log(final);
            }
            else if( unitType == 3 && convertQuantity != 0){
//                ml
                var final = (amount*convertQuantity)/1000;
                $('.amount').val(final);
                console.log(final);
            }

            else if( (unitType == 4 && convertQuantity == 0) || (unitType == 4 && convertQuantity != 0)){
//                liter
//                var final = amount
                $('.amount').val(amount);
                console.log(amount);
            }
            else {
//                each
                console.log(final);
            }

        });

//        output generation
        $(document).on('click','.formulateOutpute',function(){
            var abc = "test";
            console.log(abc);
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