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
        <li><?= $this->Html->link(__('Approve POs'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('PO List') ?>
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
                        <?php foreach ($events as $key => $event) {
                            ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $event->po->customer->name ?></td>
                                <td><?= $this->System->display_date($event->po->po_date) ?></td>
                                <td><?= $this->System->display_date($event->po->delivery_date) ?></td>
                                <td><?= $event->po->net_total ?></td>
                                <td class="actions">
                                    <?php
                                    if($event->is_action_taken==0):
                                        echo $this->Html->link(__('Edit & Approve'), ['action' => 'edit', $event->id], ['class' => 'btn default red-stripe']);
                                    else:
                                        echo $this->Html->link(__('Edit & Approve'), ['action' => 'edit', $event->id], ['disabled', 'class' => 'btn default red-stripe']);
                                    endif;
//                                    echo $this->Form->postLink(__('Approve'), ['action' => 'delete', $event->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to Approve # {0}?', $event->id)]);
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

