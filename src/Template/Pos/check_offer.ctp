<?php
/**
 * Created by PhpStorm.
 * User: JR
 * Date: 14-Nov-16
 * Time: 11:38 AM
 */
?>

<table class="table table-bordered">
    <?php
    if (sizeof($wonOffers) > 0):
        ?>
        <tr><td colspan="12" class="text-center"><span class="label label-warning crossSpan">Offer Items</span></td></tr>
        <?php
        foreach($offerItems as $offer_id=>$items):
            foreach($items as $item):
            ?>
            <tr>
                <td colspan="2" class="text-center">
                    <?= $itemArray[$item]?>
                    <input type="hidden" class="offer_items" data-offer="<?=$offer_id?>" name="offer_items[]" value="<?=$item?>">
                </td>
            </tr>
        <?php
            endforeach;
        endforeach;
        ?>
        <tr><td colspan="12" class="text-center"><span class="label label-success crossSpan">Offer Detail</span></td></tr>
        <?php
        foreach ($wonOffers as $wonOffer):
            ?>
            <tr>
                <td>
                    <table class="table table-bordered">
                        <tr>
                            <td>Offer Type</td>
                            <td><?= $wonOffer['offer_type'] ?></td>
                        </tr>
                        <tr>
                            <td>Offer Name</td>
                            <td><?= $wonOffer['offer_name'] ?></td>
                        </tr>
                        <tr>
                            <td>Offer Unit Name</td>
                            <td><?= $wonOffer['offer_unit_name'] ?></td>
                        </tr>
                        <tr>
                            <td>Amount Type</td>
                            <td><?= $wonOffer['amount_type'] ?></td>
                        </tr>
                        <tr>
                            <td>Payment Mode</td>
                            <td><?= $wonOffer['payment_mode'] ?></td>
                        </tr>
                        <tr>
                            <td>Amount Unit</td>
                            <td><?= $wonOffer['amount_unit'] ?></td>
                        </tr>
                        <tr>
                            <td><label class="label label-default">Value</label></td>
                            <td><?= $wonOffer['value'] ?><input type="hidden" class="check_offer_value" value="<?= $wonOffer['value']?>" /></td>
                        </tr>
                    </table>
                </td>
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
            <span style="cursor: pointer;" class="label label-danger crossSpan">Close</span>
            <span style="cursor: pointer;" class="label label-warning closeAndApply">Close & Apply</span>
        </td>
    </tr>
</table>
