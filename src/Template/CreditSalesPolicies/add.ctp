<?php
use Cake\Core\Configure;
//echo '<pre>';
//print_r($policyDetail);
//echo '</pre>';
//exit;
?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Policies'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Policy') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Policy') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($creditSalesPolicy, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                    <?php
                    echo $this->Form->input('policy_start_date', ['type'=>'text', 'class'=>'form-control datepicker']);
                    echo $this->Form->input('policy_expected_end_date', ['type'=>'text', 'class'=>['form-control datepicker']]);
                    ?>
                    <div class="form-group input text">
                        <label for="policy-expected-end-date" class="col-sm-3 control-label text-right">Policy Expected End Date</label>
                        <div class="col-sm-9 container_policy_expected_end_date">
                            <textarea rows="14" cols="50" name="policy_detail" readonly="readonly" class="form-control"><?php echo json_encode($policyDetail)?></textarea>
                        </div>
                    </div>
                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                    </div>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on("focus",".datepicker", function()
        {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });
    });
</script>