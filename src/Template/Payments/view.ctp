<?php
$status = \Cake\Core\Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Payments'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Payment') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Payment Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th><?= __('Body') ?></th>
                            <td><?= h($payment->body) ?></td>
                        </tr>


                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= __($status[$payment->status]) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Created By') ?></th>
                            <td><?= $this->Number->format($payment->created_by) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Created Date') ?></th>
                            <td><?= $this->Number->format($payment->created_date) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Updated By') ?></th>
                            <td><?= $this->Number->format($payment->updated_by) ?></td>
                        </tr>

                        <tr>
                            <th><?= __('Updated Date') ?></th>
                            <td><?= $this->Number->format($payment->updated_date) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

