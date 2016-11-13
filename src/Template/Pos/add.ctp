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
            <?= $this->Html->link(__('POs'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('New PO') ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Add New PO') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create($po, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('customer_level_no', ['label'=>'Customer Level', 'empty'=>'Select', 'options'=>$administrativeLevels, 'class'=>'form-control level_no', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('customer_unit', ['type'=>'select', 'label'=>'Customer Location', 'empty'=>'Select', 'class'=>'form-control customer_unit', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 unit_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('credit_limit', ['label'=>'Credit Limit', 'type'=>'text', 'readonly', 'class'=>'form-control credit_limit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('cash_invoice_days', ['label'=>'Cash Invoice Days', 'type'=>'text', 'class'=>'form-control cash_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('invoice_type', ['type'=>'select', 'options'=>[1=>'Cash', 2=>'Credit'], 'label'=>'Invoice Type', 'empty'=>'Select', 'class'=>'form-control invoice_type', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        echo $this->Form->input('customer_id', ['label'=>'Customer', 'empty'=>'Select', 'options'=>[], 'class'=>'form-control customer_id', 'templates'=>['label' =>'<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>', 'select' => '<div id="container_{{name}}" class="col-sm-6 customer_select"><select name="{{name}}"{{attrs}} class="form-control">{{content}}</select></div>']]);
                        echo $this->Form->input('available_credit', ['label'=>'Available Credit', 'type'=>'text', 'readonly', 'class'=>'form-control available_credit', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('credit_invoice_days', ['label'=>'Credit Invoice Days', 'type'=>'text', 'class'=>'form-control credit_invoice_days', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        echo $this->Form->input('field_po_no', ['label'=>'Field PO No.', 'type'=>'text', 'class'=>'form-control', 'templates'=>['label' => '<label {{attrs}} class="col-sm-5 control-label text-right" >{{text}}</label>','input' => '<div class="col-sm-6 container_{{name}}"> <input {{attrs}} class="form-control" type="{{type}}" name="{{name}}"></div>']]);
                        ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on('change', '.level_no', function () {
            var obj = $(this);
            var level = obj.val();
            $('.customer_level_no').html('<option>Select</option>');

            if(level>0 || level==0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Pos/getUnit")?>',
                    data: {level: level},
                    success: function (data, status) {
                        //console.log(data);
                        $('.unit_select').html('');
                        $('.unit_select').html(data);
                    }
                });
            }else{
                $('.customer_level_no').html('<option>Select</option>');
            }
        });

        $(document).on('change', '.customer_unit', function () {
            var obj = $(this);
            var unit = obj.val();
            $('.customer_id').html('<option>Select</option>');

            if(unit>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Pos/getCustomer")?>',
                    data: {unit: unit},
                    success: function (data, status) {
                        //console.log(data);
                        $('.customer_select').html('');
                        $('.customer_select').html(data);
                    }
                });
            }else{
                $('.customer_id').html('<option>Select</option>');
            }
        });

        $(document).on('change', '.customer_id', function () {
            var obj = $(this);
            var customer_id = obj.val();

            if(customer_id>0){
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/Pos/getCustomerDetail")?>',
                    data: {customer_id: customer_id},
                    success: function (data, status) {
                        var data = JSON.parse(data);
                        $('.credit_limit').val(data.credit_limit);
                        $('.available_credit').val(data.available_credit);
                        $('.cash_invoice_days').val(data.cash_invoice_days);
                        $('.credit_invoice_days').val(data.credit_invoice_days);
                    }
                });
            }else{
                $('.credit_limit').val(0);
                $('.available_credit').val(0);
                $('.cash_invoice_days').val(0);
                $('.credit_invoice_days').val(0);
            }
        });
    });
</script>