<?php
use Cake\Core\Configure;
use Cake\View\Helper\SystemHelper;

?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?= $this->Html->link(__('Credit Note Events'), ['action' => 'index']) ?>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= __('Edit Credit Note Event') ?></li>

    </ul>
</div>


<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-pencil-square-o fa-lg"></i><?= __('Edit Credit Note Event') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-sm btn-success']); ?>
                </div>

            </div>
            <div class="portlet-body">
                <?= $this->Form->create($creditNoteEvent, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php
                        pr($creditNote);
                        echo $this->Form->input('customer_id', ['type' => 'text', 'readonly' => true, 'value' => $creditNoteEvent['customer']['name']]);
                        echo $this->Form->input('date', ['type' => 'text', 'value' => date('d-M-Y H:m:s', $creditNoteEvent['date']), 'readonly' => true]);
                        echo $this->Form->input('total_after_demurrage', ['required' => true, 'value' => $creditNoteEvent['total_after_demurrage']]);
                        echo $this->Form->input('demurrage_percentage', ['required' => true, 'id' => 'demurrage-percentage', 'value' => $creditNoteEvent['demurrage_percentage']]);
                        echo $this->Form->input('approval_status', ['required' => true, 'options' => Configure::read('approval_status')]);
                        //  echo $this->Form->input('adjustment_status');
                        //  echo $this->Form->input('status', ['options' => Configure::read('status_options')]);
                        ?>
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue pull-right', 'style' => 'margin-top:20px']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

<script>
    var prev_percentage = '<?php echo $creditNoteEvent['credit_note']['demurrage_percentage'];?>'
    var prev_total = '<?php echo $creditNoteEvent['credit_note']['total_after_demurrage'];?>'
     var tt = prev_percentage*prev_total
    var rr = tt/100;
    var dd = prev_total-rr
    console.log(dd);
    $(document).on('keypress', '#demurrage-percentage', function () {
        var percentage = parseFloat($("#demurrage-percentage").val())

    });
</script>

