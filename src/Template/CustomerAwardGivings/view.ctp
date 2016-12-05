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
            <?= $this->Html->link(__('Customer Award Givings'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Customer Award Giving') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Customer Award Giving Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                                                                                                        <tr>
                                    <th><?= __('Customer Award') ?></th>
                                    <td><?= $customerAwardGiving->has('customer_award') ? $this->Html->link($customerAwardGiving->customer_award->id, ['controller' => 'CustomerAwards', 'action' => 'view', $customerAwardGiving->customer_award->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Customer') ?></th>
                                    <td><?= $customerAwardGiving->has('customer') ? $this->Html->link($customerAwardGiving->customer->name, ['controller' => 'Customers', 'action' => 'view', $customerAwardGiving->customer->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Award') ?></th>
                                    <td><?= $customerAwardGiving->has('award') ? $this->Html->link($customerAwardGiving->award->name, ['controller' => 'Awards', 'action' => 'view', $customerAwardGiving->award->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Parent Global Id') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->parent_global_id) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Award Account Code') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->award_account_code) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Amount') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->amount) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Giving Mode') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->giving_mode) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Award Giving Date') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->award_giving_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($customerAwardGiving->updated_date) ?></td>
                                </tr>
                                                                                                                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

