<?php
$status = \Cake\Core\Configure::read('status_options');
$yes_no = \Cake\Core\Configure::read('yes_no');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Credit Note Events'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Credit Note Event List') ?>
                </div>
                <div class="tools">
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Credit Note') ?></th>
                            <th><?= __('Recipient') ?></th>
                            <th><?= __('Sender') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($creditNoteEvents as $key => $creditNoteEvent) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $creditNoteEvent->has('credit_note') ?
                                        $this->Html->link($creditNoteEvent->credit_note
                                            ->id, ['controller' => 'CreditNotes',
                                            'action' => 'view', $creditNoteEvent->credit_note
                                                ->id]) : '' ?></td>
                                <td><?= $creditNoteEvent->has('recipient') ?
                                        $this->Html->link($creditNoteEvent->recipient
                                            ->full_name_en, ['controller' => 'Users',
                                            'action' => 'view', $creditNoteEvent->recipient
                                                ->id]) : '' ?></td>

                                <td><?= $creditNoteEvent->has('sender') ?
                                        $this->Html->link($creditNoteEvent->sender
                                            ->full_name_en, ['controller' => 'Users',
                                            'action' => 'view', $creditNoteEvent->sender
                                                ->id]) : '' ?></td>
                                <td><?php if ($creditNoteEvent->is_action_taken == 0) { ?>
                                        <label class="label label-danger"> Pending</label>
                                    <?php } else{?>
                                        <label class="label label-success"> Yes</label>
                                    <?php } ?>
                                </td>
                                <td class="actions">
                                    <?php
                                  //  echo $this->Html->link(__('View'), ['action' => 'view', $creditNoteEvent->id], ['class' => 'btn btn-sm btn-info']);

                                    echo $this->Html->link(__('Action'), ['action' => 'edit', $creditNoteEvent->id], ['class' => 'btn btn-sm btn-warning']);

                                   // echo //$this->Form->postLink(__('Delete'), ['action' => 'delete', $creditNoteEvent->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $creditNoteEvent->id)]);

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

