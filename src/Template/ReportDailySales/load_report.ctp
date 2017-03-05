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
                <h4 class="text-center"><?= __('Daily Sales Report') ?></h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table>
                        <tr>
                            <td rowspan="5">Name of Territory</td>
                        </tr>
                        <tr>
                            <td colspan="21" class="text-center">THIS MONTH RECORD</td>
                            <td colspan="12" class="text-center">CUMULATIVE RECORD (<?=$data['start_date']?> To This Date)</td>
                            <td colspan="5" class="text-center">DUES UPTO THE DATE</td>
                        </tr>
                        <tr>
                            <td colspan="10" class="text-center">SALES</td>
                            <td colspan="11" class="text-center">COLLECTION</td>

                            <td colspan="5" class="text-center">SALES</td>
                            <td colspan="7" class="text-center">COLLECTION</td>

                            <td rowspan="3">Over 90 Days</td>
                            <td rowspan="3">Over 120 Days</td>
                            <td rowspan="3">Over 180 Days</td>
                            <td rowspan="3">Bad Debt</td>
                            <td rowspan="3">Grand Total Dues</td>
                        </tr>
                        <tr>
                            <td rowspan="2" class="text-center">Target</td>
                            <td colspan="4" class="text-center">Sales This Day</td>
                            <td colspan="5" class="text-center">Sales Achievement This Month</td>

                            <td rowspan="2" class="text-center">Target</td>
                            <td colspan="4" class="text-center">Collection This Day</td>
                            <td colspan="6" class="text-center">Collection Achievement this Month</td>

                            <td rowspan="2" class="text-center">Target</td>
                            <td colspan="4" class="text-center">Sales This Day</td>
                            <td rowspan="2" class="text-center">Target</td>
                            <td colspan="6" class="text-center">Sales Achievement This Month</td>
                        </tr>
                        <tr>
                            <td>Credit</td>
                            <td>Credit Note</td>
                            <td>Cash</td>
                            <td>T.Sales</td>
                            <td>Credit</td>
                            <td>Credit Note</td>
                            <td>Cash</td>
                            <td>T.Sales</td>
                            <td>%Ach.</td>

                            <td>Credit</td>
                            <td>Cash</td>
                            <td>B.Debt</td>
                            <td>T. Coll.</td>

                            <td>Credit</td>
                            <td>Cash</td>
                            <td>B.Debt</td>
                            <td>Adjustment</td>
                            <td>T. Coll.</td>
                            <td>%Ach.</td>


                            <td>Credit</td>
                            <td>Cash</td>
                            <td>T.Sales</td>
                            <td>%Ach.</td>

                            <td>Credit</td>
                            <td>Cash</td>
                            <td>B.Debt</td>
                            <td>Adjustment</td>
                            <td>T. Coll.</td>
                            <td>%Ach.</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>