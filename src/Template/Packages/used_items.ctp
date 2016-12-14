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
            <?= $this->Html->link(__('Packages'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Add Used Items') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add Used Items') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($used_items, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <tr>
                                <td>
                                    <?php echo $this->Form->input('warehouse_id', ['options' => $warehouses, 'style' => 'width:50%', 'class' => 'form-control warehouses', 'required' => true, 'empty' => __('Select')]); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable" id="used_items">
                                    <tr>
                                        <th><?= __('Item') ?></th>
                                        <th><?= __('Unit') ?></th>
                                        <th><?= __('Stock') ?></th>
                                        <th><?= __('Stock (Kg/Litre)') ?></th>
                                        <th><?= __('Used Quantity (Kg/Litre)') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width:18%">
                                            <?php echo $this->Form->input('UsedItems.0.item_id', ['options' => $items, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td style="width: 23%;">
                                            <?php echo $this->Form->input('UsedItems.0.manufacture_unit_id', ['options' => $units, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control units', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td>
                                            <?php
                                            echo $this->Form->input('UsedItems.0.stock_quantity', ['type' => 'text', 'style' => 'width: 100%', 'required' => true, 'readonly' => true, 'class' => 'form-control numbersOnly stockQuantity', 'templates' => ['label' => '']]); ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo $this->Form->input('UsedItems.0.stock_quantity_bulk', ['type' => 'text', 'style' => 'width: 100%', 'readonly' => true, 'class' => 'form-control numbersOnly stockQuantity-bulk', 'templates' => ['label' => '']]); ?>
                                        </td>
                                        <td><?php echo $this->Form->input('UsedItems.0.quantity', ['type' => 'text', 'style' => 'width: 100%', 'required' => 'required', 'class' => 'form-control numbersOnly quantity ', 'templates' => ['label' => '']]); ?></td>

                                        <td width="50px;"><span
                                                class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
                    </div>

                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        var item_id,
            unit_id,
            warehouse_id;

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
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
        $(document).on('change', '.warehouses', function () {
            warehouse_id = parseInt($(this).val())

        });
        $(document).on('change', '.items', function () {
            item_id = parseInt($(this).val())
            $(this).closest('tr').find('.units').val('');
            $(this).closest('tr').find('.stockQuantity').val('');
            $(this).closest('tr').find('.stockQuantity-bulk').val('');

        });
        Array.prototype.insert = function (index, item) {
            this.splice(index, 0, item);
        };
        $(document).on('change', '.units', function () {
            var obj = $(this);
            item_id = obj.closest('tr').find('.items').val();
            if (item_id == "" || item_id == undefined || warehouse_id == "" || warehouse_id == undefined) {
                $(this).val('');
                return alert("Please Select Ware House and Item");
            }
            unit_id = parseInt($(this).val());
            var item_array = [];
            var unit_array = [];
            var units = $(".units");
            var items = $(".items");

            $('#used_items tr').not(obj.closest('tr')).each(function () {
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
                obj.val('');
                return alert("Given Item Unit Already Selected");
            }
            var thisRow = $(this).closest('tr');
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: '<?= $this->Url->build("/Packages/stockQuantity")?>',
                data: {item_id: item_id, unit_id: unit_id, warehouse_id: warehouse_id},
                success: function (data, status) {
                    if (data.code == 200) {
                        //console.log(data);
                        thisRow.find('.stockQuantity').val(data.result);

                        var bulk_quantity;
                        if (data.unit_size == 0) {
                            if (data.unit_type == 1 || data.unit_type == 3) {
                                bulk_quantity = data.result / 1000;
                                thisRow.find('.stockQuantity-bulk').val(bulk_quantity.toFixed(3));
                            }
                            else {
                                bulk_quantity = data.result;
                                thisRow.find('.stockQuantity-bulk').val(bulk_quantity.toFixed(3));
                            }

                        }
                        else {
                            if (data.unit_type == 1 || data.unit_type == 3) {
                                var converted = data.result * data.unit_size;
                                var bulk_quantity = converted / 1000;
                                thisRow.find('.stockQuantity-bulk').val(bulk_quantity.toFixed(3));
                            }
                            else {
                                var bulk_quantity = data.result * data.unit_size;
                                thisRow.find('.stockQuantity-bulk').val(bulk_quantity.toFixed(3));
                            }
                        }
                    }
                    else if (data.code == 404) {
                        thisRow.find('.stockQuantity').val("");
                        thisRow.find('.stockQuantity-bulk').val("");
                        return alert(data.result)
                    }
                }
            });
        });

        $(document).on('keyup', '.quantity', function (event) {
            var obj = $(this);
            var thisRow = obj.closest('tr');
            var input = parseFloat(obj.val());
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
            var stock_amount = parseFloat(thisRow.find('.stockQuantity-bulk').val());
            console.log(stock_amount);
            if (stock_amount == '' || isNaN(stock_amount) || stock_amount == 0) {
                obj.val('');
                return alert("Stock is Empty")
            }
            if (input > stock_amount) {
                obj.val(0);
                alert('Try lesser quantity!');
            }
        });

    });
</script>