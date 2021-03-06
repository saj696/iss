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
        <li><?= $this->Html->link(__('Credit Sales Policy'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Policy List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Policy'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Start Date') ?></th>
                            <th><?= __('End Date') ?></th>
                            <th><?= __('Policy Detail') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($creditSalesPolicies as $key => $creditSalesPolicy) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= date('d-m-Y', $creditSalesPolicy->policy_start_date) ?></td>
                                <td><?= date('d-m-Y', $creditSalesPolicy->policy_expected_end_date) ?></td>
                                <td><?php echo '<pre>'; print_r(json_decode($creditSalesPolicy->policy_detail, true)); echo '</pre>';?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

