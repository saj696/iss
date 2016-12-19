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
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <td>SL:</td>
                            <td>Item Name</td>
                            <td>Unit</td>
                            <td>Quantity</td>
                        </tr>
                        <?php foreach($items as $key=>$row):?>
                            <tr>
                                <td><?=$key+1?></td>
                                <td><?php echo SystemHelper::getItemAlias($row['item']['id'], $warehouse_id); ?></td>
                                <td><?=$row['unit']['unit_display_name']?></td>
                                <td><?=$row['quantity']?></td>
                            </tr>
                        <?php endforeach;?>

                    </table>
                </div>

            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

