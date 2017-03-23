<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 15-Jan-17
 * Time: 10:59 AM
 */
use Cake\Core\Configure;
$webroot =  $this->request->webroot;
?>

<div style="margin: -7px 0 0 0px; padding: 43px; background-color: #ffffff; width: 100%">
    <div class="portlet-body">
        <div style="margin:0">
            <button class="btn btn-circle red icon-print2" style="float: right;" onclick="print_rpt(<?=$webroot?>)">&nbsp;Print&nbsp;</button>
        </div>

        <div id="PrintArea" style="width: 100%;">
            <div>
                <table style="width: 100%; margin: 15px 30px 15px 0;">
                    <tr>
                        <td>
                            <div>
                                <h3>East West Chemicals Limited</h3>
                                <h5>Corporate Office: 52/1- New Eskaton Road Hasan Holdings Ltd (9th Floor), Dhaka 1000, Bangladesh.</h5>
                                <h5>Phone: 02-9360658, 8359881, Fax: 02-9351395.</h5>
                            </div>
                        </td>
                        <td>
                            <div style="text-align: right">
                                <h3>Invoice</h3>
                                <h5>Invoice Type: <?= Configure::read('invoice_type')[$invoiceArray['invoice_type']]?></h5>
                                <h5>Invoice Time: <?= date('d-m-Y h:i:s', $invoiceArray['created_date']+(6*60*60))?></h5>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div>
                <hr />
            </div>

            <div>
                <table style="width: 100%; margin: 15px 30px 15px 0;">
                    <tr>
                        <td>
                            <div>
                                <h5 style="text-decoration: underline;">Customer</h5>
                                <h5><?=$customerInfo['code']?></h5>
                                <h5><?='M/s. '.$customerInfo['name']?></h5>
                                <h5><?=$customerInfo['address']?></h5>
                            </div>
                        </td>
                        <td>
                            <div>
                                <h5 style="text-decoration: underline;">Territory</h5>
                                <h5><?=$locationInfo['unit_name']?></h5>
                            </div>
                        </td>
                        <td>
                            <div class="pull-right" style="text-align: right">
                                <h5>Order No: <?=$invoiceArray['field_po_no']?></h5>
                                <h5>Invoice No: <?=$invoice_no?></h5>
                                <h5>Invoice Date: <?=date('d-m-Y', $invoiceArray['invoice_date'])?></h5>
                                <h5>Delivery Date: <?=date('d-m-Y', $invoiceArray['delivery_date'])?></h5>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div>
                <table style="width: 100%; margin: 15px 30px 15px 0;">
                    <tr>
                        <td>
                            <div>
                                <h5>Credit Limit: <?=$customerInfo['credit_limit']?></h5>
                            </div>
                        </td>
                        <td>
                            <div style="text-align: center">
                                <h5>Total Due: <?=$currentDue?></h5>
                            </div>
                        </td>
                        <td>
                            <div class="pull-right" style="text-align: right">
                                <h5>Available Credit: <?=$customerInfo['credit_limit']-$currentDue?></h5>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div>
                <hr />
            </div>

            <div>
                <table class="table" style="border: 0;">
                    <tr>
                        <td>Sl. No.</td>
                        <td>Product</td>
                        <td>Qty</td>
                        <td>Bonus</td>
                        <td>Total Qty</td>
                        <td>Unit Price</td>
                        <td>Total Price</td>
                        <td>Discount</td>
                        <td>Special Discount</td>
                        <td>Net Value</td>
                    </tr>
                    <?php
                    $sum_total_price = 0;
                    $sum_less_discount = 0;
                    $sum_net_value = 0;
                    foreach($invoiceArray['invoiced_products'] as $key=>$product):
                    ?>
                        <tr>
                            <td><?=$key+1?></td>
                            <td><?=$itemArray[$product['item_unit_id']]?></td>
                            <td><?=$product['product_quantity']?></td>
                            <td><?=$product['bonus_quantity']?></td>
                            <td><?=$product['product_quantity']+$product['bonus_quantity']?></td>
                            <td><?=$product['unit_price']?></td>
                            <td><?=($product['product_quantity']+$product['bonus_quantity'])*$product['unit_price']?></td>
                            <td><?=$product['bonus_quantity']*$product['unit_price']?></td>
                            <td><?= $product['instant_discount']?></td>
                            <td><?=($product['product_quantity']+$product['bonus_quantity'])*$product['unit_price']-$product['bonus_quantity']*$product['unit_price']-$product['instant_discount']?></td>
                        </tr>
                    <?php
                        $sum_total_price += ($product['product_quantity']+$product['bonus_quantity'])*$product['unit_price'];
                        $sum_less_discount += $product['bonus_quantity']*$product['unit_price']+$product['instant_discount'];
                        $sum_net_value += ($product['product_quantity']+$product['bonus_quantity'])*$product['unit_price']-$product['bonus_quantity']*$product['unit_price']-$product['instant_discount'];
                    endforeach;
                    ?>

                    <tr>
                        <td colspan="6"></td>
                        <td><?=$sum_total_price?></td>
                        <td><?=$sum_less_discount?></td>
                        <td></td>
                        <td><?=$sum_net_value?></td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="table" style="border: 0;">
                    <td>Total Payable Amount Taka: <?=$this->System->convert_number_to_words($sum_net_value).' Only'?></td>
                    <td style="text-align: right">Net Payable Amount: <?=$sum_net_value?></td>
                </table>
            </div>

            <div style="margin-top: 300px;">
                <table class="table" style="border: 0;">
                    <td style="text-decoration: overline;text-align: left">Customer Seal & Signature</td>
                    <td style="text-decoration: overline; text-align: left">Created By</td>
                    <td style="text-decoration: overline; text-align: center">Delivered By</td>
                    <td style="text-decoration: overline; text-align: right">Authorized Signature</td>
                </table>
            </div>

            <div style="margin-top: 50px;">
                <h5>Depot: <?=$depotInfo['name']?$depotInfo['name']:'Not Set'?></h5>
                <h5>Address: <?=$depotInfo['address']?$depotInfo['address']:'Not Set'?></h5>
            </div>
        </div>
    </div>
</div>