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
                        echo $this->Form->input('level_no', ['options'=>$administrativeLevels, 'label'=>'Level', 'class'=>'form-control level', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('administrative_unit_id', ['label'=>'Unit', 'empty' => __('Select'),'class'=> 'form-control unit']);
                        echo $this->Form->input('prefix', ['options'=>$administrativeLevels, 'label'=>'Prefix Level', 'class'=>'form-control prefix', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('code', ['class'=>'form-control codeCustomer', 'readonly']);
                        echo $this->Form->input('name');
                        echo $this->Form->input('address', ['rows'=>1]);
                        echo $this->Form->input('proprietor');
                        echo $this->Form->input('contact_person');
                        //                        echo $this->Form->input('business_type');
                        echo $this->Form->input('mobile');
                        echo $this->Form->input('telephone');
                        echo $this->Form->input('email');
                        //                        echo $this->Form->input('credit_limit');
                        //                        echo $this->Form->input('credit_invoice_days');
                        //                        echo $this->Form->input('cash_invoice_days');
                        echo $this->Form->input('is_mango', ['type'=>'checkbox', 'value'=>1]);
                        echo $this->Form->input('is_potato', ['type'=>'checkbox', 'value'=>1]);
                        echo $this->Form->input('customer_type', ['options'=>Configure::read('customer_types')]);
                        echo $this->Form->input('pesticide_no',['label'=> 'Pesticide License No']);
                        echo $this->Form->input('pesticide_issue_date',['type'=>'text','class'=>'form-control datepicker','label'=>['text'=>__('Pesticide License Issue Date')]]);
                        echo $this->Form->input('pesticide_end_date',['type'=>'text','class'=>'form-control datepicker','label'=>['text'=>__('Pesticide License End Date')]]);
                        echo $this->Form->input('trade_no',['label'=> 'Trade License No']);
                        echo $this->Form->input('trade_issue_date',['type'=>'text','class'=>'form-control datepicker','label'=>['text'=>__('Trade License Issue Date')]]);
                        echo $this->Form->input('trade_end_date',['type'=>'text','class'=>'form-control datepicker','label'=>['text'=>__('Trade License End Date')]]);
                        echo $this->Form->input('picture_file', ['type'=>'file', 'label'=>'Photo']);
                        echo $this->Form->input('nid_file', ['type'=>'file', 'label'=>'NID']);
                        echo $this->Form->input('signature_file', ['type'=>'file', 'label'=>'Signature']);
                        echo $this->Form->input('customer_status', ['options'=>Configure::read('customer_status')]);
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
                url: '<?= $this->Url->build("/Customers/ajax")?>',
                data: {level: level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });
    });
</script>
