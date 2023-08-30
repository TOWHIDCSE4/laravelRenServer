<?php

namespace App\Helper;

class StatusConfigCommissionDefineCode
{

    const PROGRESSING = 0; // chờ phía admin xét
    const UNCONFIRMED = 1; // admin không xác nhận
    const COMPLETED = 2; // hoàn tất

    static function defineDataHostConfigCommission($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang xử lý"],
                "UNCONFIRMED" => [1, "UNCONFIRMED", "Admin không xác nhận"],
                "COMPLETED" => [2, "COMPLETED", "COMPLETED"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Đang xử lý"],
                1 => [1, "UNCONFIRMED", "Admin không xác nhận"],
                2 => [2, "COMPLETED", "COMPLETED"],
            ];
            return $data;
        }
    }

    static function getConfigCommissionNum($status, $get_name = false)
    {
        $data = StatusConfigCommissionDefineCode::defineDataHostConfigCommission(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getConfigCommissionCode($status, $get_name = false)
    {
        $data = StatusConfigCommissionDefineCode::defineDataHostConfigCommission(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
