<?php
$status = \Cake\Core\Configure::read('status_options');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Decided Requests'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Chalan List') ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?= __('Sl. No.') ?></th>
                                <th><?= __('Chalan Date') ?></th>
                                <th><?= __('Chalan No.') ?></th>
                                <th><?= __('Warehouse')?></th>
                                <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($events as $key => $event)
                        {
                            ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= date('d-m-Y', $event->created_date) ?></td>
                                <td><?= $event['transfer_resource']['serial_no'] ?></td>
                                <td><?= $warehouses[$event['transfer_resource']['transfer_items'][0]['warehouse_id']]?></td>
                                <td class="actions" width="20%">
                                    <?php
                                    if($event['is_action_taken']==0):
                                        echo $this->Html->link(__('Send Delivery'), ['action' => 'deliver', $event->id], ['class' => 'btn btn-sm btn-primary']);
                                    else:
                                        echo $this->Html->link(__('Send Delivery'), ['action' => 'deliver', $event->id], ['class' => 'btn btn-sm btn-primary', 'disabled']);
                                    endif;
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
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
    </form>
    </div>
</div>

<script>
    $(document).ready(function()
    {
        $(document).on("click", ".forward", function(event)
        {
            $(".popContainer").hide();
            $(this).closest('td').find('.popContainer').show();
        });

        $(document).on("click",".crossSpan",function()
        {
            $(".popContainer").hide();
        });

        $(document).on("click", ".warehouse_for_chalan", function()
        {
            var obj = $(this);
            var myArr = [];
            $( ".warehouse_for_chalan" ).each(function( index ) {
                if($(this).prop('checked')){
                    myArr.push($(this).attr('data-warehouse-id'));
                }
            });

            //console.log(myArr);
            var uniqueArr = uniqueArray(myArr);
            if(uniqueArr.length>1) {
                $(this).prop('checked', false);
                $(this).closest('span').removeClass('checked');
                alert('Multiple warehouse not allowed! Make chalan for a single warehouse.');
            }
        });

        $("#chalanForm").submit(function(e) {
            var self = this;
            e.preventDefault();

            $( ".warehouse_for_chalan" ).each(function( index ) {
                if($(this).prop('checked')){
                    var warehouse_id = $(this).attr('data-warehouse-id');
                    $(".warehouse").html('<input type="hidden" name="warehouse_id" value="'+warehouse_id+'" />');
                    return false;
                }
            });
            self.submit();
        });
    });

    function uniqueArray(arr) {
        var i,
            len = arr.length,
            out = [],
            obj = { };

        for (i = 0; i < len; i++) {
            obj[arr[i]] = 0;
        }
        for (i in obj) {
            out.push(i);
        }
        return out;
    }
</script>