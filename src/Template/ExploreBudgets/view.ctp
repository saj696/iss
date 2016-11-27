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
        <li>
            <?= $this->Html->link(__('Sales Budgets'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('View Sales Budget') ?></li>
    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-picture-o fa-lg"></i><?= __('Sales Budget Details') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'],['class'=>'btn btn-sm btn-success']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                                                                                                        <tr>
                                    <th><?= __('Sales Budget Configuration') ?></th>
                                    <td><?= $salesBudget->has('sales_budget_configuration') ? $this->Html->link($salesBudget->sales_budget_configuration->id, ['controller' => 'SalesBudgetConfigurations', 'action' => 'view', $salesBudget->sales_budget_configuration->id]) : '' ?></td>
                                </tr>
                                                                                                        <tr>
                                    <th><?= __('Administrative Unit') ?></th>
                                    <td><?= $salesBudget->has('administrative_unit') ? $this->Html->link($salesBudget->administrative_unit->unit_name, ['controller' => 'AdministrativeUnits', 'action' => 'view', $salesBudget->administrative_unit->id]) : '' ?></td>
                                </tr>
                                                                                                                                                                                                                
                                                            <tr>
                                    <th><?= __('Level No') ?></th>
                                    <td><?= $this->Number->format($salesBudget->level_no) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Product Scope') ?></th>
                                    <td><?= $this->Number->format($salesBudget->product_scope) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Item Id') ?></th>
                                    <td><?= $this->Number->format($salesBudget->item_id) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Sales Measure Unit') ?></th>
                                    <td><?= $this->Number->format($salesBudget->sales_measure_unit) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Sales Amount') ?></th>
                                    <td><?= $this->Number->format($salesBudget->sales_amount) ?></td>
                                </tr>
                                                    
                            
                                <tr>
                                    <th><?= __('Status') ?></th>
                                    <td><?= __($status[$salesBudget->status]) ?></td>
                                </tr>
                                                            
                                                            <tr>
                                    <th><?= __('Created By') ?></th>
                                    <td><?= $this->Number->format($salesBudget->created_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Created Date') ?></th>
                                    <td><?= $this->Number->format($salesBudget->created_date) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated By') ?></th>
                                    <td><?= $this->Number->format($salesBudget->updated_by) ?></td>
                                </tr>
                                                    
                                                            <tr>
                                    <th><?= __('Updated Date') ?></th>
                                    <td><?= $this->Number->format($salesBudget->updated_date) ?></td>
                                </tr>
                                                                                                                                <tr>
                                    <th><?= __('Budget Period Start') ?></th>
                                    <td><?= h($salesBudget->budget_period_start) ?></tr>
                                </tr>
                                                        <tr>
                                    <th><?= __('Budget Period End') ?></th>
                                    <td><?= h($salesBudget->budget_period_end) ?></tr>
                                </tr>
                                                                                            </table>
                </div>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

