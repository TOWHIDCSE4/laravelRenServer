<?php

namespace App\Helper;

use App\Models\MsgCode;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Str;

class Helper
{

    static function removeItemArrayIfNullValue(array $array): array
    {

        $newArray = $array;

        foreach ($newArray as $key => $value) {

            if ($value === null) {
                unset($newArray[$key]);
            }
        }

        return $newArray;
    }

    static function validateDate($date, $format = 'Y-m')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    static function createAndValidateFormatDate($date, $format = 'Y-m', $zone = 'Asia/Ho_Chi_Minh')
    {
        try {
            return DateTime::createFromFormat($format, $date, new DateTimeZone($zone));
        } catch (\Throwable $t) {
            return false;
        }
    }

    static function parseAndValidateDateTime($date)
    {
        try {
            return Carbon::parse($date);
        } catch (\Throwable $t) {
            return false;
        }
    }

    static function checkAddress($province = null, $district = null, $wards = null)
    {
        if (Place::getNameProvince($province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (Place::getNameDistrict($district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (Place::getNameWards($wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }

        return true;
    }


    static function getTimeNowDateTime()
    {
        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt =  new DateTime($dt->toDateTimeString());
        return $dt;
    }

    static function getTimeNowDateTimeOptional($day = true, $month = true, $year = true)
    {
        $dt = Carbon::now('Asia/Ho_Chi_Minh');

        if ($day && $month && $year) {
            $dt = $dt->format('Y-m-d');
        } else if ($month && $year) {
            $dt = $dt->format('Y-m');
        } else if ($year) {
            $dt = $dt->format('Y');
        }
        return $dt;
    }

    static function getTimeNowString()
    {
        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt = $dt->toDateTimeString();
        return $dt;
    }

    static function getRandomOrderString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = $dt1 . $dt2 . Helper::generateRandomString(8);
        return $order_code;
    }

    static function getRandomRevenueExpenditureString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "TC" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }


    static function getRandomTallySheetString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "K" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static function getRandomImportStockString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "N" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static function getRandomTransferStockString()
    {

        $dt = Carbon::now('Asia/Ho_Chi_Minh');
        $dt1 = $dt->format('dm');
        $dt2 = substr($dt->format('Y'), 2, 3);

        $order_code = "C" . $dt1 . $dt2 . Helper::generateRandomString(6);
        return $order_code;
    }

    static public function generateRandomString($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    static public function generateRandomNum($length = 6)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static public function validEmail($str)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    static public function checkContainSpecialCharacter($str)
    {
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $str)) {
            return false;
        }
        return true;
    }

    static public function day_php_to_standard($day)
    {
        $day = (int)$day;
        if ($day == 0) return 8;
        if ($day == 1) return 2;
        if ($day == 2) return 3;
        if ($day == 3) return 4;
        if ($day == 4) return 5;
        if ($day == 5) return 6;
        if ($day == 6) return 7;
        return 8;
    }

    static function currency_money_format($number, $suffix = 'đ')
    {
        if (!empty($number)) {
            return number_format($number, 0, ',', '.') . "{$suffix}";
        }
    }

    static function generateTransactionID()
    {
        // $prefix = 'TXN'; // Customize this to your organization/application
        // $prefixLength = 2;
        // $uniqueStringLength = 5;

        // // Generate a random unique string (alphanumeric, uppercase)
        // $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $prefixCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $prefix = Str::random($prefixLength, $prefixCharacters);
        // $prefix = Str::random($prefixLength, $characters);
        // $uniqueString = Str::random($uniqueStringLength, $characters);

        // // Get the current timestamp (Unix timestamp)
        // $timestamp = time();

        // // Combine the prefix, timestamp, and unique string to form the transaction ID
        // $transactionID = strtoupper($prefix) . $timestamp . strtoupper($uniqueString);

        // return $transactionID;

        $prefixLength = 2;
        $uniqueStringLength = 5;

        // Generate a random unique string (alphanumeric, uppercase)
        $prefixCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $prefix = Str::random($prefixLength, $prefixCharacters);
        $uniqueString = Str::random($uniqueStringLength, $characters);

        // Get the current timestamp (Unix timestamp)
        $timestamp = time();

        // Combine the prefix, timestamp, and unique string to form the transaction ID
        $transactionID = strtoupper($prefix) . $timestamp . strtoupper($uniqueString);
        
        return $transactionID;
    }
}
