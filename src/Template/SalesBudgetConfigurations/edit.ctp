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
            <?= $this->Html->link(__('Sales Budget Configurations'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Sales Budget Configuration') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Sales Budget Configuration') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($salesBudgetConfiguration, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('level_no', ['options'=>$levelArr, 'empty'=>'Select']);
                        echo $this->Form->input('product_scope', ['options'=>[1=>'Specific', 2=>'All'], 'empty'=>'Select']);
                        echo $this->Form->input('sales_measure', ['options'=>[1=>'Quantity', 2=>'Value'], 'empty'=>'Select', 'class'=>'sales_measure form-control']);
                        if($salesBudgetConfiguration->sales_measure==1):
                            echo $this->Form->input('sales_measure_unit', ['options'=>Configure::read('pack_size_units'), 'empty'=>'Select', 'class'=>'form-control sales_measure_unit', 'templates'=>['inputContainer' => '<div class="sales_measure_unit_div form-group input {{required}}">{{content}}</div>']]);
                        elseif($salesBudgetConfiguration->sales_measure==2):
                            echo $this->Form->input('sales_measure_unit', ['options'=>Configure::read('pack_size_units'), 'empty'=>'Select', 'class'=>'form-control sales_measure_unit', 'templates'=>['inputContainer' => '<div class="hidden sales_measure_unit_div form-group input {{required}}">{{content}}</div>']]);
                        endif;
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on('change', '.sales_measure', function(){
            if(($(this).val()==1)){
                $('.sales_measure_unit_div').removeClass('hidden');
                $('.sales_measure_unit').val('');
            }else{
                $('.sales_measure_unit_div').addClass('hidden');
            }
        });
    })
</script>