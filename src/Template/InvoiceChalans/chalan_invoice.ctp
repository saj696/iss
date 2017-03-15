<?php
use Cake\Core\Configure;

$status = \Cake\Core\Configure::read('status_options');
$webroot =  $this->request->webroot;
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Process Chalan'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Process Chalan') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Process Chalan') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                    <span class="btn btn-sm yellow" style="float: right; margin-left: 10px;" onclick="print_rpt(<?=$webroot?>)">&nbsp;Print&nbsp;</span>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <form method="post" class="form-horizontal" role="form" action="<?= $this->Url->build("/InvoiceChalans/makeChalan")?>">
                        <?php foreach($invoiceIds as $invoiceId):?>
                            <input type="hidden" name="invoiceIds[]" value="<?= $invoiceId?>" />
                        <?php endforeach;?>
                        <?php foreach($eventIds as $eventId):?>
                            <input type="hidden" name="eventIds[]" value="<?= $eventId?>" />
                        <?php endforeach;?>
                        <div id="PrintArea">
                            <div>
                                <table style="width: 100%; margin: 15px 30px 15px 0; text-align: center">
                                    <tr>
                                        <td>
                                            <div>
                                                <h3>East West Chemicals Limited</h3>
                                                <h5>Corporate Office: 52/1- New Eskaton Road Hasan Holdings Ltd (9th Floor), Dhaka 1000, Bangladesh.</h5>
                                                <h5>Phone: 02-9360658, 8359881, Fax: 02-9351395.</h5>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php foreach($invoices as $invoice):?>
                            <div class="col-md-12">
                                <table class="table table-bordered" style="margin: 20px 0 0 0;">
                                    <tr>
                                        <td colspan="3"><span class="pull-left">Customer: <b><?= $invoice['customer']['name']?></b></span><span class="pull-right">Invoice Date: <b><?= date('d-m-Y', $invoice['invoice_date'])?></b></span></td>
                                    </tr>
                                </table>
                                <table class="table table-bordered" style="margin: 0px;">
                                    <tbody>
                                        <tr style="color: white; background-color: lightgrey">
                                            <th>Item</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Net Total</th>
                                        </tr>
                                        <?php foreach($invoice['invoiced_products'] as $detail):
                                            ?>
                                            <tr>
                                                <td><?= $itemArray[$detail['item_unit_id']]?></td>
                                                <td class="text-center"><?= $detail['product_quantity']+$detail['bonus_quantity']+$detail['special_offer_bonus_quantity']?></td>
                                                <td class="text-center"><?= $detail['net_total']?></td>
                                            </tr>
                                        <?php endforeach;?>
                                    </tbody>
                                </table>
                                <table class="table table-bordered" style="margin: 0 0 20px 0;">
                                    <tr>
                                        <td colspan="3"><span class="pull-left">Delivery date: <b><?= date('d-m-Y', $invoice['delivery_date'])?></b></span><span class="pull-right">Net Total: <b><?= $invoice['net_total']?></b></span></td>
                                    </tr>
                                </table>
                            </div>
                    <?php endforeach;?>
                    </div>
                </div>

                <div class="text-center" style="margin-bottom: 20px;">
                    <?= $this->Form->button(__('Make Chalan'), ['class' => 'btn default yellow-stripe', 'style'=>'font-size:13px; padding:6px 8px;']) ?>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>