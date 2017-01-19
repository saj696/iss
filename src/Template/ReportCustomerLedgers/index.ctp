<?php
use Cake\Core\Configure;
$status = Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Customer Ledger Report'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Customer Ledger Report') ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create('',['class' => 'form-horizontal report_form','method'=>'get', 'role' => 'form', 'action'=>'loadReport/report']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('start_date', ['type'=>'text', 'class'=>'datepicker form-control', 'required'=>'required']);
                        echo $this->Form->input('end_date', ['type'=>'text', 'class'=>'datepicker form-control', 'required'=>'required']);
                        echo $this->Form->input('explore_level', ['label'=>'Customer Level', 'options'=>$exploreLevels, 'class'=>'form-control explore_level', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('parent_unit', ['options'=>[], 'class'=>'parent_unit form-control', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('unit_id', ['empty'=>'Select', 'required'=>'required', 'class'=>'form-control unit']);
                        echo $this->Form->input('customer_id', ['empty'=>'Select', 'required'=>'required', 'class'=>'form-control customer']);
                        ?>
                    </div>
                    <div class="col-md-12 text-center">
                        <?= $this->Form->button(__('Search'), ['class' => 'btn yellow', 'style' => 'margin:10px 0 20px 0']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on("focus",".datepicker", function() {
            $(this).datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('change', '.explore_level', function () {
            var obj = $(this);
            var explore_level = obj.val();
            obj.closest('.input').next().find('.parent_unit').html('<option value="">Select</option>');
            $('.unit').html('<option value="">Select</option>');

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportCustomerLedgers/ajax/parent_units")?>',
                data: {explore_level: explore_level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });

        $(document).on('change', '.parent_unit', function () {
            var obj = $(this);
            var parent_unit = obj.val();
            var explore_level = $('.explore_level').val();
            obj.closest('.input').next().find('.unit').html('<option value="">Select</option>');
            $('.unit').html('<option value="">Select</option>');

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportCustomerLedgers/ajax/units")?>',
                data: {parent_unit:parent_unit, explore_level: explore_level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });

        $(document).on('change', '.unit', function () {
            var obj = $(this);
            var unit = obj.val();
            obj.closest('.input').next().find('.customer').html('<option value="">Select</option>');
            $('.customer').html('<option value="">Select</option>');

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportCustomerLedgers/ajax/customers")?>',
                data: {unit:unit},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });
    });
</script>