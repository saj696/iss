<?php
use Cake\Core\Configure;

$status = Configure::read('status_options');
$user = $this->request->Session()->read('Auth')['User'];
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Receive Items'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Receive Items List') ?>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Sending Date') ?></th>
                            <th><?= __('Items') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($events as $key => $event)
                        {
                            ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= date('d-m-Y', $event->created_date) ?></td>
                                <td>
                                    <?php
                                    $items = $event['transfer_resource']['transfer_items'];
                                    $size = sizeof($items);
                                    if(sizeof($size>0)):
                                        foreach($items as $key=>$item):
                                            if($size==$key+1):
                                                echo $itemArray[$item['item_id']]. '('.$item['quantity'].')';
                                            else:
                                                echo $itemArray[$item['item_id']]. '('.$item['quantity'].') | ';
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>
                                </td>
                                <td class="actions" width="38%">
                                    <?php
                                    if($user['user_group_id'] == Configure::read('depot_in_charge_ug')):
                                        ?>
                                        <button class="btn btn-sm btn-success distribute">Distribute</button>
                                        <?php
                                    elseif($user['user_group_id'] == Configure::read('warehouse_in_charge_ug')):
                                        echo $this->Html->link(__('Receive'), ['action' => 'receive', $event->id], ['class' => 'btn btn-sm btn-success']);
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
    });
</script>