<?php

namespace App\Helper;

class StatusHistoryPotentialUserDefineCode
{
    // type_from 
    const TYPE_FROM_LIKE = 0; // Loại từ yêu thích
    const TYPE_FROM_RESERVATION = 1; // Loại từ giữ chỗ
    const TYPE_FROM_SENT_MESSAGE = 2; // Loại từ gửi tin nhắn
    const TYPE_FROM_CALL = 3; // Loại từ gọi điện

    // status
    const PROGRESSING = 0; // Đang xử lý
    const CANCELED = 1; // Từ chối
    const COMPLETED = 2; // Hoàn tất
    const HIDDEN = 3; // Tạm ẩn
    const CONSULTING = 4; // Đang tư vấn

    static function defineDataStatusHistoryPotentialUser($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang xử lý"],
                "CANCELED" => [1, "CANCELED", "Từ chối"],
                "COMPLETED" => [2, "COMPLETED", "Hoàn tất"],
                "HIDDEN" => [3, "HIDDEN", "Tạm ẩn"],
                "CONSULTING" => [4, "CONSULTING", "Đang tư vấn"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "TYPE_FROM_LIKE", "Đang xử lý"],
                1 => [1, "TYPE_FROM_RESERVATION", "Từ chối"],
                2 => [2, "TYPE_FROM_SENT_MESSAGE", "Hoàn tất"],
                3 => [3, "TYPE_FROM_CALL", "Tạm ẩn"],
                4 => [4, "CONSULTING", "Đang tư vấn"],
            ];
            return $data;
        }
    }
    static function defineDataTypeHistoryPotentialUser($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "TYPE_FROM_LIKE" => [0, "TYPE_FROM_LIKE", "Loại yêu thích"],
                "TYPE_FROM_RESERVATION" => [1, "TYPE_FROM_RESERVATION", "Loại giữ chỗ"],
                "TYPE_FROM_SENT_MESSAGE" => [2, "TYPE_FROM_SENT_MESSAGE", "Loại gửi tin nhắn"],
                "TYPE_FROM_CALL" => [3, "TYPE_FROM_CALL", "Loại gọi điện"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "TYPE_FROM_LIKE", "Loại yêu thích"],
                1 => [1, "TYPE_FROM_RESERVATION", "Loại giữ chỗ"],
                2 => [2, "TYPE_FROM_SENT_MESSAGE", "Loại gửi tin nhắn"],
                3 => [3, "TYPE_FROM_CALL", "Loại gọi điện"],
            ];
            return $data;
        }
    }

    static function getStatusHistoryPotentialUserNum($status, $get_name = false)
    {
        $data = StatusHistoryPotentialUserDefineCode::defineDataStatusHistoryPotentialUser(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusHistoryPotentialUserCode($status, $get_name = false)
    {
        $data = StatusHistoryPotentialUserDefineCode::defineDataStatusHistoryPotentialUser(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
