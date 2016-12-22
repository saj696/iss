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
                        echo $this->Form->input('credit_limit',['type' => 'text','label'=> 'Credit Limit','class' => 'form-control creditLimit']);
                        echo $this->Form->input('increase_credit_limit',['type' => 'text','label'=> 'Increase Amount', 'class' => 'form-control increaseCredit']);
                        echo $this->Form->input('credit_invoice_days',['label'=> 'Credit Invoice Days','type'=>'text','required'=>'required']);
                        echo $this->Form->input('cash_invoice_days',['label'=> 'Cash Invoice Days','type'=>'text','required'=>'required']);
                        echo $this->Form->input('business_type', ['options'=>Configure::read('customer_business_types'),'empty' => __('Select') ,'class'=>'form-control business_type']);
                        echo $this->Form->input('user_group', ['label'=>'User Group', 'options' => $userGroups, 'class'=>'form-control user_group', 'empty' => __('Select')]);
                        echo $this->Form->input('credit_approved_by', ['type'=>'select', 'empty' => 'Select', 'label'=>'Approved By', 'class'=>'form-control credit_approved_by']);
                        echo $this->Form->input('credit_approval_date',['type'=>'text','placeholder'=>'Select Date', 'class'=>'form-control datepicker', 'label'=>'Approval Date']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php  $count = count($customer['bank_informations']);
                        if(sizeof($customer['bank_informations'])>0): ?>

                        <div class="list" data-index_no="<?= $count-1 ?>">
                            <div class="itemWrapper">
                                <h4 class="forInput">Bank Informations</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th><?= __('Check Quantity') ?></th>
                                        <th><?= __('Cheque Number') ?></th>
                                        <th><?= __('Bank Name') ?></th>
                                        <th><?= __('Branch Name') ?></th>
                                        <th><?= __('Comments') ?></th>
                                        <th><?= __('Remove') ?></th>
                                    </tr>
                                    <?php

                                    foreach ($customer['bank_informations'] as $key=>$cus_row):
                                        ?>
                                        <tr class="item_tr single_list" data-formulation-id="">
                                            <td><?php

                                                echo $this->Form->input("bank_informations.".$key.".id",['type'=>'hidden','value'=>$cus_row['id']]);
                                                echo $this->Form->input("bank_informations.".$key.".check_quantity", ['type' => 'text',  'class' => 'form-control quantity', 'templates' => ['label' => '']]);
                                                ?>
                                            </td>
                                            <td><?php  echo $this->Form->input("bank_informations.".$key.".check_number", ['type' => 'text' ,'class' => 'form-control number','templates' => ['label' => '']]);?></td>
                                            <td><?php echo $this->Form->input("bank_informations.".$key.".bank_name", ['type' => 'text', 'class' => 'form-control numbersOnly bankName', 'templates' => ['label' => '']]); ?></td>
                                            <td><?php echo $this->Form->input("bank_informations.".$key.".branch_name", ['type' => 'text','class' => 'form-control branchName', 'templates' => ['label' => '']]); ?></td>
                                            <td><?php echo $this->Form->input("bank_informations.".$key.".comment", ['type' => 'text','class' => 'form-control branchName', 'templates' => ['label' => '']]); ?></td>
                                            <td>
                                                <span class="btn btn-sm btn-circle btn-danger remove">X</span>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach;
                                    else:
                                    ?>
                                    <div class="list" data-index_no="0">
                                        <div class="itemWrapper">
                                            <h4 class="forInput">Bank Informations</h4>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th><?= __('Check Quantity') ?></th>
                                                    <th><?= __('Cheque Number') ?></th>
                                                    <th><?= __('Bank Name') ?></th>
                                                    <th><?= __('Branch Name') ?></th>
                                                    <th><?= __('Comments') ?></th>
                                                    <th><?= __('Remove') ?></th>
                                                </tr>
                                                <tr class="item_tr single_list" data-formulation-id="">
                                                    <td><?php
                                                        echo $this->Form->input("bank_informations.0.check_quantity", ['type' => 'text',  'class' => 'form-control quantity', 'templates' => ['label' => '']]);
                                                        ?>
                                                    </td>
                                                    <td><?php  echo $this->Form->input("bank_informations.0.check_number", ['type' => 'text' ,'class' => 'form-control number','templates' => ['label' => '']]);?></td>
                                                    <td><?php echo $this->Form->input("bank_informations.0.bank_name", ['type' => 'text', 'class' => 'form-control numbersOnly bankName', 'templates' => ['label' => '']]); ?></td>
                                                    <td><?php echo $this->Form->input("bank_informations.0.branch_name", ['type' => 'text','class' => 'form-control branchName', 'templates' => ['label' => '']]); ?></td>
                                                    <td><?php echo $this->Form->input("bank_informations.0.comment", ['type' => 'text','class' => 'form-control branchName', 'templates' => ['label' => '']]); ?></td>
                                                    <td>
                                                        <span class="btn btn-sm btn-circle btn-danger remove">X</span>
                                                    </td>
                                                </tr>
                                                <?php
                                                endif;
                                                ?>
                                            </table>
                                            <input type="button" class="btn btn-circle btn-warning add_more" style="float: right" value="Add"/>
                                        </div>
                                    </div>
                            </div>
                            <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin:20px']) ?>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            //    Date Picker Class
            $(document).ready(function(){

                var creditLimitP = $('.creditLimit').val();
//                $('.totalCredit').val(creditLimit);

                $(document).on("focus",".datepicker", function()
                {
                    $(this).removeClass('hasDatepicker').datepicker({
                        dateFormat: 'dd-mm-yy'
                    });
                });

//        Generate Approval Name
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

//      Add more function
                $(document).on('click', '.add_more', function () {
                    var index = $('.list').data('index_no');
                    $('.list').data('index_no', index + 1);
                    var html = $('.itemWrapper .item_tr:last').clone().find('.form-control').each(function () {
                        this.name = this.name.replace(/\d+/, index + 1);
                        this.id = this.id.replace(/\d+/, index + 1);
                        this.value = '';
                    }).end();

                    $('.table').append(html);
                });

//        Remove single row
                $(document).on('click', '.remove', function () {
                    var obj = $(this);
                    var count = $('.single_list').length;
                    if (count > 1) {
                        obj.closest('.single_list').remove();
                    }
                });

//        total amount calculation
                var creditLimit = $('.creditLimit').val();
                $(document).on('keyup', '.increaseCredit', function(){
                    var obj = $(this);
                    var increaseAmount = obj.val();
                    console.log(creditLimit);
                    var totalCredit = parseFloat(increaseAmount) + parseFloat(creditLimit);
                    $('.creditLimit').val(totalCredit);

                    if(increaseAmount == '')
                    {
                        $('.creditLimit').val(creditLimitP);
                    }
                });

            });
        </script>
