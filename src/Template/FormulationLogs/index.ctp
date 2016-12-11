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
        <li><?= $this->Html->link(__('Formulation Logs'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Formulation Log List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Formulation Log'), ['action' => 'add'],['class'=>'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                                                                                            <th><?= __('Sl. No.') ?></th>
                                                                                                                    <th><?= __('body') ?></th>
                                                                                                                                                <th><?= __('status') ?></th>
                                                                                                                                                <th><?= __('created_by') ?></th>
                                                                                                                                                <th><?= __('created_date') ?></th>
                                                                                                                                                <th><?= __('updated_by') ?></th>
                                                                                                                                                <th><?= __('updated_date') ?></th>
                                                                                                    <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($formulationLogs as $key => $formulationLog) {  ?>
                                <tr>
                                                                                    <td><?= $this->Number->format($key+1) ?></td>
                                                                                            <td><?= h($formulationLog->body) ?></td>
                                                                                            <td><?= __($status[$formulationLog->status]) ?></td>
                                                                                            <td><?= $this->Number->format($formulationLog->created_by) ?></td>
                                                                                            <td><?= $this->System->display_date($formulationLog->created_date) ?></td>
                                                                                            <td><?= $this->Number->format($formulationLog->updated_by) ?></td>
                                                                                            <td><?= $this->System->display_date($formulationLog->updated_date) ?></td>
                                                                                <td class="actions">
                                        <?php
                                            echo $this->Html->link(__('View'), ['action' => 'view', $formulationLog->id],['class'=>'btn btn-sm btn-info']);

                                            echo $this->Html->link(__('Edit'), ['action' => 'edit', $formulationLog->id],['class'=>'btn btn-sm btn-warning']);

                                            echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $formulationLog->id],['class'=>'btn btn-sm btn-danger','confirm' => __('Are you sure you want to delete # {0}?', $formulationLog->id)]);
                                            
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

