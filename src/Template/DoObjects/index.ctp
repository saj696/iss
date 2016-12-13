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
        <li><?= $this->Html->link(__('Do Objects'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Do Object List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Do Object'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('serial_no') ?></th>
                                                                                                                                                <th><?= __('date') ?></th>
                                                                                                                                                <th><?= __('object_type') ?></th>
                                                                                                                                                <th><?= __('target_type') ?></th>
                                                                                                                                                <th><?= __('target_id') ?></th>
                                                                                                                                                <th><?= __('action_status') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doObjects as $key => $doObject) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->serial_no) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->date) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->object_type) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->target_type) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->target_id) ?></td>
                                                                                            <td><?= $this->Number->format($doObject->action_status) ?></td>
                                                                                <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $doObject->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $doObject->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $doObject->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $doObject->id)]);
                                            
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

