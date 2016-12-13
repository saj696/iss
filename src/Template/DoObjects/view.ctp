<?php
$status = \Cake\Core\Configure::read('status_options');
use Cake\Routing\Router;

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
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">

                        <tr>
                            <th><?= __('Serial No') ?></th>
                            <td><?= $this->Number->format($doObject->serial_no) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Date') ?></th>
                            <td><?= $this->Number->format($doObject->date) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Object Type') ?></th>
                            <td><?= $this->Number->format($doObject->object_type) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Target Type') ?></th>
                            <td><?= $this->Number->format($doObject->target_type) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Target Id') ?></th>
                            <td><?= $this->Number->format($doObject->target_id) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Action Status') ?></th>
                            <td><?= $this->Number->format($doObject->action_status) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $this->Number->format($doObject->created_by) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Created Date') ?></th>
                            <td><?= $this->Number->format($doObject->created_date) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Updated By') ?></th>
                            <td><?= $this->Number->format($doObject->updated_by) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Updated Date') ?></th>
                            <td><?= $this->Number->format($doObject->updated_date) ?></td>
                        </tr>
                    </table>
                </div>

                <form class="form-horizontal"  method="post" action="<?php echo Router::url('/',true); ?>DoObjects/view/<?=$id?>" enctype="multipart/form-data">
                    <div class="col-md-6 col-md-offset-3 recipient_row">
                        <?php echo $this->Form->input('recipient_id', ['options' => $recipients, 'class' => 'recipient form-control', 'empty' => __('Select')]);?>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-6 col-sm-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

