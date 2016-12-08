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
        <li><?= __('Edit Production Rule') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Production Rule') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($productionRule, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <?php
                            echo $this->Form->input('input_item_id', ['empty' => __('Select'), 'required' => true, 'class' => 'form-control input-items']);
                            echo $this->Form->input('input_unit_id', ['required' => true, 'class' => 'form-control input-units']);
                            echo $this->Form->input('input_quantity', ['options' => $quantity]);
                            ?>

                        </div>
                        <div class="col-md-6">
                            <?php
                            echo $this->Form->input('output_item_id', ['empty' => __('Select'), 'required' => true, 'class' => 'form-control output-items']);
                            echo $this->Form->input('output_unit_id', ['required' => true, 'class' => 'form-control output-units']);
                            echo $this->Form->input('output_quantity', ['options' => $quantity]);
                            echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                            ?>
                        </div>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
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
        var input_item_id = '<?php echo $productionRule['input_item_id'];?>';
        var output_item_id = '<?php echo $productionRule['output_item_id'];?>';

        $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + input_item_id
            , function (response) {
                $('.input-units').empty();
                var options = "";
                $.each(response, function (index, data) {
                    if (data == null) {
                        return alert("Please Create Bulk Unit For The Item")
                    }
                    options += "<option value='" + index + "'>" + data + "</option>";
                });
                $('.input-units').append(options);

            });
        $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + output_item_id
            , function (response) {
                $('.output-units').empty();
                var options = "";
                $.each(response, function (index, data) {
                    if (data == null) {
                        return alert("Please Create Bulk Unit For The Item")
                    }
                    options += "<option value='" + index + "'>" + data + "</option>";
                });
                $('.output-units').append(options);

            });
        $(document).on('change', '.input-items', function () {
            input_item_id = $(this).val();
            if (input_item_id == $('.output-items').val()) {
                $(this).prop('selectedIndex', 0);
                return alert('Input Item and Output Item Can not be same');
            }

            console.log(input_item_id);
            $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + input_item_id
                , function (response) {
                    $('.input-units').empty();
                    var options = "";
                    $.each(response, function (index, data) {
                        if (data == null) {
                            return alert("Please Create Bulk Unit For The Item")
                        }
                        options += "<option value='" + index + "'>" + data + "</option>";
                    });
                    $('.input-units').append(options);

                });
        });

        $(document).on('change', '.output-items', function () {
            output_item_id = $(this).val();
            if (output_item_id == $('.input-items').val()) {
                $(this).prop('selectedIndex', 0);
                return alert('Input Item and Output Item Can not be same');
            }
            console.log(output_item_id);
            $.getJSON('<?php echo $this->url->build('/ProductionRules/getBulkUnit/');?>' + output_item_id
                , function (response) {
                    $('.output-units').empty();
                    var options = "";
                    $.each(response, function (index, data) {
                        if (data == null) {
                            return alert("Please Create Bulk Unit For The Item")
                        }
                        options += "<option value='" + index + "'>" + data + "</option>";
                    });
                    $('.output-units').append(options);

                });
        });
    });
</script>