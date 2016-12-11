<?php
use Cake\Core\Configure;

$yes_no = Configure::read('yes_no');
?>

<script src="http://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link href="http://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" type="text/css"/>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Warehouse Items'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Warehouse Item') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Warehouse Item') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($warehouseItem, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable">
                                    <tr>
                                        <th><?= __('WareHouse') ?></th>
                                        <th><?= __('Item') ?></th>
                                        <th><?= __('Use Alias') ?></th>
                                        <th><?= __('Cancel') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width: 40%;">
                                            <?php echo $this->Form->input('WarehouseItems.0.warehouse_id', ['options' => $warehouses, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control warehouses', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?>
                                        <td><?php echo $this->Form->input('WarehouseItems.0.item_id', ['options' => $items, 'style' => 'width: 100%', 'empty' => __('Select'), 'required' => 'required', 'class' => 'form-control items', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('WarehouseItems.0.use_alias', ['style' => 'width: 100%', 'options' => $yes_no, 'required' => 'required', 'class' => 'form-control', 'templates' => ['label' => '']]); ?></td>

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

        $(document).on('change', '.items', function () {
            var This = $(this)
            var item_value = This.val();
            var warehouse_value = $(this).closest('tr').find('.warehouses').val();
            if (warehouse_value == "" || warehouse_value == undefined) {
                $(this).val('');
                return alert("Please Select Warehouse First");
            }
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/WarehouseItems/itemAvailable")?>',
                data: {'warehouse_id': warehouse_value, item_id: item_value},
                success: function (data, status) {
                    if (data == 0) {
                        This.val('');
                        return alert("This item is already inserted")
                    }
                }
            });

        });


    });
</script>
