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
            <?= $this->Html->link(__('Stores'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New Store') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New Store') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($store, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('name', ['required'=>'required']);
                        echo $this->Form->input('level_no', ['options'=>$administrativeLevels, 'class'=>'form-control level', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('unit_id', ['empty'=>'Select', 'required'=>'required', 'class'=>'form-control unit']);
                        echo $this->Form->input('type', ['options'=>Configure::read('store_types'), 'class'=>'form-control', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('address', ['rows'=>1]);
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
        $(document).on('change', '.level', function () {
            var obj = $(this);
            var level = obj.val();
            obj.closest('.input').next().find('.unit').html('<option value="">Select</option>');
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Stores/ajax")?>',
                data: {level: level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });
    });
</script>