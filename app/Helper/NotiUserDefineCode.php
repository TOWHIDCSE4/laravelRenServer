<?php

namespace App\Helper;

class NotiUserDefineCode
{

    const USER_NORMAL = 0;
    const USER_IS_HOST = 1;
    const USER_IS_ADMIN = 2;
    const ALL_USER_IN_SYSTEM = 3;

    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "USER_NORMAL" => [0, "USER_NORMAL", "Người dùng thường"],
                "USER_IS_HOST" => [1, "USER_IS_HOST", "Người dùng là chủ nhà"],
                "USER_IS_ADMIN" => [2, "USER_IS_ADMIN", "Người dùng là admin"],
                "ALL_USER_IN_SYSTEM" => [3, "ALL_USER_IN_SYSTEM", "Toàn bộ người dùng trong hệ thống"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "USER_NORMAL", "Người dùng thường"],
                1 => [1, "USER_IS_HOST", "Người dùng là chủ nhà"],
                2 => [2, "USER_IS_ADMIN", "Người dùng là admin"],
                3 => [3, "ALL_USER_IN_SYSTEM", "Toàn bộ người dùng trong hệ thống"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = NotiUserDefineCode::defineDataStatusMotel(false);

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
        $data = NotiUserDefineCode::defineDataStatusMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
