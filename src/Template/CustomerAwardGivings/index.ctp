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
        <li><?= $this->Html->link(__('Customer Award Givings'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Customer Award Giving List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Customer Award Giving'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('customer_award_id') ?></th>
                                                                                                                                                <th><?= __('customer_id') ?></th>
                                                                                                                                                <th><?= __('parent_global_id') ?></th>
                                                                                                                                                <th><?= __('award_account_code') ?></th>
                                                                                                                                                <th><?= __('award_id') ?></th>
                                                                                                                                                <th><?= __('amount') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customerAwardGivings as $key => $customerAwardGiving) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                                <td><?= $customerAwardGiving->has('customer_award') ?
                                                    $this->Html->link($customerAwardGiving->customer_award
                                                    ->id, ['controller' => 'CustomerAwards',
                                                    'action' => 'view', $customerAwardGiving->customer_award
                                                    ->id]) : '' ?></td>
                                                                                                        <td><?= $customerAwardGiving->has('customer') ?
                                                    $this->Html->link($customerAwardGiving->customer
                                                    ->name, ['controller' => 'Customers',
                                                    'action' => 'view', $customerAwardGiving->customer
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($customerAwardGiving->parent_global_id) ?></td>
                                                                                            <td><?= $this->Number->format($customerAwardGiving->award_account_code) ?></td>
                                                                                                <td><?= $customerAwardGiving->has('award') ?
                                                    $this->Html->link($customerAwardGiving->award
                                                    ->name, ['controller' => 'Awards',
                                                    'action' => 'view', $customerAwardGiving->award
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($customerAwardGiving->amount) ?></td>
                                                                                <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $customerAwardGiving->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $customerAwardGiving->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $customerAwardGiving->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $customerAwardGiving->id)]);
                                            
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

