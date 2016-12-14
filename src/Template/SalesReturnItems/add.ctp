<?php
use Cake\Core\Configure;

?>
<style>

</style>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Sales Returned'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Add Sales Return Item') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add Sales Return Item') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>

            <div class="portlet-body">
                <?= $this->Form->create($salesReturnItems, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php

                        echo $this->Form->input('SalesReturn.parent_level', ['options' => $parentLevels, 'label' => 'Customers Parents Level', 'class' => 'form-control level', 'empty' => __('Select')]);
                        echo $this->Form->input('SalesReturn.parent_unit', ['options' => [], 'label' => 'Parent Unit', 'empty' => __('Select'), 'class' => 'form-control parent-unit']);
                        echo $this->Form->input('SalesReturn.customer', ['options' => [], 'label' => 'Customer', 'empty' => __('Select'), 'class' => 'form-control customer', 'required' => true]);
                        echo $this->Form->input('SalesReturn.warehouse_id', ['required' => true, 'options' => $warehouses, 'label' => 'WareHouses', 'class' => 'form-control warehouses', 'empty' => __('Select')]);
                        echo $this->Form->input('SalesReturn.grand_total', ['value' => '', 'type' => 'hidden', 'id' => 'grand_total']);

                        ?>
                    </div>
                </div>
                <div class="row">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable" id="SalesTable">
                                    <tr>
                                        <th><?= __('Item') ?></th>
                                        <th><?= __('Unit') ?></th>
                                        <th><?= __('Returned Quantity') ?></th>
                                        <th><?= __('Unit Price') ?></th>
                                        <th><?= __('Net Total') ?></th>
                                        <th><?= __('Expire Date') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width:200px">
                                            <?php echo $this->Form->input('SalesReturnItems.0.item_id', ['required' => 'required', 'class' => 'form-control items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td>
                                            <?php echo $this->Form->input('SalesReturnItems.0.manufacture_unit_id', ['options' => $units, 'required' => 'required', 'class' => 'form-control units', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('SalesReturnItems.0.quantity', ['type' => 'text', 'required' => 'required', 'class' => 'form-control quantity numbersOnly', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('SalesReturnItems.0.unit_price', ['type' => 'text', 'required' => 'required', 'class' => 'form-control unit-price numbersOnly', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('SalesReturnItems.0.net_total', ['type' => 'text', 'required' => 'required', 'class' => 'form-control net-total numbersOnly', 'readonly' => true, 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('SalesReturnItems.0.expire_date', ['type' => 'text', 'required' => 'required', 'class' => 'form-control datepicker expire-date', 'readonly' => false, 'templates' => ['label' => '']]); ?></td>

                                        <td><span
                                                class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-12 col-md-offset-7">
                                <div class="col-md-12">
                                    <div class="col-md-1">
                                    </div>
                                    <div class="col-md-11" id="total-value-for-view">

                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
                    </div>
                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Save'), ['name' => 'for_save', 'id' => 'save', 'class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>
<script>
    $(document).on("keyup", ".numbersOnly", function (event) {
        if (!$.isNumeric(this.value)) {
            $(this).val(
                function (index, value) {
                    return value.substr(0, value.length - 1);
                })
        }
    });

    $(document).on("focus", ".datepicker", function () {
        $(this).removeClass('hasDatepicker').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
    var total_value_for_view = 0;
    var item_id;
    var warehouse_id;
    $(document).on('click', '.add_more', function () {
        var index = $('.list').data('index_no');
        $('.list').data('index_no', index + 1);
        var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
            if (this.type == 'select-one') {
                var options_select_box = $(this).html();
                $(this).html(options_select_box);
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
            }
            else {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
            }
        }).end();

        $('.moreTable').append(html);
    });

    $(document).on('click', '.remove', function () {
        var obj = $(this);
        var count = $('.single_list').length;
        if (count > 1) {
            obj.closest('.single_list').remove();
        }
    });
    var show_item_for_depot_user = '<?php echo $show_item_for_depot_user;?>'


    $(document).on('change', '.warehouses', function () {
        warehouse_id = $(this).val();
        if (show_item_for_depot_user == 1) {
            if (warehouse_id == '' || warehouse_id == undefined) {
                $('.items').empty();
                $('.items').append($('<option>').text("Select"));
                return alert("Please Select WareHouse");
            }
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: '<?= $this->Url->build("/SalesReturnItems/ajax/depotItems")?>',
                data: {warehouse_id: warehouse_id},
                success: function (response, status) {
                    $('.items').empty();
                    $('.items').append($('<option>').text("Select"));
                    $.each(response, function (key, value) {
                        if (key == null || value == null) {
                            $('.items').empty();
                            $('.items').append($('<option>').text("Select"));
                        }
                        else {
                            $('.items').append($('<option>').text(value).attr('value', key));

                        }
                    });
                }
            });
        }
    });
    $(document).on('change', '.items', function () {
        var item_id = parseInt($(this).val());

    });

    $(document).on('change', '.units', function () {
        var obj = $(this);
        var item_id = obj.closest('tr').find('.items').val();
        if (item_id == "" || item_id == undefined) {
            obj.val('');
            return alert("Please Select Item");
        }
        var unit_id = parseInt(obj.val());
        var item_array = [];
        var unit_array = [];
        var units = $(".units");
        var items = $(".items");
        $('#SalesTable tr').not(obj.closest('tr')).each(function () {
            $(this).find('.items').each(function () {
                item_array.push($(this).val());
            });
            $(this).find('.units').each(function () {
                unit_array.push($(this).val());
            });
        });
        var map = item_array.reduce(function (map, itemId, i) {
            var key_value = itemId + ":" + unit_array[i];
            map[key_value] = true;
            return map;
        }, {});
        console.log(map);

        if (map[item_id + ":" + unit_id] == true) {
            //console.log("it-"+item_id,"ui-"+unit_id)
            obj.val('');
            return alert("Given Item Unit Already Selected");
        }
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: '<?= $this->Url->build("/SalesReturnItems/ajax/isInStock")?>',
            data: {item_id: item_id, unit_id: unit_id, warehouse_id: warehouse_id},
            success: function (response, status) {
                if (response.code == 404) {
                    obj.val('');
                    // obj.closest('tr').find('.items').prop('selectedIndex', 0);
                    return alert("Stock Not Found For this Item and Unit")
                }
            }
        });
    });
    $(document).on('keyup', '.unit-price', function () {
        var obj = $(this);
        var unit_price = obj.val();
        var quantity = obj.closest('tr').find('.quantity').val();
        obj.closest('tr').find('.net-total').val(parseFloat(quantity * unit_price).toFixed(3));
        $("#total-value-for-view").html('Total: ' + getSum());
        $("#grand_total").val(getSum);
        $("#total-value-for-view").css("font-weight", "Bold");
        $("#total-value-for-view").css("fontSize", "15px");
    });
    $(document).on('keyup', '.quantity', function () {

        var obj = $(this);
        var quantity = obj.val();
        var unit_price = obj.closest('tr').find('.unit-price').val();
        obj.closest('tr').find('.net-total').val(parseFloat(quantity * unit_price).toFixed(3));
        $("#total-value-for-view").html('Total: ' + getSum());
        $("#grand_total").val(getSum);
        $("#total-value-for-view").css("font-weight", "Bold");
        $("#total-value-for-view").css("fontSize", "15px");
    });
    $(document).on('change', '.level', function () {
        var obj = $(this);
        var level = obj.val();
        $('.customer').select2('data', null);
        $.ajax({
            type: 'POST',
            url: '<?= $this->Url->build("/SalesReturnItems/ajax/units")?>',
            data: {level: level},
            success: function (response, status) {
                $('.parent-unit').empty();
                $('.parent-unit').append($('<option>').text("Select"));
                $.each(JSON.parse(response), function (key, value) {
                    $('.parent-unit').append($('<option>').text(value).attr('value', key));
                });
            }
        });


    });
    var getSum = function () {
        var sum = 0;
        var field = '.net-total';
        $('.moreTable').find(field).each(function (index, element) {
            sum += parseFloat($(element).val());
        });
        return sum;
    };
    $(document).on('change', '.parent-unit', function () {
        var obj = $(this);
        var unit = obj.val();
        $.ajax({
            type: 'POST',
            url: '<?= $this->Url->build("/SalesReturnItems/ajax/customers")?>',
            data: {unit: unit},
            dataType: 'json',
            success: function (response, status) {
                //console.log(response);
                $.each(response, function (key, value) {
                    $('.customer').append($('<option>').text(value).attr('value', key));
                });
            }
        });
    });
</script>

