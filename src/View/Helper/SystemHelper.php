<?php
namespace App\View\Helper;

use App\Model\Table\AdministrativeUnitsTable;
use App\Model\Table\ItemUnitsTable;
use Cake\Database\Schema\Table;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * System helper
 */
class SystemHelper extends Helper
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];


    public function display_date($date)
    {
        if (strlen($date) < 1) {
            return '';
        }
        $display_string = date('d-M-Y', $date);
        if ($display_string === false) {
            return '';
        } else {
            return $display_string;
        }
    }

    public function display_date_time($date)
    {
        if (strlen($date) < 1) {
            return '';
        }
        $display_string = date('d-M-Y H:m:s', $date);
        if ($display_string === false) {
            return '';
        } else {
            return $display_string;
        }
    }

    public function Get_Bng_to_Eng($str = NULL)
    {
        $engNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, '');
        $bangNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', '');
        $converted = str_replace($bangNumber, $engNumber, $str);
        return $converted;
    }

    public function get_date_diff($date1, $date2)
    {
        $diff = abs($date1 - $date2);

        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        printf("%d years, %d months, %d days\n", $years, $months, $days);
    }

    public function asked_level_global_id($asked_level, $own_global_id)
    {
        return ((pow(2, ($asked_level * 5) + 1) - 1) * (pow(2, (Configure::read('max_level_no') - $asked_level) * 5))) & $own_global_id;
    }

    public function get_current_financial_year()
    {
        $year = TableRegistry::get('financial_years')->find('all', ['conditions' => ['status' => 1]])->first();
        return $year['year'];
    }

    public function get_unit_name($id)
    {
        $info = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['id' => $id]])->first();
        return $info['unit_name'];
    }

    public function get_level_name($level_no)
    {
        $info = TableRegistry::get('administrative_levels')->find('all', ['conditions' => ['level_no' => $level_no]])->first();
        return $info['level_name'];
    }

    public function get_items_by_resource($resource_id)
    {
        $items = TableRegistry::get('transfer_items')->find('all', ['conditions' => ['transfer_resource_id' => $resource_id]])
            ->select(['items.name', 'items.pack_size', 'items.unit', 'transfer_items.item_id', 'transfer_items.quantity'])
            ->innerJoin('items', 'items.id = transfer_items.item_id')
            ->hydrate(false)->toArray();
        return $items;
    }


    public function generate_code($prefix, $type, $padding, $item_id = null, $manufacture_unit_id = null)
    {
        if ($type == 'customer'):
            $lastCode = TableRegistry::get('customers')->find('all', ['conditions' => ['code like' => $prefix . '%'], 'order' => ['id' => 'desc'], 'limit' => 1])->first();
            if (sizeof($lastCode) > 0):
                $arr = explode($prefix, $lastCode['code']);
                $lastSerial = $arr[1];
                $newSerial = intval($lastSerial) + 1;
                echo $prefix . str_pad($newSerial, $padding, 0, STR_PAD_LEFT);
            else:
                $newSerial = 1;
                echo $prefix . str_pad($newSerial, $padding, 0, STR_PAD_LEFT);
            endif;
        else:
            $lastCode = TableRegistry::get('item_units')->find('all', ['conditions' => ['code like' => $prefix . '%', 'item_id' => $item_id, 'manufacture_unit_id' => $manufacture_unit_id], 'order' => ['id' => 'desc'], 'limit' => 1])->first();
            if (sizeof($lastCode) > 0):
                $arr = explode($prefix, $lastCode['code']);
                $lastSerial = $arr[1];
                $newSerial = intval($lastSerial) + 1;
                return $prefix . str_pad($newSerial, $padding, 0, STR_PAD_LEFT);
            else:
                $newSerial = 1;
                return $prefix . str_pad($newSerial, $padding, 0, STR_PAD_LEFT);
            endif;
        endif;
    }

    public function getItemAlias($item_id)
    {
        $user = $this->request->session()->read('Auth.user');
        $item_unit_table = TableRegistry::get('item_units');
        $item_name_for_warehouse = "";
        if (!empty($user['warehouse_id'])) {
            $warehouse_items = TableRegistry::get('WarehouseItems')
                ->find('all')
                ->contain(['Items', 'Warehouses'])
                ->where(['Warehouses.id' => $user['warehouse_id'], 'Items.id' => $item_id, 'Items.status' => 1, 'WarehouseItems.status' => 1])
                ->hydrate(false)
                ->first();
            if ($warehouse_items['use_alias'] == 1) {
                $item_name_for_warehouse = $warehouse_items['item']['alias'];
                return $item_name_for_warehouse;
            } else {
                $item_name_for_warehouse = $warehouse_items['item']['name'];
                return $item_name_for_warehouse;
            }
        } else {
            $warehouse_items = TableRegistry::get('WarehouseItems')
                ->find('all')
                ->contain(['Items', 'Warehouses'])
                ->where(['Items.id' => $item_id, 'Items.status' => 1, 'WarehouseItems.status' => 1])
                ->first();
            $item_name_for_warehouse = $warehouse_items['item']['name'];
            return $item_name_for_warehouse;
        }

    }

    public function get_item_unit_array()
    {

            $user = $this->request->session()->read('Auth.user');
            $warehouse_user = $user['id'];
            $item_unit_table = TableRegistry::get('item_units');
            $result = $item_unit_table->find('all')->contain(['Items', 'Units'])->where([
                'Items.status' => 1,
                'Units.status' => 1,
                'item_units.status' => 1
            ])->hydrate(false);

            $dropArray = [];
            foreach ($result as $key => $value):
                $dropArray[$value['id']] = SystemHelper::getItemAlias($value['item']['id']) . '--' . $value['unit']['unit_display_name'];

            endforeach;

            return $dropArray;
        }


    public function item_offers($item_id)
    {
        $expected = [];
        $offers = TableRegistry::get('productwise_special_offers')->find('all', ['conditions' => ['item_id' => $item_id, 'status !=' => 99]]);

        foreach ($offers as $offer) {
            $offerInfo = TableRegistry::get('special_offers')->find('all', ['conditions' => ['id' => $offer->offer_id, 'program_period_start <=' => time(), 'program_period_end >=' => time(), 'status !=' => 99]])->first();

            if ($offerInfo['offer_detail']) {
                $offerDetail = json_decode($offerInfo['offer_detail'], true);
                foreach ($offerDetail['detailOffer'] as $detailInfo) {
                    if (in_array($item_id, $detailInfo['items'])) {
                        $expected[] = $detailInfo['offer'];
                    }
                }
            }
        }
        return $expected;
    }

    public function slab_computer($amount, $comArray){
        $commission = 0;
        for($i=0; $i<sizeof($comArray); $i++){
            if($amount > $comArray[$i]['end']){
                $commission += ($comArray[$i]['commission']/100)*($comArray[$i]['end']-$comArray[$i]['start']);
            } else {
                $commission += ($comArray[$i]['commission']/100)*($amount - $comArray[$i]['start']);
                break;
            }
        }

        return $commission?$commission:0;
    }
}
