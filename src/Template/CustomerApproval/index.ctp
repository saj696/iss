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
        <li><?= $this->Html->link(__('Customers'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Approved Customers') ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Proprietor') ?></th>
                            <th><?= __('Contact Person') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($customers as $key => $customer) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= h($customer->name) ?></td>
                                <td><?= h($customer->proprietor) ?></td>
                                <td><?= h($customer->contact_person) ?></td>
                                <td><?= ($customer->status)==0?"Not Approved":"Approved" ?></td>
                                <td class="actions">
                                    <?php
                                    if($customer->status==0):
                                        echo $this->Html->link(__('Approve'), ['action' => 'approve', $customer->id], ['class' => 'btn btn-sm btn-info']);
                                    else:
                                        echo $this->Html->link(__('Approve'), ['action' => 'approve', $customer->id], ['class' => 'btn btn-sm btn-info', 'disabled']);
                                    endif;
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $customer->id], ['class' => 'btn btn-sm btn-warning']);
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

