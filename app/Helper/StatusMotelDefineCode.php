<?php

namespace App\Helper;

class StatusMotelDefineCode
{

    const MOTEL_EMPTY = 0; // Phòng trống
    const MOTEL_HIRED = 2; // Phòng đã được thuê
    const MOTEL_DRAFT = 3; // Phòng được lưu từ bản nháp

    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "MOTEL_EMPTY" => [0, "MOTEL_EMPTY", "Phòng trống"],
                "MOTEL_HIRED" => [2, "MOTEL_HIRED", "Phòng đã được thuê"],
                "MOTEL_DRAFT" => [3, "MOTEL_DRAFT", "Phòng được lưu từ bản nháp"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "MOTEL_EMPTY", "Phòng trống"],
                2 => [2, "MOTEL_HIRED", "Phòng đã được thuê"],
                3 => [3, "MOTEL_DRAFT", "Phòng được lưu từ bản nháp"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusMotelDefineCode::defineDataStatusMotel(false);

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
        $data = StatusMotelDefineCode::defineDataStatusMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
