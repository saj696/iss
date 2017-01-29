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
            <?= $this->Html->link(__('Pos'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Po') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Po Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                                                                                                        <tr>
                                    <th><?= __('Customer') ?></th>
                                    <td><?= $po->has('customer') ? $this->Html->link($po->customer->name, ['controller' => 'Customers', 'action' => 'view', $po->customer->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Customer Level No') ?></th>
                                    <td><?= $this->Number->format($po->customer_level_no) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Customer Unit Global Id') ?></th>
                                    <td><?= $this->Number->format($po->customer_unit_global_id) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Customer Type') ?></th>
                                    <td><?= $this->Number->format($po->customer_type) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Po Date') ?></th>
                                    <td><?= $this->Number->format($po->po_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Delivery Date') ?></th>
                                    <td><?= $this->Number->format($po->delivery_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Invoice Type') ?></th>
                                    <td><?= $this->Number->format($po->invoice_type) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Net Total') ?></th>
                                    <td><?= $this->Number->format($po->net_total) ?></td>
                                </tr>
                                                    
                            
                                <tr>
                                    <th><?= __('Status') ?></th>
                                    <td><?= __($status[$po->status]) ?></td>
                                </tr>
                                                            
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($po->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($po->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($po->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($po->updated_date) ?></td>
                                </tr>
                                                                                                                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

