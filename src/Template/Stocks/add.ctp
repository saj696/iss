<?php
use Cake\Core\Configure;

$config_stock_types = Configure::read('stock_log_types');
unset($config_stock_types[0], $config_stock_types[1], $config_stock_types[2], $config_stock_types[7], $config_stock_types[8]
    , $config_stock_types[9],
    $config_stock_types[10]
);

?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Stocks'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Add Stock') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add Stock') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($stock, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <tr>
                                <td>
                                    <?php echo $this->Form->input('warehouse_id', ['options' => $warehouses, 'style' => 'width:50%', 'class' => 'form-control', 'required' => true, 'empty' => __('Select')]); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable">
                                    <tr>
                                        <th><?= __('Item') ?></th>
                                        <th><?= __('Unit') ?></th>
                                        <th><?= __('Stock Type') ?></th>
                                        <th><?= __('Quantity (Pcs)') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width:25%;">
                                            <?php echo $this->Form->input('details.0.item_id', ['options' => $items, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td style="width: 30%;">
                                            <?php echo $this->Form->input('details.0.manufacture_unit_id', ['required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control units', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td style="width:20%"><?php echo $this->Form->input('details.0.type', ['options' => $config_stock_types, 'style' => 'width: 100%', 'required' => 'required', 'class' => 'form-control', 'templates' => ['label' => '']]); ?></td>

                                        <td style="width:25%"><?php echo $this->Form->input('details.0.quantity', ['type' => 'text', 'style' => 'width: 100%', 'required' => 'required', 'class' => 'form-control quantity numbersOnly', 'templates' => ['label' => '']]); ?></td>
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
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

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
        $(document).on('change', '.items', function () {
            var obj = $(this);
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                data: {'item_id': parseInt(obj.val())},
                url: '<?= $this->Url->build("/Stocks/ajax/units_from_item_units")?>',
                success: function (data, status) {
                    if (data != null) {
                        obj.closest('tr').find('.units').empty();
                        $.each(data, function (key, value) {
                            console.log(value)
                            console.log(key)
                            obj.closest('tr').find('.units').append($('<option>').text(value).attr('value', key));
                        });
                    }
                    else {
                        obj.closest('tr').find('.units').empty();
                        obj.closest('tr').find('.units').append($('<option>').text('Select').attr('value',''));
                        return alert("Unit Not Found For Selected Item");
                    }
                }
            });
        });
    });
</script>