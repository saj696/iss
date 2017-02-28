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
                <h4 class="text-center"><?= __('Sales & Collection Report') ?></h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td><?= __('Sl#') ?></td>
                                <td><?= __('Unit Name') ?></td>
                                <td><?= __('Credit Limit') ?></td>
                                <td><?= __('Opening Due') ?></td>
                                <td><?= __('Credit Sales') ?></td>
                                <td><?= __('Credit Note') ?></td>
                                <td><?= __('Cash Sales') ?></td>
                                <td><?= __('Net Total Sales') ?></td>
                                <td><?= __('Credit Collection') ?></td>
                                <td><?= __('Cash Collection') ?></td>
                                <td><?= __('Adjustment') ?></td>
                                <td><?= __('Total Recovery') ?></td>
                                <td><?= __('Closing Due') ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sl = 0;
                            foreach($finalArray as $unit=>$data):
                                ?>
                                <tr>
                                    <td><?=$sl+1?></td>
                                    <td><?=$nameArray[$unit]?></td>
                                    <td><?=$data['credit_limit']?></td>
                                    <td><?=$data['opening_due']?></td>
                                    <td><?=$data['credit_sales']?></td>
                                    <td><?=$data['credit_note']?></td>
                                    <td><?=$data['cash_sales']?></td>
                                    <td><?=$data['total_sales']?></td>
                                    <td><?=$data['credit_collection']?></td>
                                    <td><?=$data['cash_collection']?></td>
                                    <td><?=$data['adjustment']?></td>
                                    <td><?=$data['recovery']?></td>
                                    <td><?=$data['closing_due']?></td>
                                </tr>
                            <?php
                            $sl++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>