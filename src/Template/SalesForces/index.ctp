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
        <li><?= $this->Html->link(__('Sale Forces'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Sale Force List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Sale Force'), ['action' => 'add'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Applicable Level') ?></th>
                            <th><?= __('Actions')?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($salesForces as $key => $salesForce) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $salesForce->name ?></td>
                                <td><?= $administrativeLevels[$salesForce->applicable_level] ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $salesForce->id], ['class' => 'btn btn-sm btn-info']);
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $salesForce->id], ['class' => 'btn btn-sm btn-warning']);
                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $salesForce->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $salesForce->id)]);
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

