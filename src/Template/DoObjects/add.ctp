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
            <?= $this->Html->link(__('Do Objects'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Product Indents') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add Product Indents') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($doObject, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">

                            <div class="col-md-12" id="file_wrapper" data-index_no="0">
                                <table class="table table-bordered ">
                                    <thead>
                                    <tr>
                                        <td>Itme</td>
                                        <td>Unit</td>
                                        <td>Quantity</td>
                                        <td>Action</td>
                                    </tr>
                                    </thead>

                                    <tbody class="file_container">
                                    <tr class="single_row">
                                        <td><?php echo $this->Form->input('do_object_items.0.item_id', ['options' => $items, 'required' => 'required', 'class' => 'item form-control', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('do_object_items.0.unit_id', ['options' => '', 'class' => 'unit form-control','required' => 'required', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('do_object_items.0.quantity', ['required' => 'required', 'class' => 'form-control', 'templates' => ['label' => '']]); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm add_file"><i class="fa fa-plus" aria-hidden="true"></i> </button>
                                            <button type="button" class="btn btn-danger btn-sm remove_file"><i class="fa fa-times"  aria-hidden="true"></i></button>
                                        </td>
                                    </tr>
                                    </tbody>

                                </table>

                            </div>

                            <div class="col-md-6 col-md-offset-3">
                                <?php
                                echo $this->Form->input('date', ['type' => 'text', 'label' => 'Date', 'class' => 'datepicker form-control']);
                                ?>
                            </div>
                            <div class="col-md-6 col-md-offset-3 recipient_row">
                                <?php echo $this->Form->input('do_events.0.recipient_id', ['options' => $recipients, 'class' => 'recipient form-control', 'empty' => __('Select')]);?>
                            </div>
                        </div>

                        <button class="btn blue pull-right save" style="margin:20px" type="submit">Save</button>
                        <button class="btn green pull-right save_send" style="margin-top:20px" type="button">Save & Send</button>

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

        $('.recipient_row').hide();
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $(document).on("focus", ".datepicker", function () {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('click', '.add_file', function () {

            var qq = $('#file_wrapper').attr('data-index_no');
            var index = parseInt(qq);

            $('#file_wrapper').attr('data-index_no', index + 1);

            var html = $('tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
            }).end();
            $('.file_container').append(html);
        });

        $(document).on('click', '.remove_file', function () {
            var obj = $(this);
            var count = $('.single_row').length;
            if (count > 1) {
                obj.closest('.single_row').remove();
            }
        });


        $(document).on('change', '.item', function () {

            var item_id = $(this).val();

            var obj = $(this);
            if (item_id) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/DoObjects/getItemUnits")?>',
                    data: {item_id: item_id},
                    dataType: 'json',

                    success: function (data, status) {
                        obj.closest('.single_row').find('.unit').html('');
                        obj.closest('.single_row').find('.unit').append("<option value=''><?= __('Select') ?></option>");
                        $.each(data, function (key, value) {
                            obj.closest('.single_row').find('.unit').append($("<option></option>").attr("value", key).text(value));
                        });
                    }
                });
            }else {
                alert('Please select a Customer and Award Type')
            }
        });

        $(document).on('click','.save_send',function(){
            $('.recipient_row').show();
            $(this).hide();
            $('.save').html('Save & Send');
        });
    })
</script>


