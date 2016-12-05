<?php
$status = \Cake\Core\Configure::read('status_options');
$approval_status = \Cake\Core\Configure::read('approval_status');
$adjustment_status = 1;
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Credit Notes'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Credit Note List') ?>
                </div>
                <div class="tools">
                    <?php //echo$this->Html->link(__('New Credit Note'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer') ?></th>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Sender') ?></th>
                            <th><?= __('Base Amount') ?></th>
                            <th><?= __('Demurrage') ?></th>
                            <th><?= __('Total Amount') ?></th>
                            <th><?= __('Approval Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // pr($creditNotes);die;
                        foreach ($creditNotes as $key => $creditNote) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $creditNote->has('customer') ?
                                        $this->Html->link($creditNote->customer
                                            ->name, ['controller' => 'Customers',
                                            'action' => 'view', $creditNote->customer
                                                ->id]) : '' ?></td>
                                <td><?= date('d-m-Y', $creditNote->date) ?></td>
                                <td><?php
                                    echo $creditNote['credit_note_event']['sender']['full_name_en'];
                                    ?></td>
                                <td>
                                    <?php
                                    $base_amount = 0;
                                    foreach ($creditNote['credit_note_items'] as $cn):
                                        $base_amount += $cn->net_total;
                                    endforeach;
                                    echo $base_amount;
                                    ?>
                                </td>
                                <td><?= $this->Number->format($creditNote->demurrage_percentage) . '%' ?></td>
                                <td><?= $this->Number->format($creditNote->total_after_demurrage) ?></td>

                                <td><?= __($approval_status[$creditNote->approval_status]) ?></td>
                                <td class="actions">
                                    <?php
                                    //

                                    if ($creditNote->approval_status == 2) {
                                        echo $this->Html->link(__('Action'), ['action' => 'edit', $creditNote->id], ['class' => 'btn btn-sm btn-warning']);
                                    } elseif ($creditNote->approval_status == 3) {
                                        echo $this->Html->link(__('View'), ['action' => 'view', $creditNote->id], ['class' => 'btn btn-sm btn-info']);
                                    } elseif ($creditNote->approval_status == 3) {
                                        echo $this->Html->link(__('View'), ['action' => 'view', $creditNote->id], ['class' => 'btn btn-sm btn-info']);
                                    }

                                    //echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $creditNote->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $creditNote->id)]);

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

