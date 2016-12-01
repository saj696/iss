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
            <?= $this->Html->link(__('Users'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New User') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New User') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm grey-gallery']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($user, ['type' => 'file', 'class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        echo $this->Form->input('level_no', ['options' => $administrativeLevels, 'class'=>'form-control level', 'empty' => __('Select'), 'label'=>'Level']);
                        echo $this->Form->input('administrative_unit_id', ['empty' => __('Select'), 'label'=>'Unit']);
                        echo $this->Form->input('user_group_id', ['options' => $userGroups, 'empty' => __('Select')]);
                        echo $this->Form->input('full_name_en',['label' => 'Full Name']);
                        echo $this->Form->input('username');
                        echo $this->Form->input('password', ['type'=>'password','required' => true]);
                        echo $this->Form->input('confirm_password', ['type'=>'password','required' => true]);
                        echo $this->Form->input('picture_file', ['type'=>'file', 'label'=>'Photo']);
                        echo $this->Form->input('depot_id', ['options' => $depots, 'empty' => __('Select')]);
                        echo $this->Form->input('warehouse_id', ['options' => $warehouses, 'empty' => __('Select')]);
                        echo $this->Form->input('task_force_id', ['options' => $taskForces, 'class'=>'form-control task_force',  'empty' => __('Select')]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn yellow pull-right', 'style' => 'margin-top:20px']) ?>
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
                url: '<?= $this->Url->build("/Customers/ajax")?>',
                data: {level: level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });

        $(document).on('change', '.task_force', function () {
            var obj = $(this);
            var task_force_id = obj.val();
            var admin_level = $('.level').val();
            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/Users/checkTaskForceLevel")?>',
                data: {task_force_id: task_force_id, admin_level:admin_level},
                success: function (data, status) {
                    if(data==0){
                        obj.val('');
                        toastr.error('Selected Task Force is not applicable for this level');
                    }
                }
            });
        });
    });
</script>