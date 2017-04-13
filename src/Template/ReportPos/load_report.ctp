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

<div class="col-md-12" style="margin-top: 20px;">
    <?= $this->element('company_header');?>
    <div class="portlet-body">
        <?php if(!isset($btnHide)):?>
        <div class="col-md-12">
            <button class="btn btn-circle red icon-print2" style="float: right; margin-bottom: 10px" onclick="print_rpt(<?=$webroot?>)">&nbsp;Print&nbsp;</button>

            <?= $this->Form->create('',['class' => 'form-horizontal','method'=>'get', 'role' => 'form', 'action'=>'loadReport/pdf']) ?>
            <?php foreach($data as $name=>$val):?>
                <input type="hidden" name="<?=$name?>" value="<?=$val?>">
            <?php endforeach;?>
            <button type="submit" class="pdf btn btn-circle yellow icon-print2" style="float: right; margin-bottom: 10px; margin-right: 10px;">&nbsp;PDF&nbsp;</button>
            <?= $this->Form->end() ?>
        </div>
        <?php endif;?>

        <div id="PrintArea">
            <div class="row">
                <h4 class="text-center"><?= __('PO Report') ?></h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td><?= __('Sl#') ?></td>
                                <td><?= __('PO Date') ?></td>
                                <td><?= __('Customer Name') ?></td>
                                <td><?= __('PO Type')?></td>
                                <td><?= __('Total Products')?></td>
                                <td><?= __('Net Total')?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(sizeof($pos->toArray())>0):?>
                            <?php foreach($pos as $key=>$po):?>
                                <tr>
                                    <td><?= $key+1?></td>
                                    <td><?= date('d-m-Y', $po['po_date'])?></td>
                                    <td><?= $po['customer']['name']?></td>
                                    <td><?= Configure::read('invoice_type')[$po['invoice_type']]?></td>
                                    <td><?= sizeof($po['po_products'])?></td>
                                    <td><?= $po['net_total']?></td>
                                </tr>
                            <?php endforeach;?>
                            <?php else:?>
                                <tr>
                                    <td colspan="12" class="text-center" style="background-color: lightgrey">No Data Found.</td>
                                </tr>
                            <?php endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>