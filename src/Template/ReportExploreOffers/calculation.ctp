<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 24-Jan-17
 * Time: 1:19 PM
 */
?>
<table class="table table-bordered">
    <?php
    if (sizeof($finalArray) > 0):
        ?>
        <tr><td colspan="12" class="text-center"><span class="label label-success crossSpan">Offer Detail</span></td></tr>
        <tr>
            <th>Customer Name</th>
            <th>Cash Discount</th>
            <th>Achieved</th>
            <th>Due</th>
<!--            <td>Product Bonus</td>-->
<!--            <td>Awards</td>-->
        </tr>
        <?php
        foreach ($customers as $customer):
            ?>
            <tr>
                <td>
                    <?= $customer['name']?>
                </td>
                <td>
                    <?php
                        echo isset($finalArray[$customer['id']]['cash_discount'])?$finalArray[$customer['id']]['cash_discount']:0;
                    ?>
                    <input type="hidden" name="offer[<?=$customer['id']?>][type]" value="<?=isset($finalArray[$customer['id']]['type'])?$finalArray[$customer['id']]['type']:0?>" />
                    <input type="hidden" name="offer[<?=$customer['id']?>][cash_discount]" value="<?=isset($finalArray[$customer['id']]['cash_discount'])?$finalArray[$customer['id']]['cash_discount']:0?>" />
                </td>
                <td>
                    <?php
                        echo isset($achieved_total_cash_discounts[$customer['id']])?$achieved_total_cash_discounts[$customer['id']]:0;
                    ?>
                    <input type="hidden" name="offer[<?=$customer['id']?>][achieved]" value="<?=isset($achieved_total_cash_discounts[$customer['id']])?$achieved_total_cash_discounts[$customer['id']]:0?>" />
                </td>
                <td>
                    <?php
                    $achieved = 0;
                    $cash_discount = 0;
                    if(isset($achieved_total_cash_discounts[$customer['id']])){
                        $achieved = $achieved_total_cash_discounts[$customer['id']];
                    }
                    if(isset($finalArray[$customer['id']]['cash_discount'])){
                        $cash_discount = $finalArray[$customer['id']]['cash_discount'];
                    }
                    $due = $cash_discount - $achieved;
                    echo $due;
                    ?>
                    <input type="hidden" name="offer[<?=$customer['id']?>][due]" value="<?=$due?>" />
                </td>
<!--                <td>--><?//= isset($finalArray[$customer['id']]['product_bonus'])?$finalArray[$customer['id']]['product_bonus']:0; ?><!--</td>-->
<!--                <td>-->
<!--                    --><?php
//                        if(isset($finalArray[$customer['id']]['awards'])){
//                            if(sizeof($finalArray[$customer['id']]['awards'])>0){
//                                ?>
<!--                                <table class="table table-bordered">-->
<!--                                    <tr>-->
<!--                                        <th>Award name</th>-->
<!--                                        <th>Cash Equivalent</th>-->
<!--                                    </tr>-->
<!--                                    --><?php
//                                    foreach($finalArray[$customer['id']]['awards'] as $award){
//                                        ?>
<!--                                        <tr>-->
<!--                                            <td>--><?//= $award['name']?><!--</td>-->
<!--                                            <td>--><?//= $award['cash_equivalent']?><!--</td>-->
<!--                                        </tr>-->
<!--                                        --><?php
//                                    }
//                                    ?>
<!--                                </table>-->
<!--                                --><?php
//                            }
//                        }else{
//                            echo 'N/A';
//                        }
//                    ?>
<!--                </td>-->
            </tr>
            <?php
        endforeach;
    else:
        ?>
        <tr>
            <td colspan="12" class="text-center"><span class="label label-warning crossSpan">No Offer</span></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td colspan="12" class="text-center">
            <button style="cursor: pointer;" class="btn btn-danger crossSpan">Close</button>
            <button type="submit" style="cursor: pointer;" class="btn btn-success crossSpan">Save</button>
        </td>
    </tr>
</table>

