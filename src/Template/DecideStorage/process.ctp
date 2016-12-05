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
        <li><?= __('Process') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Process') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'view/'.$eventId], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <form method="post" class="form-horizontal" role="form" action="<?= $this->Url->build("/DecideStorage/add")?>">
                    <input type="hidden" name="event_id" class="event_id" value="<?=$eventId?>" />
                    <?php foreach($decidedArray as $warehouseId=>$itemDetail):?>
                        <table class="table table-bordered">
                            <tbody>
                                <tr><td colspan="6" class="text-center"><label class="label label-info"><?= $warehouses[$warehouseId]?></label></td></tr>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                </tr>
                                <?php foreach($itemDetail as $item=>$quantity):
                                    if($quantity>0):
                                    ?>
                                    <input type="hidden" name="detail[<?=$warehouseId?>][<?=$item?>]" value="<?= $quantity?>" />
                                    <tr>
                                        <td><?= $itemArray[$item]?></td>
                                        <td><?= $quantity>0?$quantity:0?></td>
                                    </tr>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    <?php endforeach;?>
                    <div class="text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Save'), ['class' => 'btn btn-circle yellow submitBtn']) ?>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){

    });
</script>