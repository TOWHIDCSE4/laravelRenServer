<?php

namespace App\Helper;

class StatusOrderServicesSellDefineCode
{

    const PROGRESSING = 0; // Đang xử lý
    const CANCEL_ORDER = 1; // Hủy đơn hàng
    const COMPLETED = 2; // Đã hoàn thành
    const SHIPPING = 3; // Đang giao

    static function defineDataStatusOrder($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang xử lý"],
                "CANCEL_ORDER" => [1, "CANCEL_ORDER", "Hủy đơn hàng"],
                "COMPLETED" => [2, "COMPLETED", "Đã hoàn thành"],
                "SHIPPING" => [3, "SHIPPING", "Đang giao"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Đang xử lý"],
                1 => [1, "CANCEL_ORDER", "Hủy đơn hàng"],
                2 => [2, "COMPLETED", "Đã hoàn thành"],
                3 => [3, "SHIPPING", "Đang giao"],
            ];
            return $data;
        }
    }

    static function getStatusOrderNum($status, $get_name = false)
    {
        $data = StatusOrderServicesSellDefineCode::defineDataStatusOrder(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusOrderCode($status, $get_name = false)
    {
        $data = StatusOrderServicesSellDefineCode::defineDataStatusOrder(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
