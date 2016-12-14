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
            <?= $this->Html->link(__('Reduced Stocks'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Reduced Stock') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Reduced Stock Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                                                                                                        <tr>
                                    <th><?= __('Store') ?></th>
                                    <td><?= $stockLogs->has('store') ? $this->Html->link($stockLogs->store->name, ['controller' => 'Stores', 'action' => 'view', $stockLogs->store->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Item') ?></th>
                                    <td><?= $stockLogs->has('item') ? $this->Html->link($stockLogs->item->name, ['controller' => 'Items', 'action' => 'view', $stockLogs->item->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Type') ?></th>
                                    <td><?= $this->Number->format($stockLogs->type) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Quantity') ?></th>
                                    <td><?= $this->Number->format($stockLogs->quantity) ?></td>
                                </tr>
                                                    
                            
                                <tr>
                                    <th><?= __('Status') ?></th>
                                    <td><?= __($status[$stockLogs->status]) ?></td>
                                </tr>
                                                            
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($stockLogs->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($stockLogs->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($stockLogs->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($stockLogs->updated_date) ?></td>
                                </tr>
                                                                                                                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

