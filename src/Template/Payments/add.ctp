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
            <?= $this->Html->link(__('Payments'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Payment') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Payment') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($payment,['class' => 'form-horizontal','role'=>'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('parent_level',['options' => $parantsLevels, 'label'=>'Customers Parents Label','class'=> 'form-control level', 'empty'=>__('Select'), 'templates'=>['select' => '<div id="container_{{name}}" class="col-sm-9 levelContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('parent_unit', ['options' => [],'label'=>'Customer Parent Unit', 'empty' => __('Select'),'class'=> 'form-control unit','templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 unitContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer', ['options' => [],'label'=>'Customer', 'empty' => __('Select'),'class'=> 'form-control customer', 'templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 customerContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('due_invoice', ['options' => [],'label'=>'Due Invoice', 'empty' => __('Select'),'class'=> 'form-control dueInvoice', 'templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 dueInvoiceContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tbody class="appendTr">
                            <tr class="portlet box grey-silver" style="color: white">
                                <th>SN</th>
                                <th>Invoice Date</th>
                                <th>Net Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Current Payment</th>
                            </tr>
                            </tbody>
                        </table>
                        <?= $this->Form->button(__('Submit'),['class'=>'btn blue pull-right','style'=>'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function(){

        // Parent Level Onchange function
        $(document).on('change', '.level', function () {
            var obj = $(this);
            var level = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Payments/ajax/units")?>',
                data: {level: level},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear Unit Container
                    var $el = $('.unitContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Customer Container
                    var $el = $('.customerContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Invoice Container
                    var $el = $('.dueInvoiceContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Append Unit Container
                    $.each(data, function(key, value) {
                    $('.unitContainer select')
                        .append($("<option></option>")
                        .attr("value",key)
                        .text(value));
                    });
                }
            });
        });

        // Parent Unit Onchange function
        $(document).on('change', '.unit', function () {
            var obj = $(this);
            var unit = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Payments/ajax/customers")?>',
                data: {unit: unit},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear Customer Container
                    var $el = $('.customerContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Invoice Container
                    var $el = $('.dueInvoiceContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Append Customer Container
                    $.each(data, function(key, value) {
                        $('.customerContainer select')
                            .append($("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });
                }
            });
        });

        // Customer Onchange function
        $(document).on('change', '.customer', function () {
            var obj = $(this);
            var customer = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Payments/ajax/dueInvoice")?>',
                data: {customer: customer},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear Invoice Container
                    var $el = $('.dueInvoiceContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Append Invoice Container
                    $.each(data, function(key, value) {
                        $('.dueInvoiceContainer select')
                            .append($("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });

                }
            });
        });

        // Due invoice Onchange function and add table row
        $(document).on('change', '.dueInvoice', function () {
            var obj = $(this);
            var dueInvoice = obj.val();
            console.log(dueInvoice);
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Payments/ajax/paymentTable")?>',
                data: {dueInvoice: dueInvoice},
                success: function (data, status) {
                    $('.appendTr').append(data);
                }
            });
        });

    //End all funciton

    });


</script>