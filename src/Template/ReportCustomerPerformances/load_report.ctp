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
                <h4 class="text-center">
                    <strong>
                    <?php
                    if($report_type==1){
                        echo 'Sales Target Vs Achievement Report';
                    }else{
                        echo 'Collection Target Vs Achievement Report';
                    }
                    ?>
                    </strong>
                </h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td>Location Name</td>
                                <td colspan="3" style="text-align: center;">This Month</td>
                                <td colspan="3" style="text-align: center;">Cumulative</td>
                            </tr>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td></td>
                                <td>Target</td>
                                <td>Net Total</td>
                                <td>%</td>
                                <td>Target</td>
                                <td>Net Total</td>
                                <td>%</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($finalArray as $unit=>$data):
                                ?>
                                <tr>
                                    <td><?= $nameArray[$unit]?></td>
                                    <td><?= $data['this_month_target']?></td>
                                    <td><?= $data['this_month_achievement']?></td>
                                    <td>
                                        <?php
                                        if($data['this_month_target']>0){
                                            echo round(($data['this_month_achievement']/$data['this_month_target']*100), 2);
                                        }else{
                                            echo 0;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $data['cumulative_target']?></td>
                                    <td><?= $data['cumulative_achievement']?></td>
                                    <td>
                                        <?php
                                        if($data['cumulative_target']>0){
                                            echo round(($data['cumulative_achievement']/$data['cumulative_target']*100), 2);
                                        }else{
                                            echo 0;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>