<?php
use Cake\Core\Configure;

$quantity = Configure::read('pack_size_units');
?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Production Rules'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Add Production Rule') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Production Rule') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($productionRule, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <div class="table-scrollable">
                                    <table class="table table-bordered table-hover moreTable">
                                        <tr>
                                            <th><?= __('Input Item') ?></th>
                                            <th><?= __('Input Unit') ?></th>
                                            <th><?= __('Input Quantity') ?></th>
                                            <th><?= __('Output Item') ?></th>
                                            <th><?= __('Output Unit') ?></th>
                                            <th><?= __('Output Quantity') ?></th>
                                            <th></th>
                                        </tr>
                                        <tr class="item_tr single_list">
                                           <td  width="190px;"><?php echo $this->Form->input('ProductionRules.0.input_item_id', ['options' => $inputItems, 'required' => 'required', 'class' => 'form-control input-items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?>
                                            <td  width="190px;"><?php echo $this->Form->input('ProductionRules.0.input_unit_id', ['empty' => __('Select'), 'required' => 'required', 'class' => 'form-control input-units', 'templates' => ['label' => '']]); ?></td>
                                            <td><?php echo $this->Form->input('ProductionRules.0.input_quantity', ['required' => 'required', 'class' => 'form-control numbersOnly', 'templates' => ['label' => '']]); ?></td>

                                            <td width="190px;"><?php echo $this->Form->input('ProductionRules.0.output_item_id', ['options' => $outputItems, 'required' => 'required','class' => 'form-control output-items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?>
                                            <td  width="190px;"><?php echo $this->Form->input('ProductionRules.0.output_unit_id', ['empty' => __('Select'), 'required' => 'required', 'class' => 'form-control output-units', 'templates' => ['label' => '']]); ?></td>
                                            <td><?php echo $this->Form->input('ProductionRules.0.output_quantity', ['required' => 'required', 'class' => 'form-control numbersOnly', 'templates' => ['label' => '']]); ?></td>

                                            <td><span
                                                    class="btn btn-sm btn-circle btn-danger remove pull-right">X</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
                    </div>

                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
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
        var input_item_id;
        var output_item_id;
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
            }).end();

            $('.moreTable').append(html);
        });

        $(document).on('click', '.remove', function () {
            var obj = $(this);
            var count = $('.single_list').length;
            if (count > 1) {
                obj.closest('.single_list').remove();
            }
        });
        $(document).on('change', '.input-items', function () {
            input_item_id = $(this).val();
            if (input_item_id == $(this).closest('tr').find('.output-items').val()) {
                $(this).val('');
                return alert('Input Item and Output Item Can not be same');
            }
            var thisRow = $(this).closest('tr');
            console.log(input_item_id);
            $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + input_item_id
                , function (response) {
                    thisRow.find('.input-units').empty();
                    var options = "";
                    $.each(response, function (index, data) {
                        if (data == null) {
                            return alert("Please Create Bulk Unit For The Item")
                        }
                        options += "<option value='" + index + "'>" + data + "</option>";
                    });
                    thisRow.find('.input-units').append(options);

                });
        });

        $(document).on('change', '.output-items', function () {
            output_item_id = $(this).val();
            if (output_item_id == $(this).closest('tr').find('.input-items').val()) {
                $(this).val('');
                return alert('Input Item and Output Item Can not be same');
            }
            var thisRow = $(this).closest('tr');
            console.log(output_item_id);
            $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + output_item_id
                , function (response) {
                    thisRow.find('.output-units').empty();
                    var options = "";
                    $.each(response, function (index, data) {
                        if (data == null) {
                            return alert("Please Create Bulk Unit For The Item")
                        }
                        options += "<option value='" + index + "'>" + data + "</option>";
                    });
                    thisRow.find('.output-units').append(options);

                });
        });
    });
</script>