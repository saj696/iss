<?php
error_reporting(0);
$webroot = $this->request->webroot;
$unit_type = \Cake\Core\Configure::read('pack_size_units');
?>
<div class="col-md-12" style="margin-top: 20px;">
    <div class="portlet-body">
        <div class="portlet-body">
            <?php if (!isset($btnHide)): ?>
                <div class="col-md-12">
                    <button class="btn btn-circle red icon-print2" style="float: right; margin-bottom: 10px" onclick="print_rpt(<?= $webroot ?>)">&nbsp;Print&nbsp;</button>
                    <?= $this->Form->create('', ['class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'action' => 'loadReport/pdf']) ?>

                    <input type="hidden" name="global_id" value="<?= $data['global_id'] ?>">
                    <input type="hidden" name="start_date" value="<?= $data['start_date'] ?>">
                    <input type="hidden" name="end_date" value="<?= $data['end_date'] ?>">
                    <input type="hidden" name="parent_level" value="<?= $data['parent_level'] ?>">
                    <button type="submit" class="pdf btn btn-circle yellow icon-print2" style="float: right; margin-bottom: 10px; margin-right: 10px;">&nbsp;PDF&nbsp;</button>
                    <?= $this->Form->end() ?>
                </div>
            <?php endif; ?>
            <div id="PrintArea">
                <?= $this->element('company_header'); ?>
                <div class="row">
                    <h4 class="text-center"><strong>Product Wise Sales Report </strong></h4>
                    <h4 class="text-center"><strong>From: <strong><?php echo $start_date; ?></strong></h4>
                    <h4 class="text-center"><strong>To: <strong><?php echo $end_date; ?></strong></h4>
                    <h4 class="text-center"><strong><?= $administrative_unit ?> </strong></h4>
                </div>
                <div class="row">
                    <div class="col-md-12 report-table" style="overflow: auto;">
                        <table class="table table-bordered">
                            <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td>Code</td>
                                <td>Product Name</td>
                                <td>Pack Size</td>
                                <td>Cash Bonus Quantity</td>
                                <td>Cash Product Quantity</td>
                                <td>Cash Sales Price</td>
                                <td>Cash Sales Value</td>
                                <td>Credit Bonus Quantity</td>
                                <td>Credit Product Quantity</td>
                                <td>Credit Sales Price</td>
                                <td>Credit Sales Value</td>
                                <td>Total Sales Value</td>
                                <td>Total Bonus Value</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($sales as $sales_data): ?>
                                <tr>
                                    <td><?= $sales_data['code']; ?></td>
                                    <td><?= $sales_data['ITEM_NAME']; ?></td>
                                    <td>
                                        <?php
                                        if ($sales_data['unit_type'] != 5) {
                                            echo $sales_data['unit_size'] . '-' . $unit_type[$sales_data['unit_type']];
                                        } else {
                                            echo $unit_type[$sales_data['unit_type']];
                                        }
                                        ?>
                                    </td>
                                    <td><?php if (isset($sales_data['CSH_BONUS_QUANT'])) {
                                            //echo $sales_data['CSH_BONUS_QUANT'];
                                            echo $this->System->get_unit_quantity($sales_data['unit_type'], $sales_data['unit_size'], $sales_data['CSH_BONUS_QUANT'], $sales_data['converted_quantity']);
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php if (isset($sales_data['CSH_PROD_QUANT'])) {
                                            //echo $sales_data['CSH_PROD_QUANT'];
                                            echo $this->System->get_unit_quantity($sales_data['unit_type'], $sales_data['unit_size'], $sales_data['CSH_PROD_QUANT'], $sales_data['converted_quantity']);
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php if (isset($sales_data['cash_sales_price'])) {
                                            echo $sales_data['cash_sales_price'];
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php
                                        $cash_sales_value = $sales_data['cash_sales_price'] * $sales_data['unit_size'];
                                        echo $cash_sales_value;
                                        ?>
                                    </td>
                                    <td><?php if (isset($sales_data['CR_BONUS_QUANT'])) {
                                            //echo $sales_data['CR_BONUS_QUANT'];
                                            echo $this->System->get_unit_quantity($sales_data['unit_type'], $sales_data['unit_size'], $sales_data['CR_BONUS_QUANT'], $sales_data['converted_quantity']);
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php if (isset($sales_data['CR_PROD_QUANT'])) {
                                            // echo $sales_data['CR_PROD_QUANT'];
                                            echo $this->System->get_unit_quantity($sales_data['unit_type'], $sales_data['unit_size'], $sales_data['CR_PROD_QUANT'], $sales_data['converted_quantity']);
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php if (isset($sales_data['credit_sales_price'])) {
                                            echo $sales_data['credit_sales_price'];
                                        } else {
                                            echo 0;
                                        } ?>
                                    </td>
                                    <td><?php
                                        $credit_sales_value = $sales_data['credit_sales_price'] * $sales_data['unit_size'];
                                        echo $credit_sales_value;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $total_sales_value = $cash_sales_value + $credit_sales_value;
                                        echo $total_sales_value;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $total_bonus_value = $sales_data['CSH_BONUS_QUANT'] * $cash_sales_value + $sales_data['CR_BONUS_QUANT'] * $credit_sales_value;
                                        echo $total_bonus_value;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>