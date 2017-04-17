<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css"
        type="text/javascript"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css"
      rel="stylesheet"
      type="text/css"/>
<style>
    label {
        margin-left: 20px;
    }

    #datepicker {
        width: 180px;
        margin: 0 20px 20px 20px;
    }

    #datepicker > span:hover {
        cursor: pointer;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Invoice Age wise  report') ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    <div class="col-lg-12">
                        <?= $this->Form->create('', ['class' => 'form-horizontal report_form', 'method' => 'post', 'role' => 'form', 'action' => 'loadReport/report']) ?>
                        <div class="col-md-12">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">

                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <label>Location </label>
                                    </div>
                                    <div class="col-md-8">
                                        <?php echo $this->Form->input('parent_level', ['options' => $parentLevels, 'required' => true, 'label' => false, 'class' => 'form-control level']); ?>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="col-md-3">

                                    </div>
                                    <div class="col-md-8">
                                        <?php echo $this->Form->input('global_id', ['options' => [], 'label' => '', 'required' => true, 'empty' => __('Select'), 'class' => 'form-control parent-unit']); ?>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="col-md-3">
                                        <label>Group By  </label>
                                    </div>
                                    <div class="col-md-8">
                                        <?php echo $this->Form->input('level', ['options' => [], 'label' => false, 'required' => true, 'empty' => __('Select'), 'class' => 'form-control parent-unit-final']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=" row text-center
                            " style="margin-bottom: 20px;">
                            <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
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

        $(document).on('change', '.level', function () {
            var obj = $(this);
            var level = obj.val();
            $('.parent-unit').select2('data', null);
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportInvoiceAge/ajax/units")?>',
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

        $(document).on('change', '.parent-unit', function () {
            var obj = $(this);
            var level = $('.level').val();
            //  $('.parent-unit').select2('data', null);
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportInvoiceAge/ajax/child")?>',
                data: {level: level, global_id: obj.val()},
                success: function (response, status) {
                    $('.parent-unit-final').empty();
                    //$('.parent-unit-final').append($('<option>').text("Select"));
                    $.each(JSON.parse(response), function (key, value) {

                        $('.parent-unit-final').append($('<option>').text(value.level_name).attr('value', value.level_no));
                    });
                }
            });
        });

        $(function () {
            $(".datepicker").datepicker({
                autoclose: true,
                todayHighlight: true
            }).datepicker('update', new Date());
        });

    </script>