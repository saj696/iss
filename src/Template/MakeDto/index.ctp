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
                    <table class="table table-bordered table-hover">
                        <tr>
                            <td>SL:</td>
                            <td>Item Name</td>
                            <td>Unit</td>
                            <td>Stock Quantity</td>
                            <td>Transfer Quantity</td>
                        </tr>
                        <?php foreach ($items as $key => $row): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><input type="hidden" name="item[<?=$key?>][item_id]" value="<?=$row['item_id']?>"><?php echo SystemHelper::getItemAlias($row['item_id'],$user_warehouse_id); ?></td>
                                <td><input type="hidden" name="item[<?=$key?>][unit_id]" value="<?=$row['unit_id']?>"><?= $row['unit_name'] ?></td>
                                <td><input type="hidden" name="item[<?=$key?>][stock_id]" value="<?=$row['stock_id']?>"><?= $row['stock_quantity'] ?></td>
                                <td><input class="form-control" name="item[<?=$key?>][quantity]" type="number" value="0" max="<?=$row['stock_quantity']?>"></td>
                            </tr>
                        <?php endforeach; ?>

                    </table>



                    <div class="form-group input select required">
                        <label for="" class="col-sm-1 col-sm-offset-3 control-label">Warehouse</label>
                        <div id="" class="col-sm-3">
                            <select  name="warehouse" required="required" class="item form-control" id="">
                                <option value="">Select</option>
                                <?php foreach($warehouses as $row):?>
                                    <option value="<?= $row['id']?>"><?= $row['warehouse_name']?></option>
                                <?php endforeach;?>
                            </select></div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-5 col-sm-3">
                            <button type="submit" class="btn btn-primary btn-lg">Make Deliver</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

