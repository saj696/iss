<?php
use Cake\Core\Configure;
$status = Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Explore Offers'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-plus-square-o fa-lg"></i><?= __('Explore Offers') ?>
                </div>
            </div>

            <div class="portlet-body">
                <?= $this->Form->create('',['class' => 'form-horizontal', 'role' => 'form', 'action'=>'save']) ?>
                <div class="row">
                    <div class="col-md-7 col-md-offset-2">
                        <?php
                        echo $this->Form->input('offer_id', ['label'=>'Offer', 'options'=>$offers, 'class'=>'form-control offer', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('start_date', ['type'=>'text', 'class'=>'datepicker form-control start_date', 'required'=>'required']);
                        echo $this->Form->input('end_date', ['type'=>'text', 'class'=>'datepicker form-control end_date', 'required'=>'required']);
                        echo $this->Form->input('explore_level', ['label'=>'Customer Level', 'options'=>$exploreLevels, 'class'=>'form-control explore_level', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('parent_unit', ['options'=>[], 'class'=>'parent_unit form-control', 'empty'=>'Select', 'required'=>'required']);
                        echo $this->Form->input('unit_id', ['empty'=>'Select', 'required'=>'required', 'class'=>'form-control unit']);
                        ?>
                    </div>
                    <div class="col-md-12 text-center">
                        <span class="btn yellow calculate" style="margin:10px 0 20px 0">Calculate</span>
                    </div>
                    <div class="row popContainerExploreOffer" style="display: none; max-height: 80%; min-width: 50%; overflow: auto">
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on("keyup", ".numbersOnly", function(event) {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        $(document).on("focus",".datepicker", function() {
            $(this).datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });

        $(document).on('change', '.explore_level', function () {
            var obj = $(this);
            var explore_level = obj.val();
            obj.closest('.input').next().find('.parent_unit').html('<option value="">Select</option>');
            $('.unit').html('<option value="">Select</option>');

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportExploreOffers/ajax/parent_units")?>',
                data: {explore_level: explore_level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });

        $(document).on('change', '.parent_unit', function () {
            var obj = $(this);
            var parent_unit = obj.val();
            var explore_level = $('.explore_level').val();
            obj.closest('.input').next().find('.unit').html('<option value="">Select</option>');
            $('.unit').html('<option value="">Select</option>');

            $.ajax({
                type: 'POST',
                url: '<?= $this->Url->build("/ReportExploreOffers/ajax/units")?>',
                data: {parent_unit:parent_unit, explore_level: explore_level},
                success: function (data, status) {
                    obj.closest('.input').next().find('.col-sm-9').html('');
                    obj.closest('.input').next().find('.col-sm-9').html(data);
                }
            });
        });

        $(document).on('click', '.calculate', function () {
            var unit_id = $('.unit').val();
            var offer_id = $('.offer').val();
            var start_date = $('.start_date').val();
            var end_date = $('.end_date').val();

            if(unit_id>0 && offer_id>0) {
                $.ajax({
                    type: 'POST',
                    url: '<?= $this->Url->build("/ReportExploreOffers/calculation")?>',
                    data: {unit_id:unit_id, offer_id:offer_id, start_date:start_date, end_date:end_date},
                    success: function (data, status) {
                        $('.popContainerExploreOffer').show();
                        $('.popContainerExploreOffer').html(data);
                    }
                });
            }else{
                toastr.error('Select an Administrative Unit!');
                $('.load_result').hide();
            }
        });

        $(document).on("click",".crossSpan",function() {
            $(".popContainerExploreOffer").hide();
        });

//        $(document).on('click', '.calculate', function(){
//            var max_date = $(this).closest('.customerTr').find('span').attr("data-max");
//            var customer = $(this).closest('.customerTr').find('span').attr("data-customer");
//            var start_date = $('.start_date').val();
//            var end_date = $('.end_date').val();
//            var offer_id = $('.offer').val();
//
//            $.ajax({
//                type: 'POST',
//                url: '<?//= $this->Url->build("/ReportExploreOffers/calculation")?>//',
//                data: {max_date:max_date, start_date:start_date, end_date:end_date, offer_id:offer_id, customer:customer},
//                success: function (data, status) {
//
//                }
//            });
//        });
    });

</script>