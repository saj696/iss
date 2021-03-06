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
        <li><?= $this->Html->link(__('POs'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('PO List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New PO'), ['action' => 'add'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer') ?></th>
                            <th><?= __('PO Date') ?></th>
                            <th><?= __('Delivery Date') ?></th>
                            <th><?= __('Total Amount') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pos as $key => $po) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $po->customer->name ?></td>
                                <td><?= $this->System->display_date($po->po_date) ?></td>
                                <td><?= $this->System->display_date($po->delivery_date) ?></td>
                                <td><?= $po->net_total ?></td>
                                <td class="actions">
                                    <?php
                                    if($po->po_status==1):
                                        echo $this->Html->link(__('Forward'), ['action' => 'forward', $po->id], ['class' => 'btn btn-circle default yellow-stripe']);
                                    else:
                                        echo $this->Html->link(__('Forward'), ['action' => 'view', $po->id], ['disabled', 'class' => 'btn btn-circle default yellow-stripe']);
                                    endif;
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

