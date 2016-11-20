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
            <?= $this->Html->link(__('Invoices for Chalan'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Make Chalan') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Make Chalan') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <form method="post" class="form-horizontal" role="form" action="<?= $this->Url->build("/InvoiceChalans/add")?>">
                    <?php foreach($invoiceIds as $invoiceId):?>
                        <input type="hidden" name="invoiceIds[]" value="<?= $invoiceId?>" />
                    <?php endforeach;?>
                    <input type="hidden" name="chalan_no" value="<?= $sl_no?>" />
                    <div class="col-md-12">
                        <table class="table table-bordered" style="margin: 20px 0 0 0;">
                            <tr>
                                <td colspan="3"><span class="pull-left">Chalan no: <b><?= $sl_no?></b></span><span class="pull-right">Date: <b><?= date('d-m-Y')?></b></span></td>
                            </tr>
                        </table>
                        <table class="table table-bordered" style="margin-bottom: 20px;">
                            <tbody>
                                <tr class="portlet box grey-silver" style="color: white">
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                </tr>
                                <?php foreach($returnData as $item_id=>$detail):?>
                                    <tr>
                                        <td><?= $itemArray[$detail['product_id']]?></td>
                                        <td class="text-center"><?= $detail['product_quantity']?><input type="hidden" name="detail[<?=$item_id?>]" value="<?= $detail['product_quantity']?>"></td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Send Delivery'), ['name'=>'send', 'class' => 'btn default green-stripe', 'style'=>'font-size:13px; padding:6px 8px;']) ?>
                        <?= $this->Form->button(__('Forward'), ['name'=>'forward', 'class' => 'btn default yellow-stripe', 'style'=>'font-size:13px; padding:6px 8px;']) ?>
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