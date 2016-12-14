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
        <li><?= $this->Html->link(__('Sales Returned'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Sales Returned') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Add Sales Return Items'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer') ?></th>
                            <th><?= __('Total') ?></th>
                            <th><?= __('Date') ?></th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($salesReturns as $key => $sales_returns) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $sales_returns->has('customer') ?
                                        $this->Html->link($sales_returns->customer
                                            ->name, ['controller' => 'Customers',
                                            'action' => 'view', $sales_returns->customer
                                                ->id]) : '' ?></td>


                                <td><?= $this->Number->format($sales_returns->grand_total) ?></td>

                                <td><?= $this->System->display_date($sales_returns->created_date) ?></td>

                                    <?php
                                   // echo $this->Html->link(__('View'), ['action' => 'view', $sales_returns->id], ['class' => 'btn btn-sm btn-info']);

                                    // echo $this->Html->link(__('Edit'), ['action' => 'edit', $salesReturnItem->id], ['class' => 'btn btn-sm btn-warning']);

                                  //  echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $sales_returns->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete # {0}?', $sales_returns->id)]);

                                    ?>

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


