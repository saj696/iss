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
        <li><?= $this->Html->link(__('Sales Budgets'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Sales Budget List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Sales Budget'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Period Start') ?></th>
                            <th><?= __('Period End') ?></th>
                            <th><?= __('Location') ?></th>
                            <th><?= __('Product Scope') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($salesBudgets as $key => $salesBudget) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= h($salesBudget->budget_period_start) ?></td>
                                <td><?= h($salesBudget->budget_period_end) ?></td>
                                <td><?= $salesBudget->administrative_unit->unit_name ?></td>
                                <td><?= $salesBudget->product_scope ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $salesBudget->id], ['class' => 'btn btn-sm btn-info']);
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $salesBudget->id], ['class' => 'btn btn-sm btn-warning']);
                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $salesBudget->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $salesBudget->id)]);
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

