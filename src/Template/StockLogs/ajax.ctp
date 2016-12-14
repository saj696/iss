<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 08-Oct-16
 * Time: 10:55 AM
 */
use Cake\Core\Configure;

$config_stock_types = Configure::read('stock_log_types');
unset($config_stock_types[3], $config_stock_types[4], $config_stock_types[5],$config_stock_types[6]);
if (sizeof($dropArray) > 0):
    ?>
    <div class="col-lg-12">
        <div class="list" data-index_no="0">
            <div class="itemWrapper">
                <table class="table table-bordered moreTable">
                    <tr>
                        <th><?= __('Item') ?></th>
                        <th><?= __('Type') ?></th>
                        <th><?= __('Quantity') ?></th>
                        <th></th>
                    </tr>
                    <tr class="item_tr single_list">
                        <td style="width: 35%;"><?php echo $this->Form->input('details.0.stock_id', ['options' => $dropArray, 'required' => 'required', 'style' => 'max-width: 100%', 'class' => 'form-control item', 'empty' => __('Select'), 'templates' => ['label' => '']]); ?></td>
                        <td style="width: 35%;"><?php echo $this->Form->input('details.0.type', ['options' => $config_stock_types, 'empty' => 'Select', 'style' => 'width: 100%', 'required' => 'required', 'class' => 'form-control', 'templates' => ['label' => '']]); ?></td>
                        <td><?php echo $this->Form->input('details.0.quantity', ['type' => 'text', 'style' => 'width: 100%', 'class' => 'form-control quantity numbersOnly', 'required', 'templates' => ['label' => '']]); ?></td>
                        <td width="50px;"><span class="btn btn-sm btn-circle btn-danger remove pull-right">X</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row col-md-offset-11">
        <input type="button" class="btn btn-circle btn-warning add_more" value="Add"/>
    </div>

    <div class="row text-center" style="margin-bottom: 20px;">
        <?= $this->Form->button(__('Submit'), ['class' => 'btn blue', 'style' => 'margin-top:20px']) ?>
    </div>
<?php else: ?>
    <div class="col-lg-12">
        <table class="table table-bordered">
            <tr>
                <td class="text-center"><label class="label label-danger">No Items</label></td>
            </tr>
        </table>
    </div>
<?php endif; ?>
