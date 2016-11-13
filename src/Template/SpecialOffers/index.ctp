<?php
use Cake\Core\Configure;
$status = Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Special Offers'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Special Offer List') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Special Offer'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Program Name') ?></th>
                            <th><?= __('Period Start') ?></th>
                            <th><?= __('Period End') ?></th>
                            <th><?= __('Invoice Type') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($specialOffers as $key => $specialOffer) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= h($specialOffer->program_name) ?></td>
                                <td><?= date('d-m-Y', $specialOffer->program_period_start) ?></td>
                                <td><?= date('d-m-Y', $specialOffer->program_period_end) ?></td>
                                <td><?= Configure::read('special_offer_invoice_types')[$specialOffer->invoice_type] ?></td>
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

