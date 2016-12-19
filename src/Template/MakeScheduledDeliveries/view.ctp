<?php
$status = \Cake\Core\Configure::read('status_options');
use Cake\Routing\Router;
use App\View\Helper\SystemHelper;

//echo "<pre>";
//print_r($recipients->toArray());
//die();

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
        <li><?= __('View Do Object') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Do Object Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group input select">
                            <label for="" class="col-sm-3 control-label text-right">Do Objects</label>

                            <div id="" class="col-sm-6">
                                <select name="" class="form-control" id="do_object" required>
                                    <option value="">Select</option>
                                    <?php foreach ($do_objects as $row): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['sl'] ?>)<?php echo "  ".$row['target_name']." (".$row['date'].")" ?></option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                        </div>
                        <br/>

                        <div id="stock_wrap">

                        </div>
                    </div>
                </div>


            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {

        $(document).on('change', '#do_object', function () {
            var do_object_id = $(this).val();
            var  do_event_id=<?php echo $do_event_id;?>;
            var  ds_tbl_id=<?php echo $ds_tbl_id;?>;
            var obj = $(this);

            if(do_object_id){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/MakeScheduledDeliveries/ajaxMakeScheduledDeliv")?>',
                    data: {
                        do_object_id: do_object_id,do_event_id:do_event_id,ds_tbl_id:ds_tbl_id
                    },
                    //  dataType: 'json',

                    success: function (data, status) {
                        $('#stock_wrap').html(data);
                    }
                });
            }else {
                $('#stock_wrap').html('');
            }


        });



    })
</script>