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
                    <h4 class="text-center"><strong>Product Wise Monthly Sales Report </strong></h4>
                    <h4 class="text-center"><strong>Year: <strong><?php echo $data['year']; ?></strong></h4>
                    <h4 class="text-center"><strong><?= $administrative_unit ?> </strong></h4>
                </div>
                <div class="row">
                    <div class="col-md-12 report-table" style="overflow: auto;">
                        <table class="table table-bordered">
                            <thead>
                            <tr style="border-bottom: 3px solid lightgrey">
                                <td>Product Name</td>
                                <td>Pack Size</td>
                                <td>January</td>
                                <td>February</td>
                                <td>March</td>
                                <td>April</td>
                                <td>May</td>
                                <td>June</td>
                                <td>July</td>
                                <td>August</td>
                                <td>September</td>
                                <td>October</td>
                                <td>November</td>
                                <td>December</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sales as $key=>$detailArray) {
                                ?>
                                <tr>
                                    <td><?= explode('-', $key)[0]?></td>
                                    <td>
                                        <?php
                                        $unit_size = explode('-', $key)[1];
                                        $type = explode('-', $key)[2];
                                        if ($type != 5) {
                                            echo $unit_size . '-' . $unit_types[$type];
                                        } else {
                                            echo $unit_types[$type];
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[1]['CSH_PROD_QUANT'])?intval($detailArray[1]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[1]['CR_PROD_QUANT'])?intval($detailArray[1]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[2]['CSH_PROD_QUANT'])?intval($detailArray[2]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[2]['CR_PROD_QUANT'])?intval($detailArray[2]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[3]['CSH_PROD_QUANT'])?intval($detailArray[3]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[3]['CR_PROD_QUANT'])?intval($detailArray[3]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[4]['CSH_PROD_QUANT'])?intval($detailArray[4]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[4]['CR_PROD_QUANT'])?intval($detailArray[4]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[5]['CSH_PROD_QUANT'])?intval($detailArray[5]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[5]['CR_PROD_QUANT'])?intval($detailArray[5]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[6]['CSH_PROD_QUANT'])?intval($detailArray[6]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[6]['CR_PROD_QUANT'])?intval($detailArray[6]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[7]['CSH_PROD_QUANT'])?intval($detailArray[7]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[7]['CR_PROD_QUANT'])?intval($detailArray[7]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[8]['CSH_PROD_QUANT'])?intval($detailArray[8]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[8]['CR_PROD_QUANT'])?intval($detailArray[8]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[9]['CSH_PROD_QUANT'])?intval($detailArray[9]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[9]['CR_PROD_QUANT'])?intval($detailArray[9]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[10]['CSH_PROD_QUANT'])?intval($detailArray[10]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[10]['CR_PROD_QUANT'])?intval($detailArray[10]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[11]['CSH_PROD_QUANT'])?intval($detailArray[11]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[11]['CR_PROD_QUANT'])?intval($detailArray[11]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cash = isset($detailArray[12]['CSH_PROD_QUANT'])?intval($detailArray[12]['CSH_PROD_QUANT']):0;
                                        $credit = isset($detailArray[12]['CR_PROD_QUANT'])?intval($detailArray[12]['CR_PROD_QUANT']):0;
                                        echo $cash+$credit;
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>