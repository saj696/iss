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
                <div class=''>
<!--                    <form class="form-horizontal" method="post"-->
<!--                          action="--><?php //echo Router::url('/', true); ?><!--DoEvents/view/" enctype="multipart/form-data">-->
<!---->
<!--                        <table class="table table-bordered">-->
<!--                            <tr>-->
<!--                                <td>SL:</td>-->
<!--                                <td>Item Name</td>-->
<!--                                <td>Unit</td>-->
<!--                                <td>Quantity</td>-->
<!---->
<!---->
<!--                            </tr>-->
<!---->
<!--                            --><?php //$old = 0;
//                            $new = 0;
//                            $sum = 0;
//                            $test_items = [];
//                            ?>
<!--                            --><?php //foreach ($do_items as $key => $row):
////                                array_push($test_items,$row['item_id']);
////                                pr(array_count_values($test_items));
////                                pr($test_items);
////                                if(array_count_values(($test_items))[$row['id']])
////                                {
////                                    echo "heymama";
////                                }
//                                ?>
<!--                                <tr>-->
<!--                                    <td>--><?//= $key + 1 ?><!----><?php //$new = $row['item_id'] ?><!--</td>-->
<!--                                    <td>--><?//= $row['item_name'] ?><!--</td>-->
<!--                                    <td>--><?//= $row['unit_name'] ?><!--</td>-->
<!--                                    <td>--><?//= $row['total_amount'];
//                                        $sum += $row['total_amount'] ?><!--</td>-->
<!--                                </tr>-->
<!--                        --><?php //if($new != $old)?>
<!---->
<!--                        --><?php //endforeach;
//
//
//                        ?>
<!--                        </table>-->
<!--                        <button class="btn blue pull-right" style="margin:20px" type="submit">Submit</button>-->
<!--                    </form>-->

                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group input select">
                            <label for="" class="col-sm-3 control-label text-right">Warehouse</label>

                            <div id="" class="col-sm-6">
                                <select name="" class="form-control " id="warehouse">
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