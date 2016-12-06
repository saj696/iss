<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Customer Award Givings'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Customer Award Giving') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Customer Award Giving') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($customerAwardGiving, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('parent_level', ['options' => $parantsLevels, 'label' => 'Customers Parents Label', 'class' => 'form-control level', 'empty' => __('Select'), 'templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 levelContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('parent_unit', ['options' => [], 'label' => 'Customer Parent Unit', 'empty' => __('Select'), 'class' => 'form-control unit', 'templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 unitContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('customer_id', ['options' => [], 'empty' => __('Select'), 'empty' => __('Select'), 'class' => 'form-control customer', 'templates' => ['select' => '<div id="container_{{name}}" class="col-sm-9 customerContainer"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('award_account_code', ['options' => $awardTypes, 'label' => 'Award Type', 'empty' => __('Select')]);


                        //                        echo $this->Form->input('award_id', ['options' => $awards, 'empty' => __('Select')]);

                        //                        echo $this->Form->input('customer_award_id', ['options' => $customerAwards, 'empty' => __('Select')]);
                        //                        echo $this->Form->input('parent_global_id');
                        //                        echo $this->Form->input('amount');
                        //                        echo $this->Form->input('giving_mode');
                        //                        echo $this->Form->input('award_giving_date');
                        //                        echo $this->Form->input('created_by');
                        //                        echo $this->Form->input('created_date');
                        //                        echo $this->Form->input('updated_by');
                        //                        echo $this->Form->input('updated_date');
                        ?>
                        <?php //$this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                    <div class="col-md-12" id="result_table"></div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Adjustment Amount</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal"  method="post" action="<?php echo Router::url('/',true); ?>CustomerAwardGivings/adjustment" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Amount</label>
                        <div class="col-sm-10">
                            <input type="number" name="amount" class="form-control" id="" required>
                            <input type="hidden" name="id" class="form-control" id="amount_id" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-default">Save</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {


        // Parent Level Onchange function
        $(document).on('change', '.level', function () {
            var obj = $(this);
            var level = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CustomerAwardGivings/ajax/units")?>',
                data: {level: level},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear Unit Container
                    var $el = $('.unitContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Customer Container
                    var $el = $('.customerContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Invoice Container
                    var $el = $('.dueInvoiceContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Append Unit Container
                    $.each(data, function (key, value) {
                        $('.unitContainer select')
                            .append($("<option></option>")
                                .attr("value", key)
                                .text(value));
                    });
                }
            });
        });

        // Parent Unit Onchange function
        $(document).on('change', '.unit', function () {
            var obj = $(this);
            var unit = obj.val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/CustomerAwardGivings/ajax/customers")?>',
                data: {unit: unit},
                dataType: 'json',
                success: function (data, status) {

                    //   Clear Customer Container
                    var $el = $('.customerContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Clear Invoice Container
                    var $el = $('.dueInvoiceContainer select');
                    $el.empty();
                    $el.append($("<option></option>")
                        .attr("value", '').text('Select'));

                    //   Append Customer Container
                    $.each(data, function (key, value) {
                        $('.customerContainer select')
                            .append($("<option></option>")
                                .attr("value", key)
                                .text(value));
                    });
                }
            });
        });


        // Datepicker function
        $(document).on('focus', '.datepicker', function () {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: "dd-mm-yy"
            });
        });

        $(document).on('change', '#award-account-code', function () {

            var customer_id = $('#customer-id').val();
            var award_account_code = $(this).val();
            if (customer_id && award_account_code) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/CustomerAwardGivings/getCustomerAwards")?>',
                    data: {customer_id: customer_id, award_account_code: award_account_code},

                    success: function (data, status) {
                        $('#result_table').html(data);
                    }
                });
            }else {
                alert('Please select a Customer and Award Type')
            }
        });

    });
    $(document).on("click", ".adjustment", function (event) {

        var id = $(this).attr('data-row-id');
        $('#amount_id').val(id);
      //  console.log(id)

    });

</script>