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
        </li>

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

            </div>


            <div class="portlet-body">
                <form class="form-horizontal" method="post"  action="<?php echo Router::url('/', true); ?>MakeDto/index" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">

                            <div class="col-md-12" id="file_wrapper" data-index_no="0">
                                <table class="table table-bordered ">
                                    <thead>
                                    <tr>
                                        <td>Itme</td>
                                        <td>Unit</td>
                                        <td>Stock Quantity</td>
                                        <td>Quantity</td>
                                        <td>Action</td>
                                    </tr>
                                    </thead>

                                    <tbody class="file_container">
                                    <tr class="single_row">
                                        <td><?php echo $this->Form->input('item.0.item_id', ['options' => $items, 'required' => 'required', 'class' => 'item form-control', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('item.0.unit_id', ['options' => '', 'class' => 'unit form-control','required' => 'required', 'templates' => ['label' => '']]); ?></td>
                                        <td><?php echo $this->Form->input('item.0.stock_quantity', ['required' => 'required', 'class' => 'stock_quantity form-control', 'templates' => ['label' => ''], 'readonly']);  ?></td>
                                        <td><?php echo $this->Form->input('item.0.quantity', ['type'=>'number','required' => 'required', 'class' => 'quantity form-control', 'templates' => ['label' => '']]); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm add_file"><i class="fa fa-plus" aria-hidden="true"></i> </button>
                                            <button type="button" class="btn btn-danger btn-sm remove_file"><i class="fa fa-times"  aria-hidden="true"></i></button>
                                        </td>
                                    </tr>
                                    </tbody>

                                </table>

                            </div>

                            <div class="col-md-6 col-md-offset-3">
                                <div class="form-group input select required">
                                    <label for="" class="col-sm-3  control-label">Warehouse</label>
                                    <div id="" class="col-sm-6">
                                        <select  name="warehouse" required="required" class="form-control" id="">
                                            <option value="">Select</option>
                                            <?php foreach($warehouses as $row):?>
                                                <option value="<?= $row['id']?>"><?= $row['warehouse_name']?></option>
                                            <?php endforeach;?>
                                        </select></div>
                                </div>

                            </div>

                        </div>

                        <button class="btn blue pull-right save" style="margin:20px" type="submit">Save</button>

                    </div>


                </div>
                </form>

            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {

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


        $(document).on('change', '.unit', function () {
            var unit_id = $(this).val();
            var item_id = $(this).closest('.single_row').find('.item').val();



            var obj = $(this);
            if (item_id) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/MakeDto/getItemUnitStockAmount")?>',
                    data: {item_id: item_id,unit_id:unit_id},
                    dataType: 'json',

                    success: function (data, status) {
                        console.log(data);
                        obj.closest('.single_row').find('.stock_quantity').html('');
                        obj.closest('.single_row').find('.stock_quantity').attr("value", data);
                        obj.closest('.single_row').find('.quantity').attr("max", data);

                    }
                });
            }
        });

        $(document).on('change', '.item', function () {
            var item_id = $(this).val();
            var obj = $(this);
            if (item_id) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/MakeDto/getItemUnits")?>',
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
            }
        });

    })
</script>