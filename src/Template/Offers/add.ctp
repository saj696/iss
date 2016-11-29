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
            <?= $this->Html->link(__('Offers'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Offer') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Offer') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($offer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6" style="margin-left: 25px;">
                        <?php
                        echo $this->Form->input('general_conditions', ['type'=>'textarea', 'rows'=>11, 'class'=>'form-control conditions', 'templates'=>['textarea' => '<div class="col-sm-9"><textarea class="form-control condition" name="{{name}}"{{attrs}}>{{value}}</textarea></div>']]);
                        ?>
                    </div>
                    <div class="col-md-5" style="max-height: 250px; overflow-y: scroll;">
                        <table class="table table-bordered" style="overflow: scroll">
                            <?php foreach($functionArray as $func):?>
                            <tr class="func_tr"><td><?= $func?></td></tr>
                            <?php endforeach;?>
                        </table>
                    </div>
                </div>

                <div class="row text-center">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn yellow submit', 'style' => 'margin:20px']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on('keyup', '.condition', function(){
            var str = $('textarea.condition').val();
            alert(str);
            toastr.info('credit_closing_percentage (period, payment)');
        });
    });
</script>

