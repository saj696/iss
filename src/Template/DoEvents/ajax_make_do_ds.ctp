<?php use Cake\Routing\Router; ?>
<?php if ($do_items) {


    $do_items[count($do_items)] = array('item_id' => 'last');
    //  echo "<pre>";print_r($do_items);?>

    <br/>
    <table class="table table-striped table-bordered table-hover" id="info_tale">
        <tr>
            <td>SL:</td>
            <td>Item Name</td>
            <td>Unit</td>
            <td>Quantity</td>
            <td>Stock</td>
            <td>Further Needed</td>
            <td>KG/L</td>
        </tr>

        <?php
        $test_items = [];
        $my_item_sum = 0;
        $my_id = 0;
        $item_id = 0;
        ?>





        <?php foreach ($do_items as $key => $row) { ?>


            <?php
            if ($my_id == 0 && $row['item_id'] != 'last') {
                $my_id = $row['item_id'];
                $my_item_sum += isset($row['require']) ? $row['require'] : 0;
                $item_id = $row['item_id'];
            } elseif ($row['item_id'] == 'last') {
                ?>
                <tr>
                    <td colspan="6"><input type="hidden" id="<?= $item_id ?>" value="<?= $my_item_sum; ?>"></td>
                    <td><?= $my_item_sum; ?></td>
                </tr>
            <?php } elseif ($my_id == $row['item_id']) {
                $my_item_sum += $row['require'];
            } else {
                ?>
                <tr>
                    <td colspan="6"><input type="hidden" id="<?= $item_id ?>" value="<?= $my_item_sum; ?>"></td>
                    <td><?= $my_item_sum; ?></td>
                </tr>

                <?php
                $my_id = 0;
                $my_item_sum = 0;
                $my_item_sum = $row['require'];
            } ?>

            <?php if ($row['item_id'] != 'last') { ?>
                <tr>
                    <td><?= $key + 1 ?><?php $new = $row['item_id'] ?></td>
                    <td><?= $row['item_name'] ?></td>
                    <td><?= $row['unit_name'] ?></td>
                    <td><?= $row['asked_quantity']; ?></td>
                    <td><?= $row['stock_amount'] ?></td>
                    <td><?= $row['further_needed'] ?></td>
                    <td><?= $row['require'] ?></td>
                </tr>
            <?php } ?>

        <?php } ?>
    </table>


    <div class="panel panel-success">
        <!-- Default panel contents -->
        <div class="panel-heading"><strong>Fix DO Quantities</strong></div>
        <div class="panel-body">
            <div class="table-scrollable">
                <form class="form-horizontal" method="post"
                      action="<?php echo Router::url('/', true); ?>DoEvents/fixDoQuantities"
                      enctype="multipart/form-data" id="fix_from">
                    <?php foreach ($do_object_ids as $key => $val): ?>
                        <input type="hidden" name="pi_ids[]" value="<?= $val ?>">
                    <?php endforeach ?>
                    <input type="hidden" name="parent_warehouse_id" value="<?= $parent_warehouse_id ?>">

                    <table class="table table-striped table-bordered table-hover" id="file_wrapper" data-index_no="0">
                        <thead>
                        <tr>
                            <td>Warehouse</td>
                            <td>Items</td>
                            <td>Further Needed</td>
                            <td>Warehouse Bulk Stock</td>
                            <td>DO Unit</td>
                            <td>DO Quantity</td>
                            <td>Destination</td>
                            <td>Action</td>
                        </tr>
                        </thead>
                        <tbody class="file_container">
                        <tr class="single_row">
                            <td><select name="do_object_items[0][warehouse_id]" class="form-control warehouse_id" id=""
                                        required>
                                    <option value="">Select</option>
                                    <?php foreach ($warehouses as $row): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><select name="do_object_items[0][item_id]" class="form-control item_id" id="" required>
                                    <option value="">Select</option>
                                </select>
                            </td>
                            <td><input type="text" name="do_object_items[0][further_needed]"
                                       class="form-control further_needed" readonly></td>
                            <td><input type="text" name="do_object_items[0][warehouse_bulk_stock]"
                                       class="form-control warehouse_bulk_stock" readonly value="100"></td>
                            <td><input type="text" name="do_object_items[0][do_unit]" class="form-control do_unit"
                                       required></td>
                            <td><input type="text" name="do_object_items[0][do_quantity]"
                                       class="form-control do_quantity" required></td>
                            <td><select name="do_object_items[0][destination_id]" class="form-control destination_id"
                                        required>
                                    <option value="">Select</option>
                                    <?php foreach ($warehouses as $row): ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm add_file"><i class="fa fa-plus"
                                                                                                 aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm remove_file"><i class="fa fa-times"
                                                                                                   aria-hidden="true"></i>
                                </button>

                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <button class="btn blue pull-right" style="margin:20px" type="submit">Submit</button>
                </form>

            </div>

        </div>


    </div>

<?php } else {
    echo "No Result Found";
} ?>


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


        $(document).on('change', '.warehouse_id', function () {

            var warehouse_id = $(this).val();

            var obj = $(this);
            if (warehouse_id) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/DoEvents/getItemName")?>',
                    data: {warehouse_id: warehouse_id},
                    dataType: 'json',

                    success: function (data, status) {
                        obj.closest('.single_row').find('.item_id').html('');
                        obj.closest('.single_row').find('.item_id').append("<option value=''><?= __('Select') ?></option>");
                        $.each(data, function (key, value) {
                            obj.closest('.single_row').find('.item_id').append($("<option></option>").attr("value", key).text(value));
                        });
                    }
                });
            }
        });

        $(document).on('change', '.item_id', function () {
            var item_id = $(this).val();
            var obj = $(this);
            var id = '#'.concat(item_id);

            var data = $(id).val();

            if (data) {
                obj.closest('.single_row').find('.further_needed').attr("value", data);
            } else {
                obj.closest('.single_row').find('.further_needed').attr("value", 0);
            }

        });

    })
</script>

