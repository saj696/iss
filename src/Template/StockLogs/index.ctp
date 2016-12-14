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
        <li><?= $this->Html->link(__('Stock Logs'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Stock Logs') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Reduce Stock'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Warehouse') ?></th>
                            <!--                            <th>--><? //= __('Stock') ?><!--</th>-->
                            <th><?= __('Type') ?></th>
                            <th><?= __('Quantity') ?></th>
                            <th><?= __('Date') ?></th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($stockLogs as $key => $stocklog) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?=
                                    $stocklog->has('warehouse') ? $this->Html->link(
                                        $stocklog->warehouse->name, ['controller' => 'Warehouses', 'action' => 'view', $stocklog->warehouse->id])
                                        : '' ?></td>
                                <?php
                                //$stocklog->has('stock') ? $this->Html->link(
                                // $stocklog->stock->id, ['controller' => 'Stocks', 'action' => 'view', $stocklog->stock->id])
                                //        : ''
                                ?>

                                <td><?= Cake\Core\Configure::read('stock_log_types')[$stocklog->type] ?></td>
                                <td><?= $this->Number->format($stocklog->quantity) ?></td>
                                <td><?= date('d-M-Y', $stocklog->created_date) ?></td>
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

