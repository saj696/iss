<?php
use Cake\Core\Configure;

?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Units'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Unit') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Unit') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($unit, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('unit_level', ['empty' => __('Select'), 'options' => Configure::read('unit_levels')]);
                        echo $this->Form->input('constituent_unit_id', ['label' => 'Constituent Level', 'required' => true]);
                        echo $this->Form->input('unit_name'); ?>
                        <?php
                        echo $this->Form->input('is_bulk', ['value' => 0, 'type' => 'checkbox', 'class' => 'form-control chk', 'templates' => ['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' => '<label {{attrs}} class="col-sm-1 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);

                        echo $this->Form->input('unit_size', ['id' => 'unit-size', 'required' => true]);
                        echo $this->Form->input('unit_type', ['options' => Configure::read('pack_size_units')]);
                        echo $this->Form->input('is_sales_unit', ['value' => '0', 'type' => 'checkbox', 'class' => 'form-control chk-sales-unit', 'templates' => ['inputContainer' => '<div class="form-group input {{required}}">{{content}}</div>', 'label' => '<label {{attrs}} class="col-sm-1 control-label text-right" >{{text}}</label>', 'input' => '<div class="col-sm-7 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);

                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue center-block', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {

        $("#constituent-unit-id").hide();
        $('label[for=constituent-unit-id], input#constituent-unit-id').hide();
        $('#unit-size').show();
        $('label[for=unit-size], input#unit-size').show();

        $(document).on("change", "#unit-level", function () {
            var obj = $(this);
            var level = parseInt(obj.val());
            $("#constituent-unit-id").show();
            $('label[for=constituent-unit-id], input#constituent-unit-id').show();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Units/ajax")?>',
                data: {level: level},
                success: function (data, status) {
                    if (level == 1) {
                        obj.closest('.input').next().find('#constituent-unit-id').html('');
                        obj.closest('.input').next().find('#constituent-unit-id').html('<option value="">Select</option>');
                        obj.closest('.input').next().find('#constituent-unit-id').removeAttr('required');
                    }
                    else {
                        obj.closest('.input').next().find('.col-sm-9').html('');
                        obj.closest('.input').next().find('.col-sm-9').html(data);
                    }
                }
            });


        });

        $(document).on("change", "#constituent-unit-id", function () {
            $.ajax({
                type: 'GET',
                dataType: 'JSON',
                url: '<?= $this->Url->build("/Units/setUnitType/")?>' + $(this).attr('value'),
                success: function (data, status) {
                    $("#unit-type").val(parseInt(data.unit_type));

                }
            });

        });
        $('input[name="is_bulk"]').bind('change', function () {
            if ($(".chk").attr("checked")) {
                $('input[name="is_bulk"]').val("1");
                $('#unit-size').hide();
                $('#unit-size').removeAttr('required');
                $('label[for=unit-size], input#unit-size').hide();
            }

            else {

                $('#unit-size').show();
                $('input[name="is_bulk"]').val("0");
                $('#unit-size').attr('required');
                $('label[for=unit-size], input#unit-size').show();
            }
        });

        $('input[name="is_sales_unit"]').bind('change', function () {
            if ($(".chk-sales-unit").attr("checked")) {
                $('input[name="is_sales_unit"]').val("1");
            }
        });
    });
</script>