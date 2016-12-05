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
            <?= $this->Html->link(__('Credit Note Items'), ['controller'=>'CreditNotes','action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Credit Note Item') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Credit Notes') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['controller'=>'CreditNoteItems','action' => 'created_credit_notes'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($creditNoteItem, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php

                        echo $this->Form->input('parent_level', ['options' => $parantsLevels, 'label' => 'Customers Parents Level', 'class' => 'form-control level', 'empty' => __('Select')]);
                        echo $this->Form->input('parent_unit', ['options' => [], 'label' => 'Parent Unit', 'empty' => __('Select'), 'class' => 'form-control parent-unit']);
                        echo $this->Form->input('customer', ['options' => [], 'label' => 'Customer', 'empty' => __('Select'), 'class' => 'form-control customer', 'required' => true]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable">
                                    <tr>
                                        <th><?= __('Invoice') ?></th>
                                        <th><?= __('Item Unit') ?></th>
                                        <th><?= __('Quantity') ?></th>
                                        <th><?= __('Net Total') ?></th>
                                        <th><?= __('Actions') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td><?php echo $this->Form->input('CreditNoteItems.0.invoice_id', ['style' => 'width: 100%', 'required' => 'required', 'type' => 'text', 'class' => 'form-control invoice numbersOnly', 'templates' => ['label' => '']]); ?></td>

                                        <td style="width:29%;">
                                            <?php echo $this->Form->input('CreditNoteItems.0.item_id', ['required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control items', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?>
                                        <td><?php echo $this->Form->input('CreditNoteItems.0.quantity', ['style' => 'width: 100%', 'type' => 'text', 'required' => 'required', 'class' => 'form-control quantity numbersOnly', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('CreditNoteItems.0.net_total', ['style' => 'width: 100%', 'type' => 'text', 'required' => 'required', 'class' => 'form-control net-total numbersOnly', 'readonly' => true, 'templates' => ['label' => '']]); ?></td>
                                        <td width="50px;"><span
                                                class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-12 col-md-offset-8">
                                <div class="col-md-12">
                                    <div class="col-md-1">

                                    </div>
                                    <div class="col-md-11" id="total-value-for-view">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
                    </div>
                    <div class="row text-center">
                        <input type="checkbox" class="form-control forward_check" value="1"/> Send For Approval?
                    </div>
                    <br>
                    <div class="row col-md-offset-5 text-center recipient_div hidden">
                        <?php echo $this->Form->input('recipient_id', ['options' => $recipient_list, 'empty' => 'Select user', 'style' => 'width:40%;', 'class' => 'form-control recipient_id', 'templates' => ['label' => '']]); ?>
                    </div>

                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Save'), ['name' => 'for_save', 'id' => 'save', 'class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                        <?= $this->Form->button(__('Send For Approval'), ['disabled' => true, 'name' => 'for_approval', 'id' => 'approve', 'style' => 'margin-top:20px', 'class' => 'btn green']) ?>

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
        // $("#total-value-for-view").html("Amran");
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        var total_value_for_view = 0;
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
        $(document).on('change', '.level', function () {
            var obj = $(this);
            var level = obj.val();
            $('.customer').select2('data', null);
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CreditNoteItems/ajax/units")?>',
                data: {level: level},
                success: function (response, status) {
                    $('.parent-unit').empty();
                    $('.parent-unit').append($('<option>').text("Select"));
                    $.each(JSON.parse(response), function (key, value) {
                        $('.parent-unit').append($('<option>').text(value).attr('value', key));
                    });
                }
            });


        });

        $(document).on('change', '.parent-unit', function () {
            var obj = $(this);
            var unit = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CreditNoteItems/ajax/customers")?>',
                data: {unit: unit},
                dataType: 'json',
                success: function (response, status) {
                    $.each(response, function (key, value) {
                        $('.customer').append($('<option>').text(value).attr('value', key));
                    });
                }
            });
        });
        $(document).on('keyup', '.invoice', function () {
            var obj = $(this);
            var invoice_id = obj.val();
            $("#total-value-for-view").html('');
            obj.closest('tr').find('.quantity').val('');
            obj.closest('tr').find('.net-total').val('');
            if (invoice_id == "" || invoice_id == undefined) {
                return false;
            }
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CreditNoteItems/ajax/invoices")?>',
                data: {invoice_id: invoice_id},
                success: function (response, status) {
                    obj.closest('tr').find('.items').empty();
                    $.each(JSON.parse(response), function (key, value) {
                        obj.closest('tr').find('.items').append($('<option>').text(value).attr('value', key));
                    });
                }
            });
        });

        $(document).on('keyup', '.quantity', function () {
            var obj = $(this);
            var quantity = obj.val();
            $("#total-value-for-view").html('');
            var item_id = obj.closest('tr').find('.items').val();
            obj.closest('tr').find('.net-total').val('');
            console.log(item_id);
            console.log(quantity);
            if (quantity == "" || quantity == 0 || quantity == undefined) {
                obj.closest('tr').find('.net-total').val('');
                return false;
            }
            if (item_id == undefined || item_id == "")
                return alert("Please Select Item")
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CreditNoteItems/ajax/net_total")?>',
                data: {item_id: item_id, quantity: quantity},
                dataType: 'json',
                success: function (response, status) {
                    obj.closest('tr').find('.net-total').val(response);
                    $("#total-value-for-view").html('Total: ' + getSum());
                    $("#total-value-for-view").css("font-weight", "Bold");
                    $("#total-value-for-view").css("fontSize", "20px");
                }
            });
        });

        //ite


        $(document).on('change', '.items', function () {
            var obj = $(this);
            var item_id = obj.val();
            var quantity = obj.closest('tr').find('.quantity').val();
            console.log(item_id);
            console.log(quantity);
            if (quantity == "" || quantity == 0 || quantity == undefined || item_id == '' || item_id == undefined) {
                return alert("Please Select Item and quantity")
            }
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CreditNoteItems/ajax/net_total")?>',
                data: {item_id: item_id, quantity: quantity},
                dataType: 'json',
                success: function (response, status) {
                    obj.closest('tr').find('.net-total').val(response);
                    $("#total-value-for-view").html('Total: ' + getSum());
                    $("#total-value-for-view").css("font-weight", "Bold");
                    $("#total-value-for-view").css("fontSize", "20px");

                }
            });
        });

        $(document).on('click', '.forward_check', function () {
            if ($(this).attr('checked')) {
                $("#save").attr('disabled', true);
                $(document).on('change', '#recipient-id', function () {
                    $(this).val() != '' ? $("#approve").attr('disabled', false) : $("#approve").attr('disabled', true);
                });
                $(".recipient_div").removeClass('hidden');
            } else {
                $("#save").attr('disabled', false);
                $("#approve").attr('disabled', true);
                $(".recipient_div").addClass('hidden');
            }
        });

        var getSum = function () {
            var sum = 0;
            var selector = '.net-total';
            $('.moreTable').find(selector).each(function (index, element) {
                sum += parseInt($(element).val());
            });
            return sum;
        };


    });
</script>
