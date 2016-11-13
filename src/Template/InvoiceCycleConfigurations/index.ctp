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
        <li><?= $this->Html->link(__('Invoice Cycle Configurations'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Invoice Cycle Configuration List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Invoice Cycle Configuration'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Invoice Approved At') ?></th>
                            <th><?= __('Approving User Group') ?></th>
                            <th><?= __('Allow Delivery Before Approval') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoiceCycleConfigurations as $key => $invoiceCycleConfiguration) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= Cake\Core\Configure::read('invoice_approved_at')[$invoiceCycleConfiguration->invoice_approved_at] ?></td>
                                <td><?= $userGroups[$invoiceCycleConfiguration->approving_user_group] ?></td>
                                <td><?= $invoiceCycleConfiguration->allow_delivery_before_approval==1?'Yes':'No' ?></td>
                                <td class="actions">
                                    <?php
//                                    echo $this->Html->link(__('View'), ['action' => 'view', $invoiceCycleConfiguration->id], ['class' => 'btn btn-sm btn-info']);
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $invoiceCycleConfiguration->id], ['class' => 'btn btn-sm btn-warning']);
                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $invoiceCycleConfiguration->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $invoiceCycleConfiguration->id)]);
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
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
    </div>
</div>

