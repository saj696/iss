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
            <?= $this->Html->link(__('Sales Return Items'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Sales Return Item') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Sales Return Item Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                                                                                                        <tr>
                                    <th><?= __('Item') ?></th>
                                    <td><?= $salesReturnItem->has('item') ? $this->Html->link($salesReturnItem->item->name, ['controller' => 'Items', 'action' => 'view', $salesReturnItem->item->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Sales Return') ?></th>
                                    <td><?= $salesReturnItem->has('sales_return') ? $this->Html->link($salesReturnItem->sales_return->id, ['controller' => 'SalesReturns', 'action' => 'view', $salesReturnItem->sales_return->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Manufacture Unit Id') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->manufacture_unit_id) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Expire Date') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->expire_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Unit Price') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->unit_price) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Quantity') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->quantity) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Net Total') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->net_total) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($salesReturnItem->updated_date) ?></td>
                                </tr>
                                                    
                            
                                <tr>
                                    <th><?= __('Status') ?></th>
                                    <td><?= __($status[$salesReturnItem->status]) ?></td>
                                </tr>
                                                                                                                            </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

