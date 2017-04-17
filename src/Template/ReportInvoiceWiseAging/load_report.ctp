<?php
error_reporting(0);
$webroot = $this->request->webroot;
$unit_types = \Cake\Core\Configure::read('pack_size_units');

?>
<div class="col-md-12" style="margin-top: 20px;">
    <div class="portlet-body">
        <div class="portlet-body">
            <?php if (!isset($btnHide)): ?>
                <div class="col-md-12">
                    <button class="btn btn-circle red icon-print2" style="float: right; margin-bottom: 10px" onclick="print_rpt(<?= $webroot ?>)">&nbsp;Print&nbsp;</button>
                    <?= $this->Form->create('', ['class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'action' => 'loadReport/pdf']) ?>

                    <input type="hidden" name="global_id" value="<?= $data['global_id'] ?>">
                    <input type="hidden" name="year" value="<?= $data['year'] ?>">
                    <input type="hidden" name="parent_level" value="<?= $data['parent_level'] ?>">
                    <button type="submit" class="pdf btn btn-circle yellow icon-print2" style="float: right; margin-bottom: 10px; margin-right: 10px;">&nbsp;PDF&nbsp;</button>
                    <?= $this->Form->end() ?>
                </div>
            <?php endif; ?>
            <div id="PrintArea">
                <?= $this->element('company_header'); ?>
                <div class="row">
                    <h4 class="text-center"><strong>Invoice Wise Aging Report</strong></h4>
                    <h4 class="text-center"><strong><?= $administrative_unit ?> </strong></h4>
                </div>
                <div class="row">
                    <div class="col-md-12 report-table" style="overflow: auto;">
                        <table class="table table-bordered">
                            <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td>Inv No.</td>
                                <td>Inv Date</td>
                                <td>Inv Amount</td>
                                <td>Due Amount</td>
                                <td>Due Date</td>
                                <td>Age(Day)</td>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach($mainArr as $customer=>$invArr):?>
                                    <tr>
                                        <td colspan="12" class="text-center" style="background-color: lightgrey">
                                            <?php
                                            $customerDetail = $this->System->get_customer_detail($customer);
                                            echo $customerDetail['code'].' '.$customerDetail['name'].' Credit Limit: '.$customerDetail['credit_limit'].' Credit Inv Days: '.$customerDetail['credit_invoice_days'].' Cash Inv Days: '.$customerDetail['cash_invoice_days'];
                                            ?>
                                        </td>
                                    </tr>
                                    <?php foreach($invArr as $detail):?>
                                        <tr>
                                            <td><?= $detail['id']?></td>
                                            <td><?= date('d-m-Y', $detail['invoice_date'])?></td>
                                            <td><?= $detail['net_total']?></td>
                                            <td><?= $detail['due']?></td>
                                            <td><?= date('d-m-Y', ($detail['invoice_date']+$customerDetail['credit_invoice_days']*24*3600))?></td>
                                            <td><?= round((time()-$detail['invoice_date'])/(24*3600))?></td>
                                        </tr>
                                    <?php endforeach;?>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>