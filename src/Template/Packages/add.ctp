<?php
use Cake\Core\Configure;

?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Packages'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Package') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Package') ?>
                </div>
                <div class="tools">
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($package, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <tr>
                                <td>
                                    <?php echo $this->Form->input('warehouse_id', ['options' => $warehouses, 'style' => 'width:50%', 'class' => 'form-control', 'required' => true, 'empty' => __('Select')]); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable">
                                    <tr>
                                        <th><?= __('Item') ?></th>
                                        <th><?= __('Unit') ?></th>
                                        <th><?= __('Quantity') ?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width:25%;">
                                            <?php echo $this->Form->input('Packages.0.item_id', ['options' => $items, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td style="width: 30%;">
                                            <?php echo $this->Form->input('Packages.0.manufacture_unit_id', ['options' => $units, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                                        <td style="width:25%"><?php echo $this->Form->input('Packages.0.quantity', ['type' => 'text', 'style' => 'width: 100%', 'required' => 'required', 'class' => 'form-control quantity numbersOnly', 'templates' => ['label' => '']]); ?></td>
                                        <td width="50px;"><span
                                                class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
                    </div>

                    <div class="row text-center" style="margin-bottom: 20px;">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function (event) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index + 1);
                this.id = this.id.replace(/\d+/, index + 1);
                this.value = '';
            }).end();

            $('.moreTable').append(html);
        });

        $(document).on('click', '.remove', function () {
            var obj = $(this);
            var count = $('.single_list').length;
            if (count > 1) {
                obj.closest('.single_list').remove();
            }
        });
    });
</script>