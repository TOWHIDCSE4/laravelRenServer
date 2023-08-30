<?php

namespace App\Helper;

class StatusWithdrawalDefineCode
{

    const PROGRESSING = 0; // Chờ xử lý
    const CANCEL = 1;
    const APPROVED = 2; // Được chấp thuận

    static function defineDataStatusWithdrawal($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Chờ xử lý"],
                "CANCELED" => [1, "CANCELED", "Đã bị hủy"],
                "APPROVED" => [2, "APPROVED", "Được chấp thuận"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Chờ xử lý"],
                1 => [1, "CANCELED", "Đã bị hủy"],
                2 => [2, "APPROVED", "Được chấp thuận"],
            ];
            return $data;
        }
    }

    static function getStatusWithdrawalNum($status, $get_name = false)
    {
        $data = StatusWithdrawalDefineCode::defineDataStatusWithdrawal(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusWithdrawalCode($status, $get_name = false)
    {
        $data = StatusWithdrawalDefineCode::defineDataStatusWithdrawal(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
