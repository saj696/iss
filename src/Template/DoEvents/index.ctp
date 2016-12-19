<?php
$status = \Cake\Core\Configure::read('status_options');
use Cake\Routing\Router;
use Cake\Core\Configure;

?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?= $this->Url->build(('/Dashboard'), true); ?>"><?= __('Dashboard') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><?= $this->Html->link(__('Do Events'), ['action' => 'index']) ?></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- BEGIN BORDERED TABLE PORTLET-->

        <form class="form-horizontal" method="post"
              action="<?php echo Router::url('/', true); ?>DoEvents/makeDoDs" enctype="multipart/form-data">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list-alt fa-lg"></i><?= __('Do Event List') ?>
                </div>
                <div class="tools">
                    <button type="submit" class="btn btn-danger">Make...</button>
                </div>

            </div>
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th><?= __('Sl. No.') ?></th>
                            <th><?= __('Sender') ?></th>

                            <th><?= __('Created Date') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($doEvents as $key => $doEvent) { ?>
                            <tr>
                                <td><?= $this->Number->format($key + 1) ?></td>
                                <td><?= $doEvent->sender->full_name_en ?></td>
                                <td><?= date('d-M-Y',$doEvent->created_date)?></td>
                                <td class="actions">
                                    <?php
                                    if($doEvent['action_status']==Configure::read('do_object_event_action_status')['awaiting_approval']) {
                                        echo $this->Html->link(__('View'), ['action' => 'view', $doEvent->id], ['class' => 'btn btn-sm btn-info']);
                                    }else{ ?>

                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" value="<?= $doEvent['id']?>" name="events[]"> Select
                                                    </label>
                                                </div>

                                    <?php

                                    }

                                    ?>

                                </td>
                            </tr>

                        <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
            </form>
        <!-- END BORDERED TABLE PORTLET-->
    </div>
</div>

