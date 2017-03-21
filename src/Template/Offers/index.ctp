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
        <li><?= $this->Html->link(__('Offers'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Offer List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Offer'), ['action' => 'add'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Program Name')?></th>
                            <th><?= __('Start Date')?></th>
                            <th><?= __('End Date')?></th>
                            <th><?= __('Status')?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($offers as $key => $offer) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $offer->program_name?></td>
                                <td><?= date('d-m-Y', $offer->program_period_start)?></td>
                                <td><?= date('d-m-Y', $offer->program_period_end)?></td>
                                <td><?= ($offer->status==1)?'Active':'In-active' ?></td>
                                <td class="actions">
                                    <?php
                                    echo $this->Html->link(__('Edit'), ['action' => 'edit', $offer->id], ['class' => 'btn btn-sm btn-warning']);
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

