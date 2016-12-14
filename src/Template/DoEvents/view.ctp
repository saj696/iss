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
        <li><?= __('View Do Event') ?></li>
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
            <div class="portlet-body">
                <div class="table-scrollable">
                    <form class="form-horizontal"  method="post" action="<?php echo Router::url('/',true); ?>DoObjects/view" enctype="multipart/form-data">
                        <table class="table table-bordered">
                            <tr>
                                <td>SL:</td>
                                <td>Item Name</td>
                                <td>Unit</td>
                                <td>Quantity</td>
                                <td>Approve Quantity</td>

                            </tr>
                            <?php foreach($do_items as $key=>$row):?>

                                <tr>
                                    <td><?= $key+1?></td>
                                    <td><input  class="form-control" name="" type="text" readonly value="<?php echo SystemHelper::getItemAlias($row['item']['id'],$warehouse_id);?>"></td>
                                    <td><input  class="form-control" name="" type="text" readonly value="<?php foreach($row['item']['item_units'] as $r){if($r['id']==$row['unit_id']){echo $r['unit_display_name'];}}?>"></td>
                                    <td><input  class="form-control" name="" type="text" readonly value="<?=$row['quantity']?>"></td>
                                    <td><input  class="form-control" name="" type="text"></td>
                                </tr>
                            <?php endforeach;?>
                        </table>
                        <button class="btn blue pull-right" style="margin:20px" type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

