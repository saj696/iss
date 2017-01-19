<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 15-Jan-17
 * Time: 10:59 AM
 */
use Cake\Core\Configure;
$webroot =  $this->request->webroot;
$tTypeCon = Configure::read('transaction_type_display');
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
                <h4 class="text-center"><?= __('Customer Ledger') ?></h4>
            </div>

            <div class="row">
                <div class="col-md-12 report-table" style="overflow: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td colspan="6">Opening Balance</td>
                                <td><?= $finalDue?></td>
                            </tr>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td><?= __('Sl#') ?></td>
                                <td><?= __('Date') ?></td>
                                <td><?= __('Transaction No.') ?></td>
                                <td><?= __('T.Type')?></td>
                                <td><?= __('Inv Amount')?></td>
                                <td><?= __('Pay Amount')?></td>
                                <td><?= __('Balance')?></td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(sizeof($finalArray)>0):
                            $i=0;
                            $balance = $finalDue;
                            foreach($finalArray as $date=>$detail):
                                foreach($detail as $invOrPay=>$info):
                                    foreach($info as $field):?>
                                    <tr>
                                        <td><?= $i+1?></td>
                                        <td><?= date('d-m-Y', $date)?></td>
                                        <td>
                                            <?php
                                                if($invOrPay=='inv'):
                                                    echo $field['id'];
                                                else:
                                                    echo $field['id'].' - '.$field['sl_no'];
                                                endif;
                                            ?>
                                        </td>
                                        <td><?= $tTypeCon[$invOrPay][$field['type']]?></td>
                                        <td>
                                            <?php
                                            if($invOrPay=='inv'){
                                                echo $field['net_total'];
                                                $balance += $field['net_total'];
                                            }else{
                                                echo '';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if($invOrPay=='pay'){
                                                echo $field['net_total'];
                                                $balance -= $field['net_total'];
                                            }else{
                                                echo '';
                                            }
                                            ?>
                                        </td>
                                        <td><?= $balance?></td>
                                    </tr>
                                <?php
                                    $i++;
                                endforeach;
                                endforeach;
                                ?>
                            <?php
                            endforeach;
                        else:?>
                            <tr><td class="text-center alert-danger" colspan="12"><?= __('No Data Found')?></td></tr>
                        <?php
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>