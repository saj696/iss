<?php
///error_reporting(0);
$webroot = $this->request->webroot;
//$unit_type = \Cake\Core\Configure::read('pack_size_units');
?>
<div class="col-md-12" style="margin-top: 20px;">
    <div class="portlet-body">
        <div class="portlet-body">
            <?php if (!isset($btnHide)): ?>
                <div class="col-md-12">
                    <button class="btn btn-circle red icon-print2" style="float: right; margin-bottom: 10px"
                            onclick="print_rpt(<?= $webroot ?>)">&nbsp;Print&nbsp;</button>
                    <?= $this->Form->create('', ['class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'action' => 'loadReport/pdf']) ?>

                    <input type="hidden" name="global_id" value="<?= $data['global_id'] ?>">

                    <input type="hidden" name="parent_level" value="<?= $data['parent_level'] ?>">
                    <input type="hidden" name="level" value="<?= $data['level'] ?>">
                    <button type="submit" class="pdf btn btn-circle yellow icon-print2"
                            style="float: right; margin-bottom: 10px; margin-right: 10px;">&nbsp;PDF&nbsp;</button>
                    <?= $this->Form->end() ?>
                </div>
            <?php endif; ?>
            <div id="PrintArea">
                <?= $this->element('company_header'); ?>
                <div class="row">
                    <h4 class="text-center"><strong>Invoice Due in various time span </strong></h4>

                </div>
                <div class="row">
                    <div class="col-md-12 report-table" style="overflow: auto;">
                        <table class="table table-bordered report">

                            <thead>
                            <tr style="border-bottom: 3px solid lightgrey">

                                <td><?= __('Location / Name') ?></td>
                                <td><?= __('Credit Limit') ?></td>
                                <td><?= __('0-30 days') ?></td>
                                <td><?= __('31-60 days') ?></td>
                                <td><?= __('61-90 days') ?></td>
                                <td><?= __('91-120 days') ?></td>
                                <td><?= __('121-150 days') ?></td>
                                <td><?= __('151-180 days') ?></td>
                                <td><?= __('181-360 days') ?></td>
                                <td><?= __('361-720 days') ?></td>
                                <td><?= __('More Than 720 days') ?></td>
                                <td><?= __('Opening Due') ?></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $due_30 = 0;
                            $sum_due_30 = 0;
                            $due_60 = 0;
                            $sum_due_60 = 0;
                            $due_90 = 0;
                            $sum_due_90 = 0;
                            $due_120 = 0;
                            $sum_due_120 = 0;
                            $due_150 = 0;
                            $sum_due_150 = 0;
                            $due_180 = 0;
                            $sum_due_180 = 0;
                            $due_360 = 0;
                            $sum_due_360 = 0;
                            $due_720 = 0;
                            $sum_due_720 = 0;
                            $due_more_than_720 = 0;
                            $sum_due_more_720 = 0;
                            $opening_due = 0;
                            $total_credit_limit = 0;
                            $total_opening_due = 0;
                           // pr($name);
                            //die;
                            ?>
                            <?php foreach ($invoice_age_report as $administrative_unit => $data): ?>
                                <tr>
                                    <td>
                                        <?php foreach ($name as $key => $value) {
                                            if ($administrative_unit == $value['global_id']) {
                                                echo $value['name'];
                                                if (isset($value['is_blacklisted']) && $value['is_blacklisted'] == 1) {

                                                    echo '-(Black)-';
                                                }
                                            }
                                        } ?>
                                    </td>
                                    <td>
                                        <?php foreach ($credit_limit as $key => $value) {
                                            $total_credit_limit += $value['CREDIT_LIMIT'];
                                            if ($administrative_unit == $value['GLOBAL_ID']) {
                                                echo $this->Number->format($value['CREDIT_LIMIT']);
                                            }
                                        } ?>
                                    </td>

                                    <?php foreach ($data as $time_span => $result): ?>
                                        <?php if ($time_span == 1): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_30 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>

                                        <?php elseif ($time_span == 2): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_60 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>

                                        <?php elseif ($time_span == 3): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_90 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>


                                        <?php elseif ($time_span == 4): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_120 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>


                                        <?php elseif ($time_span == 5): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_150 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>


                                        <?php elseif ($time_span == 6): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_180 += $value['DUE'];

                                                ?>
                                            <?php endforeach; ?>


                                        <?php elseif ($time_span == 7): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_360 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>

                                        <?php elseif ($time_span == 8): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_720 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>

                                        <?php elseif ($time_span == 9): ?>
                                            <?php foreach ($result as $key => $value): ?>
                                                <?php $due_more_than_720 += $value['DUE'];
                                                ?>
                                            <?php endforeach; ?>


                                        <?php endif; ?>

                                        <?php
                                        $opening_due = $due_30 +
                                            $due_60 +
                                            $due_90 +
                                            $due_120 +
                                            $due_150 +
                                            $due_180 +
                                            $due_360 +
                                            $due_720 +
                                            $due_more_than_720;
                                        ?>
                                    <?php endforeach; ?>
                                    <td class="due-30">

                                        <?php
                                        echo $this->Number->format($due_30);
                                        ?>
                                    </td>

                                    <td class="due-60">
                                        <?php
                                        echo $this->Number->format($due_60);
                                        ?>
                                    </td>
                                    <td class="due-90">
                                        <?php
                                        echo $this->Number->format($due_90);
                                        ?>
                                    </td>
                                    <td class="due-120">
                                        <?php
                                        echo $this->Number->format($due_120);
                                        ?>
                                    </td>
                                    <td class="due-150">
                                        <?php
                                        echo $this->Number->format($due_150);
                                        ?>
                                    </td>
                                    <td class="due-180">
                                        <?php
                                        echo $this->Number->format($due_180);
                                        ?>
                                    </td>

                                    <td class="due-360">

                                        <?php echo $this->Number->format($due_360);
                                        ?>
                                    </td>

                                    <td class="due-720">
                                        <?php
                                        echo $this->Number->format($due_720);
                                        ?>
                                    </td>

                                    <td class="due-more-720">
                                        <?php
                                        echo $this->Number->format($due_more_than_720);
                                        ?>
                                    </td>
                                    <td>
                                        <strong><?= $this->Number->format($opening_due); ?></strong>
                                    </td>
                                    <?php
                                    $sum_due_30 += $due_30;
                                    $sum_due_60 += $due_60;
                                    $sum_due_90 += $due_90;
                                    $sum_due_120 += $due_120;
                                    $sum_due_150 += $due_150;
                                    $sum_due_180 += $due_180;
                                    $sum_due_360 += $due_360;
                                    $sum_due_720 += $due_720;
                                    $sum_due_more_720 += $due_more_than_720;
                                    $total_opening_due += $opening_due;
                                    ?>
                                    <?php
                                    $due_30 = 0;
                                    $due_60 = 0;
                                    $due_90 = 0;
                                    $due_120 = 0;
                                    $due_150 = 0;
                                    $due_180 = 0;
                                    $due_360 = 0;
                                    $due_720 = 0;
                                    $due_more_than_720 = 0;
                                    $opening_due = 0;
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                            <td>Total</td>
                            <td><strong><?= $this->Number->format($total_credit_limit) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_30) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_60) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_90) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_120) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_150) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_180) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_360) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_720) ?></strong></td>
                            <td><strong><?= $this->Number->format($sum_due_more_720) ?></strong></td>
                            <td style="background-color: #00CC00"><?= $this->Number->format($total_opening_due) ?></td>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        //        var total_sum_30 = 0
        //        total_sum_60 = 0
        //        total_sum_90 = 0
        //        total_sum_120 = 0
        //        total_sum_150 = 0
        //        total_sum_180 = 0
        //        total_sum_360 = 0
        //        total_sum_720 = 0
        //        total_sum_more_720 = 0
        //        var getSum = function (field, sum_field) {
        //            $('.report').find(field).each(function (index, element) {
        //                sum_field += $(element).html();
        //                console.log(sum_field);
        //            });
        //            return sum_field;
        //        };
        //
        //        console.log(getSum('.due-90', total_sum_90));
        //        //$('.report').find('.total-sum-30').html(getSum('.due-30',total_sum_30));
        //        // $('.report').find('.total-sum-60').html(getSum('.due-60',total_sum_60));
        //        // $('.report').find('.total-sum-90').html(getSum('.due-90',total_sum_90));
        //        // $('.report').find('.total-sum-120').html(getSum('.due-120',total_sum_120));
        //        //$('.report').find('.total-sum-150').html(getSum('.due-150',total_sum_150));
    </script>