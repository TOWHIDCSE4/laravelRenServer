<?php

namespace App\Helper;

class StatusStatisticAdminDefineCode
{

    const USER_CLICK_CALL_TO_HOST = 0; // Thống kê khách ấn vào nút gọi cho chủ nhà
    const HIDE = 2; // Đã ẩn

    static function defineDataStatusStatisticAdmin($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "USER_CLICK_CALL_TO_HOST" => [0, "USER_CLICK_CALL_TO_HOST", "Thống kê khách ấn vào nút gọi cho chủ nhà"],
                "HIDE" => [2, "HIDE", "Đã ẩn"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "USER_CLICK_CALL_TO_HOST", "Thống kê khách ấn vào nút gọi cho chủ nhà"],
                2 => [2, "HIDE", "Đã ẩn"],
            ];
            return $data;
        }
    }

    static function getStatusStatisticAdminNum($status, $get_name = false)
    {
        $data = StatusStatisticAdminDefineCode::defineDataStatusStatisticAdmin(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusStatisticAdminCode($status, $get_name = false)
    {
        $data = StatusStatisticAdminDefineCode::defineDataStatusStatisticAdmin(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
