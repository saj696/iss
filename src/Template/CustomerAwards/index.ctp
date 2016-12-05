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
        <li><?= $this->Html->link(__('Customer Awards'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Customer Award List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Customer Award'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('customer_id') ?></th>
                                                                                                                                                <th><?= __('parent_global_id') ?></th>
                                                                                                                                                <th><?= __('award_account_code') ?></th>
                                                                                                                                                <th><?= __('award_id') ?></th>
                                                                                                                                                <th><?= __('amount') ?></th>
                                                                                                                                                <th><?= __('customer_offer_id') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customerAwards as $key => $customerAward) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                                <td><?= $customerAward->has('customer') ?
                                                    $this->Html->link($customerAward->customer
                                                    ->name, ['controller' => 'Customers',
                                                    'action' => 'view', $customerAward->customer
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($customerAward->parent_global_id) ?></td>
                                                                                            <td><?= $this->Number->format($customerAward->award_account_code) ?></td>
                                                                                                <td><?= $customerAward->has('award') ?
                                                    $this->Html->link($customerAward->award
                                                    ->name, ['controller' => 'Awards',
                                                    'action' => 'view', $customerAward->award
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($customerAward->amount) ?></td>
                                                                                                <td><?= $customerAward->has('customer_offer') ?
                                                    $this->Html->link($customerAward->customer_offer
                                                    ->id, ['controller' => 'CustomerOffers',
                                                    'action' => 'view', $customerAward->customer_offer
                                                    ->id]) : '' ?></td>
                                                                                        <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $customerAward->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $customerAward->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $customerAward->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $customerAward->id)]);
                                            
                                        ?>

                                    </td>
                                </tr>

                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <ul class="pagination">
                       <?php
                       echo $this->Paginator->prev('<<');
                       echo $this->Paginator->numbers();
                       echo $this->Paginator->next('>>');
                       ?>
                   </ul>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

