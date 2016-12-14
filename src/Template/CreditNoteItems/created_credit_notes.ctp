<?php
$status = \Cake\Core\Configure::read('status_options');
$approval_status = \Cake\Core\Configure::read('credit_note_approval_status');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Credit Note Items'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Credit Note') ?>
                </div>
                <div class="tools">
                    <?= $this->Html->link(__('New Credit Note'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']); ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Customer Name.') ?></th>
                            <th><?= __('Date ') ?></th>
                            <th><?= __('Demurrage') ?></th>
                            <th><?= __('Tota After Demurrage') ?></th>
                            <th><?= __('Approval Status') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        //pr($created_credit_notes);die;
                        foreach ($created_credit_notes as $key => $notes) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $notes->has('customer') ?
                                        $this->Html->link($notes->customer
                                            ->name, ['controller' => 'Customers',
                                            'action' => 'view', $notes->customer
                                                ->id]) : '' ?></td>
                                <td><?= date('d-m-Y', $notes->date) ?></td>
                                <td><?= $this->Number->format($notes->demurrage_percentage) . '%' ?></td>
                                <td><?= $this->Number->format($notes->total_after_demurrage) ?></td>
                                <td><?= __($approval_status[$notes->approval_status]) ?></td>

                                <td class="actions">
                                    <?php
                                    if ($notes->approval_status ==1) { ?>
                                        <button type="button" data-credit-note-id="<?=$notes->id?>" class="btn btn-warning btn-sm approve-button" data-toggle="modal"
                                                data-target="#approval_modal">Send For Approval
                                        </button>
                                    <?php } else {

                                    }
                                    echo $this->Html->link(__('View'), ['action' => 'index', $notes->id], ['class' => 'btn btn-sm btn-primary']);
                                    ?>
                                </td>
                            </tr>

                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="approval_modal" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Select Recipient</h4>
                            </div>
                            <div class="modal-body">
                                <?= $this->Form->create($send_for_approval, ['class' => 'form-horizontal', 'role' => 'form']) ?>
                                <div class="row text-center">
                                    <input type="checkbox" class="form-control forward_check" value="1"/> Send For
                                    Approval?
                                </div>
                                <br>

                                <?php echo $this->Form->input('credit_note_id', ['type' => 'hidden']); ?>
                                <div class="row col-md-offset-5 text-center recipient_div hidden">
                                    <?php echo $this->Form->input('recipient_id', ['required' => true, 'options' => $recipient_list, 'empty' => 'Select user', 'class' => 'form-control recipient_id', 'templates' => ['label' => '']]); ?>
                                </div>
                                <?= $this->Form->button(__('Send'), ['name' => 'for_approval', 'id' => 'approval', 'class' => 'btn blue center-block approve-button', 'style' => 'margin-top:30px']) ?>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>

                    </div>
                </div>

                <ul class="pagination">
                    <?php
                    echo $this->Paginator->prev('<<');
                    echo $this->Paginator->numbers();
                    echo $this->Paginator->next('>>');
                    ?>
                </ul>
            </div>
        </div>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>
<script>
    $("#approval").attr('disabled', true);
    $(document).on('click', '.approve-button', function () {
        var credit_note_id =$(this).attr('data-credit-note-id');
        $('#credit-note-id').attr('value', credit_note_id);
    })
    $(document).on('click', '.forward_check', function () {
        if ($(this).attr('checked')) {
            $("#approval").attr('disabled', false);
            $(document).on('change', '#recipient-id', function () {
                $(this).val() != '' ? $("#approval").attr('disabled', false) : $("#approval").attr('disabled', true);
            });
            $(".recipient_div").removeClass('hidden');
        } else {
            $("#approval").attr('disabled', true);
            $(".recipient_div").addClass('hidden');
        }
    });
</script>

