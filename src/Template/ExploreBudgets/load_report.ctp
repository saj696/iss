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
                <h4 class="text-center"><?= __('Explore Budgets') ?></h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table class="table table-bordered">
                        <thead>
                        <tr style="border-bottom: 3px solid lightgrey">
                            <td><?= __('Sl#') ?></td>
                            <td><?= __('Location') ?></td>
                            <td><?= __('Total Budget') ?></td>
                            <td><?= __('Measure Unit')?></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(sizeof($mainArr)>0):?>
                            <?php foreach($mainArr as $key=>$detail):?>
                                <tr>
                                    <td><?= $key+1;?></td>
                                    <td><?= $detail['unit_name'];?></td>
                                    <td><?= isset($detail['total'])?$detail['total']:0;?></td>
                                    <td><?= $detail['measure_unit']>0?Configure::read('pack_size_units')[$detail['measure_unit']]:''?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php else:?>
                            <tr><td class="text-center alert-danger" colspan="12"><?= __('No Data Found')?></td></tr>
                        <?php endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>