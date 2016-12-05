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
            <?= $this->Html->link(__('Customer Awards'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Customer Award') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Customer Award Details') ?>
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
                                    <td><?= $customerAward->has('customer') ? $this->Html->link($customerAward->customer->name, ['controller' => 'Customers', 'action' => 'view', $customerAward->customer->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Award') ?></th>
                                    <td><?= $customerAward->has('award') ? $this->Html->link($customerAward->award->name, ['controller' => 'Awards', 'action' => 'view', $customerAward->award->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Customer Offer') ?></th>
                                    <td><?= $customerAward->has('customer_offer') ? $this->Html->link($customerAward->customer_offer->id, ['controller' => 'CustomerOffers', 'action' => 'view', $customerAward->customer_offer->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Parent Global Id') ?></th>
                                    <td><?= $this->Number->format($customerAward->parent_global_id) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Award Account Code') ?></th>
                                    <td><?= $this->Number->format($customerAward->award_account_code) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Amount') ?></th>
                                    <td><?= $this->Number->format($customerAward->amount) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Offer Period Start') ?></th>
                                    <td><?= $this->Number->format($customerAward->offer_period_start) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Offer Period End') ?></th>
                                    <td><?= $this->Number->format($customerAward->offer_period_end) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Action Status') ?></th>
                                    <td><?= $this->Number->format($customerAward->action_status) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Action Taken At') ?></th>
                                    <td><?= $this->Number->format($customerAward->action_taken_at) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($customerAward->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($customerAward->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($customerAward->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($customerAward->updated_date) ?></td>
                                </tr>
                                                                                                                    </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

