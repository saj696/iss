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
        <li><?= $this->Html->link(__('Do Events'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Do Event List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Do Event'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('sender_id') ?></th>
                                                                                                                                                <th><?= __('recipient_id') ?></th>
                                                                                                                                                <th><?= __('do_object_id') ?></th>
                                                                                                                                                <th><?= __('events_tepe') ?></th>
                                                                                                                                                <th><?= __('action_status') ?></th>
                                                                                                                                                <th><?= __('created_by') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doEvents as $key => $doEvent) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                            <td><?= $this->Number->format($doEvent->sender_id) ?></td>
                                                                                            <td><?= $this->Number->format($doEvent->recipient_id) ?></td>
                                                                                                <td><?= $doEvent->has('do_object') ?
                                                    $this->Html->link($doEvent->do_object
                                                    ->id, ['controller' => 'DoObjects',
                                                    'action' => 'view', $doEvent->do_object
                                                    ->id]) : '' ?></td>
                                                                                                    <td><?= $this->Number->format($doEvent->events_tepe) ?></td>
                                                                                            <td><?= $this->Number->format($doEvent->action_status) ?></td>
                                                                                            <td><?= $this->Number->format($doEvent->created_by) ?></td>
                                                                                <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $doEvent->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $doEvent->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $doEvent->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $doEvent->id)]);
                                            
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

