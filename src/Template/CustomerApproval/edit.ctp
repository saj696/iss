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
            <?= $this->Html->link(__('Customers'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Customer') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Customer') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($customer, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                            echo $this->Form->input('credit_limit',['label'=> 'Credit Limit','type'=>'number','required'=>'required']);
                            echo $this->Form->input('credit_invoice_days',['label'=> 'Credit Invoice Days','type'=>'number','required'=>'required']);
                            echo $this->Form->input('cash_invoice_days',['label'=> 'Cash Invoice Days','type'=>'number','required'=>'required']);
                            echo $this->Form->input('business_type', ['options'=>Configure::read('customer_business_types'),'class'=>'form-control business_type']);
                            echo $this->Form->input('user_group', ['label'=>'User Group', 'options' => $userGroups, 'class'=>'form-control user_group', 'empty' => __('Select')]);
                            echo $this->Form->input('credit_approved_by', ['type'=>'select', 'empty' => 'Select', 'label'=>'Approved By', 'class'=>'form-control credit_approved_by']);
                            echo $this->Form->input('credit_approval_date',['type'=>'text', 'class'=>'form-control datepicker', 'label'=>'Approval Date']);
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
        $(document).on("focus",".datepicker", function()
        {
            $(this).removeClass('hasDatepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('change', '.user_group', function() {
            var obj = $(this);
            var user_group = obj.val();
            $('.credit_approved_by').html('<option value="">Select</option>');

            if(user_group>0) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/CustomerApproval/ajax")?>',
                    data: {user_group: user_group},
                    success: function (data, status) {
                        obj.closest('.input').next().find('.col-sm-9').html('');
                        obj.closest('.input').next().find('.col-sm-9').html(data);
                    }
                });
            } else {
                $('.credit_approved_by').html('<option value="">Select</option>');
            }
        }); 
    });
</script>
