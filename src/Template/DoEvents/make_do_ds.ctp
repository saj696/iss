<?php
$status = \Cake\Core\Configure::read('status_options');
use Cake\Routing\Router;
use App\View\Helper\SystemHelper;

//echo "<pre>";print_r($do_items);die();
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Do Events'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Make Do-Ds') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Do Event Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body wrap">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group input select">
                            <label for="" class="col-sm-3 control-label text-right">Warehouse</label>

                            <div id="" class="col-sm-6">
                                <select name="" class="form-control" id="warehouse" required>
                                    <option value="">Select</option>
                                    <?php foreach ($warehouses as $row): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
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


        $(document).on('change', '#warehouse', function () {
            var warehouse_id = $(this).val();
          //  var sender_warehouse_id = $('#sender_warehouse_id').val();
//            var item_ids = $('input[name="item_id[]"]').map(function () {
//                return $(this).val();
//            }).get();
//            var item_unit_ids = $('input[name="item_unit_id[]"]').map(function () {
//                return $(this).val();
//            }).get();
            var obj = $(this);
            var events = <?php echo json_encode($data); ?>;

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/DoEvents/ajaxMakeDoDs")?>',
                data: {
                    warehouse_id: warehouse_id,
                    events:events
                },
                //  dataType: 'json',

                success: function (data, status) {
                    $('#stock_wrap').html(data);
                }
            });

        });
    })
</script>