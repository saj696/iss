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
        <li><?= $this->Html->link(__('Chalan Deliveries'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box grey-cascade">
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
                            <th><?= __('Chalan No.') ?></th>
                            <th><?= __('Items')?></th>
                            <th><?= __('Chalan Date') ?></th>
                            <th><?= __('Action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($events as $key => $event) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $event->invoice_chalan->chalan_no?></td>
                                <td>
                                    <?php
                                    $items = '';
                                    $size = sizeof($event->invoice_chalan->invoice_chalan_details);
                                    foreach($event->invoice_chalan->invoice_chalan_details as $key=>$detail){
                                        if($key==$size-1){
                                            $items .= $itemArray[$detail['product_id']].' [<span style="color:red; font-weight:bold;">'.$detail['quantity'].'</span>]';
                                        }else{
                                            $items .= $itemArray[$detail['product_id']].' [<span style="color:red; font-weight:bold;">'.$detail['quantity'].'</span>] | ';
                                        }
                                    }
                                    echo $items;
                                    ?>
                                </td>
                                <td><?= date('d-m-Y', $event->invoice_chalan->created_date)?></td>
                                <td>
                                    <?php
                                    if($event->is_action_taken==1){
                                        echo $this->Html->link(__('Deliver'), ['action' => 'edit', $event->id],['disabled', 'class'=>'btn btn-circle default green-stripe', 'confirm' => __('Are you sure you want to deliver?')]);
                                    }else{
                                        echo $this->Html->link(__('Deliver'), ['action' => 'edit', $event->id],['class'=>'btn btn-circle default green-stripe', 'confirm' => __('Are you sure you want to deliver?')]);
                                    }
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

