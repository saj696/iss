<?php
use Cake\Core\Configure;

$yes_no = Configure::read('yes_no');
?>
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
        <li><?= __('Edit Warehouse Item') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Warehouse Item') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($warehouseItem, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('warehouse_id', ['class' => 'form-control warehouses', 'options' => $warehouses, 'empty' => __('Select'), 'required' => true]);
                        echo $this->Form->input('item_id', ['class' => 'form-control items', 'options' => $items, 'empty' => __('Select'), 'required' => true]);
                        echo $this->Form->input('use_alias', ['options' => $yes_no, 'empty' => __('Select'), 'required' => true]);
                        echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).on('change', '.items', function () {
        var This = $(this)
        var item_value = This.val();
        var warehouse_value = $('.warehouses').val();
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

</script>