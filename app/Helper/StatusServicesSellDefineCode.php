<?php

namespace App\Helper;

class StatusServicesSellDefineCode
{

    const PROGRESSING = 0; // Đang xử lý
    const HIDE = 2; // Đã ẩn

    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang xử lý"],
                "HIDE" => [2, "HIDE", "Đã ẩn"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Đang xử lý"],
                2 => [2, "HIDE", "Đã ẩn"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusServicesSellDefineCode::defineDataStatusMotel(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusMotelCode($status, $get_name = false)
    {
        $data = StatusServicesSellDefineCode::defineDataStatusMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
