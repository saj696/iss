<?php
$status = \Cake\Core\Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Decide Storage'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Decide') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Decide Storage') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered">
                        <tr>
                            <td><b>Order No: </b><?= $orderNo?></td>
                            <td><b>From: </b><?= $requestFrom?></td>
                            <td><b>Date: </b><?= $requestDate?></td>
                        </tr>
                    </table>
                </div>

                <div class="table-scrollable">
                    <table class="table table-bordered">
                        <tr><td class="text-center" colspan="12"><label class="label label-primary">Request Summary</label> </td></tr>
                        <tr>
                            <th>Sl#</th>
                            <th>Item</th>
                            <th>Required</th>
                        </tr>
                        <?php foreach($items as $key=>$item):?>
                            <tr>
                                <td><?= $key+1?></td>
                                <td><?= $itemArray[$item['item_unit_id']]?></td>
                                <td><?= $item['quantity']?></td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>

                <div class="table-scrollable">
                    <table class="table table-bordered">
                        <tr><td class="text-center" colspan="12"><label class="label label-warning">Warehouse Detail</label> </td></tr>
                        <tr>
                            <th>Item</th>
                            <th>Warehouse</th>
                            <th>Existing</th>
                        </tr>
                        <?php foreach($requestWarehouseDetails as $detail):?>
                            <tr>
                                <td><?= $itemArray[$detail['item_unit_id']]?></td>
                                <td><?= $allWarehouses[$detail['warehouse_id']]?></td>
                                <td><?= $detail['existing']?></td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>

                <div class="row text-center">
                    <div class="col-md-5 col-md-offset-4">
                        <?php echo $this->Form->input('warehouse', ['options' => $warehouses, 'style'=>'max-width: 100%', 'class'=>'form-control warehouse select2me', 'empty' => __('-- Select warehouse --'), 'templates'=>['label' => '']]);?>
                    </div>
                </div>

                <div class="table-scrollable">
                    <form method="post" class="form-horizontal" role="form" action="<?= $this->Url->build("/DecideStorage/process")?>">
                        <input type="hidden" name="event_id" class="event_id" value="<?=$id?>" />
                        <table class="table table-bordered">
                            <tbody class="appendDiv">
                                <tr><td class="text-center" colspan="12"><label class="label label-success">Item Existence</label> </td></tr>
                                <tr>
                                    <th>Item</th>
                                    <th>Warehouse</th>
                                    <th>Quantity</th>
                                    <th>Decided Qty</th>
                                </tr>
                                <?php foreach($myWarehouseDetails as $detail):?>
                                    <tr class="main_tr">
                                        <td><?= $itemArray[$detail['item_unit_id']]?></td>
                                        <td><?= $allWarehouses[$detail['warehouse_id']]?></td>
                                        <td><?= $detail['quantity']?></td>
                                        <td width="20%">
                                            <input type="hidden" class="existing_quantity" value="<?= $detail['quantity']?>">
                                            <input type="hidden" class="warehouse_id" value="<?= $detail['warehouse_id']?>">
                                            <input type="text" name="decided[<?= $detail['warehouse_id']?>][<?= $detail['item_unit_id']?>]" style="height: 25px;" class="form-control decided_quantity numbersOnly" value="" />
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                        <div class="text-center" style="margin-bottom: 20px;">
                            <?= $this->Form->button(__('Process'), ['class' => 'btn btn-circle yellow']) ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on('keyup', '.decided_quantity', function(){
            var obj = $(this);
            var inputQty = obj.val();
            var existingQty = parseInt(obj.closest('.main_tr').find('.existing_quantity').val());
            if(inputQty>existingQty) {
                obj.val(0);
                toastr.info('try less!');
            }
        });

        $(document).on('change', '.warehouse', function () {
            var obj = $(this);
            var warehouse_id = obj.val();

            var myArr = [];
            $( ".warehouse_id" ).each(function( index ) {
                myArr.push($(this).val());
            });

            var uniqueArr = uniqueArray(myArr);
            uniqueArr.push(warehouse_id);
            var uniqueArrAfterSelection = uniqueArray(uniqueArr);

            if(uniqueArr.length != uniqueArrAfterSelection.length){
                toastr.info('Duplicate Warehouse!');
            } else {
                if(warehouse_id>0) {
                    var event_id = $('.event_id').val();
                    $.ajax({
                        type: 'POST',
                        url: '<?= $this->Url->build("/DecideStorage/ajax")?>',
                        data: {warehouse_id: warehouse_id, event_id: event_id},
                        success: function (data, status) {
                            if (data) {
                                $('.appendDiv').append(data);
                            }
                        }
                    });
                }
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