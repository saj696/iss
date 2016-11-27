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
            <?= $this->Html->link(__('Sales Budgets'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Sales Budget') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Sales Budget') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($salesBudget, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('budget_period_start', ['type'=>'text', 'label'=>'Period Start', 'class'=>'datepicker form-control']);
                        echo $this->Form->input('budget_period_end', ['type'=>'text', 'label'=>'Period End', 'class'=>'datepicker form-control']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="list" data-index_no="0">
                            <div class="itemWrapper">
                                <table class="table table-bordered moreTable">
                                    <tr>
                                        <th><?= __('Parent Unit')?></th>
                                        <th><?= __('Budget Unit')?></th>
                                        <th><?= __('Product Scope')?></th>
                                        <th><?= __('Qty/ Value')?></th>
                                        <th></th>
                                    </tr>
                                    <tr class="item_tr single_list">
                                        <td style="width: 25%"><?php echo $this->Form->input('details.0.parent_id', ['options' => $parents, 'required'=>'required', 'style'=>'max-width: 100%', 'class'=>'parent form-control', 'empty' => __('Select'), 'templates'=>['label' => '']]);?></td>
                                        <td style="width: 25%"><?php echo $this->Form->input('details.0.administrative_unit_id', ['options' => [], 'empty'=>'Select', 'style'=>'width: 100%', 'required'=>'required', 'class'=>'form-control administrative_unit_id', 'templates'=>['label' => '']]);?></td>
                                        <td style="width: 25%"><?php echo $this->Form->input('details.0.item_id', ['options' => $items, 'empty'=>'All', 'style'=>'width: 100%', 'class'=>'form-control', 'templates'=>['label' => '']]);?></td>
                                        <td style="width: 20%"><?php echo $this->Form->input('details.0.sales_amount', ['type' => 'text', 'style'=>'width: 100%', 'class'=>'form-control numbersOnly','required', 'templates'=>['label' => '']]);?></td>
                                        <td width="50px;"><span class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row col-md-offset-11">
                        <input type="button" class="btn btn-circle default yellow-stripe add_more" value="Add" />
                    </div>

                    <div class="row text-center">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn yellow', 'style' => 'margin-top:20px; margin-bottom:20px;']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on("focus",".datepicker", function() {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('click', '.add_more', function () {
            var index = $('.list').data('index_no');
            $('.list').data('index_no', index + 1);
            var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                this.name = this.name.replace(/\d+/, index+1);
                this.id = this.id.replace(/\d+/, index+1);
                this.value = '';
            }).end();

            $('.moreTable').append(html);
        });

        $(document).on('click', '.remove', function () {
            var obj=$(this);
            var count= $('.single_list').length;
            if(count > 1){
                obj.closest('.single_list').remove();
            }
        });

        $(document).on('change', '.parent', function(){
            var obj = $(this);
            var parent_unit = obj.val();
            obj.closest('.item_tr').find('.administrative_unit_id').html('<option>Select</option>');
            if(parent_unit>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/SalesBudgets/ajax")?>',
                    data: {parent_unit: parent_unit},
                    success: function (data, status) {
                        if (data) {
                            obj.closest('.item_tr').find('.administrative_unit_id').html('<option>Select</option>');
                            obj.closest('.item_tr').find('.administrative_unit_id').html(data);
                        }
                    }
                });
            }
        });
    });
</script>