<?php

namespace App\Helper;

class StatusFindFastMotelDefineCode
{

    const NOT_CONSULT = 0; // Chưa được tư vấn
    const CONSULTED = 2; // Đã được tư vấn

    static function defineDataStatusFindFastMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "NOT_CONSULT" => [0, "NOT_CONSULT", "Chưa được tư vấn"],
                "CONSULTED" => [2, "CONSULTED", "Đã được tư vấn"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "NOT_CONSULT", "Chưa được tư vấn"],
                2 => [2, "CONSULTED", "Đã được tư vấn"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusFindFastMotelDefineCode::defineDataStatusFindFastMotel(false);

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
        $data = StatusFindFastMotelDefineCode::defineDataStatusFindFastMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
