<?php
namespace App\View\Helper;

use App\Model\Table\AdministrativeUnitsTable;
use App\Model\Table\ItemUnitsTable;
use Cake\Controller\Component\AuthComponent;
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

    public function convert_number_to_words($number) {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
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

    public function get_item_name($id)
    {
        $info = TableRegistry::get('items')->find('all', ['conditions' => ['id' => $id]])->first();
        return $info['name'];
    }

    public function get_manufacture_unit_name($id)
    {
        $info = TableRegistry::get('units')->find('all', ['conditions' => ['id' => $id]])->first();
        return $info['unit_display_name'];
    }

    public function get_customer_detail($id)
    {
        $customer = TableRegistry::get('customers')->find('all', ['conditions' => ['id' => $id]])->first();
        return $customer;
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

    public function item_array($warehouse_id = null)
    {
        $items = TableRegistry::get('items')->find('all', ['conditions' => ['status' => 1]]);
        $item_arr = [];
        foreach ($items as $item):
            $item_arr[$item['id']] = SystemHelper::getItemAlias($item['id'], $warehouse_id);
        endforeach;
        return $item_arr;
    }

    public function getItemAlias($item_id, $warehouse_id = null)
    {
        $user = $this->request->session()->read('Auth.User');
        $item_unit_table = TableRegistry::get('item_units');
        $item_name_for_warehouse = "";
        if ($warehouse_id) {
            $warehouse_items = TableRegistry::get('WarehouseItems')
                ->find('all')
                ->contain(['Items', 'Warehouses'])
                ->where(['Warehouses.id' => $warehouse_id, 'Items.id' => $item_id, 'Items.status' => 1, 'WarehouseItems.status' => 1])
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
            $items = TableRegistry::get('Items')
                ->find('all')
                ->where(['id' => $item_id, 'status' => 1])
                ->first();
            $item_name_for_warehouse = $items['name'];
            return $item_name_for_warehouse;
        }

    }

    public function get_item_unit_array($warehouse_id = null)
    {
        $user = $this->request->session()->read('Auth.User');
        $item_unit_table = TableRegistry::get('item_units');
        $result = $item_unit_table->find('all')->contain(['Items', 'Units'])->where([
            'Items.status' => 1,
            'Units.status' => 1,
            'item_units.status' => 1
        ])->hydrate(false);

        $dropArray = [];
        foreach ($result as $key => $value):
            $dropArray[$value['id']] = SystemHelper::getItemAlias($value['item']['id'], $warehouse_id) . '--' . $value['unit']['unit_display_name'];

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

    public function generate_invoice_no($prefix_level, $invoice_date, $location_global_id){
        $prefix_level_global_id = self::asked_level_global_id($prefix_level, $location_global_id);
        $prefix_level_info = TableRegistry::get('administrative_units')->find('all', ['conditions' => ['global_id' => $prefix_level_global_id]])->first();

        $prefix = $prefix_level_info['prefix'];
        $year = date('y', $invoice_date);
        $month = date('m', $invoice_date);


        $serials = TableRegistry::get('serials')->find('all',[
            'conditions'=>[
                'month'=>date('m', $invoice_date),
                'year'=>date('Y', $invoice_date),
                'trigger_type'=>array_flip(Configure::read('serial_trigger_types'))['others'],
                'trigger_id'=>$prefix_level_info['id'],
                'serial_for'=>array_flip(Configure::read('serial_types'))['invoice'],
            ],
            'order'=>['id'=>'desc'],
            'limit'=>1
        ]);

        if($serials->toArray()){
            $serialData = $serials->toArray();
            $serialTable = TableRegistry::get('serials');
            $query = $serialTable->query();
            $query->update()->set(['serial_no' => $serialData[0]['serial_no']+1])->where(['id' => $serialData[0]['id']])->execute();

            $serial_no = $serialData[0]['serial_no'];
            $newSerial = str_pad($serial_no+1, 6, 0, STR_PAD_LEFT);
            return $prefix.$year.$month.$newSerial;
        }else{
            $user = $this->request->session()->read('Auth.User');
            $serialsTable = TableRegistry::get('serials');
            $serial = $serialsTable->newEntity();

            $serial->trigger_type = array_flip(Configure::read('serial_trigger_types'))['others'];
            $serial->trigger_id = $prefix_level_info['id'];
            $serial->serial_for = array_flip(Configure::read('serial_types'))['invoice'];
            $serial->year = date('Y', $invoice_date);
            $serial->month = date('m', $invoice_date);
            $serial->serial_no = 1;
            $serial->created_by = $user['id'];
            $serial->created_date = time();

            if ($serialsTable->save($serial)) {
                $serial_no = $serial->serial_no;
                $newSerial = str_pad($serial_no, 6, 0, STR_PAD_LEFT);
                return $prefix.$year.$month.$newSerial;
            }
        }
    }

    public function get_item_array($warehouse_id = null)
    {
        $user = $this->request->session()->read('Auth.User');
        $item_unit_table = TableRegistry::get('items');
        $result = $item_unit_table->find('all')->where([
            'Items.status' => 1
        ])->hydrate(false);

        $dropArray = [];
        foreach ($result as $key => $value):
            $dropArray[$value['id']] = SystemHelper::getItemAlias($value['id'], $warehouse_id);
        endforeach;
        return $dropArray;
    }

    public function get_unit_quantity($unit_type, $unit_size, $quantity, $converted_quantity)
    {
        if ($unit_size == 0) {
            if ($unit_type == 1 || $unit_type == 3) {
                $value = $quantity / 1000;
            } else {
                $value = $quantity;
            }
        } else {
            if ($unit_type == 1 || $unit_type == 3) {
                $value = $converted_quantity * $quantity / 1000;
            } else {
                $value = $converted_quantity * $quantity;
            }
        }
        return $value;
    }
}
