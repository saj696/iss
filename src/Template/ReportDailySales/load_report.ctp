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
                            <th rowspan="5">Location</th>
                        </tr>
                        <tr>
                            <th colspan="21" class="text-center">THIS MONTH RECORD</th>
                            <th colspan="12" class="text-center">CUMULATIVE RECORD (<?=$data['start_date']?> To This Date)</th>
                            <th colspan="5" class="text-center">DUES UPTO THE DATE</th>
                        </tr>
                        <tr>
                            <th colspan="10" class="text-center">SALES</th>
                            <th colspan="11" class="text-center">COLLECTION</th>

                            <th colspan="5" class="text-center">SALES</th>
                            <th colspan="7" class="text-center">COLLECTION</th>

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

                        <?php foreach($finalArray as $location=>$detail):?>
                        <tr>
                            <td><?= $nameArray[$location]?></td>
                            <td><?= $detail['this_month_sales_target']?></td>
                            <td><?= $detail['this_day_credit_sales']?></td>
                            <td><?= $detail['this_day_credit_note']?></td>
                            <td><?= $detail['this_day_cash_sales']?></td>
                            <td><?= $detail['this_day_credit_sales']+$detail['this_day_cash_sales']-$detail['this_day_credit_note']?></td>
                            <td><?= $detail['this_month_credit_sales']?></td>
                            <td><?= $detail['this_month_credit_note']?></td>
                            <td><?= $detail['this_month_cash_sales']?></td>
                            <td><?= $detail['this_month_credit_sales']+$detail['this_month_cash_sales']-$detail['this_month_credit_note']?></td>
                            <td><?= ($detail['this_month_credit_sales']+$detail['this_month_cash_sales']-$detail['this_month_credit_note'])>0?$detail['this_month_sales_target']/($detail['this_month_credit_sales']+$detail['this_month_cash_sales']-$detail['this_month_credit_note']):0?></td>

                            <td><?= $detail['this_month_collection_target'];?></td>
                            <td><?= $detail['this_day_credit_collection']?></td>
                            <td><?= $detail['this_day_cash_collection']?></td>
                            <td>0</td>
                            <td><?= $detail['this_day_credit_collection']+$detail['this_day_cash_collection']?></td>
                            <td><?= $detail['this_month_credit_collection']?></td>
                            <td><?= $detail['this_month_cash_collection']?></td>
                            <td>0</td>
                            <td><?= $detail['this_month_adjustment']?></td>
                            <td><?= $detail['this_month_credit_collection']+$detail['this_month_cash_collection']?></td>
                            <td><?= ($detail['this_month_credit_collection']+$detail['this_month_cash_collection'])>0?$detail['this_month_collection_target']/($detail['this_month_credit_collection']+$detail['this_month_cash_collection']):0?></td>

                            <td><?= $detail['cumulative_sales_target']?></td>
                            <td><?= $detail['cumulative_credit_sales']?></td>
                            <td><?= $detail['cumulative_cash_sales']?></td>
                            <td><?= $detail['cumulative_credit_sales']+$detail['cumulative_cash_sales']?></td>
                            <td><?= ($detail['cumulative_credit_sales']+$detail['cumulative_cash_sales'])>0?$detail['cumulative_sales_target']/($detail['cumulative_credit_sales']+$detail['cumulative_cash_sales']):0?></td>
                            <td><?= $detail['cumulative_collection_target']?></td>
                            <td><?= $detail['cumulative_credit_collection']?></td>
                            <td><?= $detail['cumulative_cash_collection']?></td>
                            <td>0</td>
                            <td><?= $detail['cumulative_adjustment']?></td>
                            <td><?= $detail['cumulative_credit_collection']+$detail['cumulative_cash_collection']?></td>
                            <td><?= ($detail['cumulative_credit_collection']+$detail['cumulative_cash_collection'])>0?$detail['cumulative_collection_target']/($detail['cumulative_credit_collection']+$detail['cumulative_cash_collection']):0?></td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>