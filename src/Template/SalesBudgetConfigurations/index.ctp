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
        <li><?= $this->Html->link(__('Sales Budget Configurations'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Sales Budget Configuration List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Sales Budget Configuration'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Level') ?></th>
                            <th><?= __('Sales Measure') ?></th>
                            <th><?= __('Product Scope') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($salesBudgetConfigurations as $key => $salesBudgetConfiguration) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $levelArr[$salesBudgetConfiguration->level_no] ?></td>
                                <td><?= $salesBudgetConfiguration->sales_measure==1?'Quantity':'Value' ?></td>
                                <td>
                                    <?php
                                    if($salesBudgetConfiguration->product_scope==1){
                                        echo 'Specific';
                                    }elseif($salesBudgetConfiguration->product_scope==2){
                                        echo 'All';
                                    }elseif($salesBudgetConfiguration->product_scope==3){
                                        echo 'Specific Unit';
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('View'), ['action' => 'view', $salesBudgetConfiguration->id], ['class' => 'btn btn-sm btn-info']);
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $salesBudgetConfiguration->id], ['class' => 'btn btn-sm btn-warning']);
                                    echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $salesBudgetConfiguration->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $salesBudgetConfiguration->id)]);
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

