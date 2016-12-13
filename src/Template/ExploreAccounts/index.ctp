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
        <li><?= $this->Html->link(__('Explore Account'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Explore Account') ?>
                </div>
                <div class="tools">
                </div>
            </div>

            <div class="portlet-body">
                <form class="form-horizontal">
                    <br/>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 col-sm-offset-2 control-label">Accounts Owner Type:</label>

                        <div class="col-sm-4">
                            <select class="form-control" name="owner_type" id="owner_type">
                                <option value="">Select</option>
                                <option value="1">Customer</option>
                                <option value="2">Sales Force</option>

                            </select>
                        </div>
                    </div>
                    <br/>

                </form>

                <div class="" id="table_list">

                </div>

            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {


        $(document).on("focus", ".datepicker", function () {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('change', '#owner_type', function () {
            var obj = $(this);
            var owner_type = obj.val();

                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ExploreAccounts/getAccountHead")?>',
                    data: {owner_type: owner_type},
                    success: function (data, status) {
                        $('#table_list').html(data);
                    }
                });

        });

        $(document).on('change','.to_date', function(){
            var obj = $(this);
            var to_date= obj.val();
          var from_date=  obj.closest('.tbl_row ').find('.frome_date').val();
          var account_head_id=  obj.closest('.tbl_row ').find('.account_head').val();
            if(!from_date){
                obj.val('');
                alert('Please first select the from date')
            }else {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ExploreAccounts/getAmount")?>',
                    data: {from_date: from_date,to_date:to_date,account_head_id:account_head_id},
                    success: function (data, status) {
                        obj.closest('.tbl_row ').find('.total_amount').val(data);
                    }
                });
            }

        });


    });

</script>