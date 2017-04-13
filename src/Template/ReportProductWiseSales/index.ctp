<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Product Wise Sales Report') ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    <div class="col-lg-12">
                        <?= $this->Form->create('', ['class' => 'form-horizontal report_form', 'method' => 'post', 'role' => 'form', 'action' => 'loadReport/report']) ?>
                        <div class="col-md-12">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <?php
                                echo $this->Form->input('start_date', ['class' => 'form-control datepicker', 'required'=>'required', 'empty' => __('Select')]);
                                echo $this->Form->input('end_date', ['class' => 'form-control datepicker', 'required'=>'required', 'empty' => __('Select')]);
                                echo $this->Form->input('parent_level', ['options' => $parentLevels, 'label' => 'Parent Level', 'required'=>'required', 'class' => 'form-control level', 'empty' => __('Select')]);
                                echo $this->Form->input('global_id', ['options' => [], 'label' => 'Parent Unit', 'required'=>'required', 'empty' => __('Select'), 'class' => 'form-control parent-unit']);
                                //echo $this->Form->input('item_id', ['label' => 'Items','class' => 'form-control items', 'empty' => __('Select')]);
                                ?>
                            </div>
                        </div>
                        <div class="row text-center" style="margin-bottom: 20px;">
                            <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on("focus", ".datepicker", function () {
        $(this).removeClass('hasDatepicker').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
    $(document).on('change', '.category', function () {
        var obj = $(this);
        $('.items').empty();
        $(".items").append("<option>Select</option>");
        $.ajax({
            type: 'POST',
            url: '<?= $this->Url->build("/ReportProductWiseSales/ajax/get_items")?>',
            dataType: 'JSON',
            data: {global_id: obj.val()},
            success: function (data, status) {
                if (data.length != 0) {
                    $.each(data, function (key, value) {
                        $(".items").append("<option value =" + key + ">" + value + "</option>");
                    });
                }
            }
        });

    });
    $(document).on('change', '.level', function () {
        var obj = $(this);
        var level = obj.val();
        $('.parent-unit').select2('data', null);
        $.ajax({
            type: 'POST',
            url: '<?= $this->Url->build("/ReportProductWiseSales/ajax/units")?>',
            data: {level: level},
            success: function (response, status) {
                $('.parent-unit').empty();
                $('.parent-unit').append($('<option>').text("Select"));
                $.each(JSON.parse(response), function (key, value) {
                    $('.parent-unit').append($('<option>').text(value.unit_name).attr('value', value.global_id));
                });
            }
        });
    });

</script>