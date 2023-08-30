<?php

namespace App\Helper;

class StatusCollaboratorReferMotelDefineCode
{

    const PROGRESSING = 0; // Đang xử lý
    const CANCEL = 1; //
    const COMPLETED = 2; // Hoàn tất
    const WAIT_CONFIRM = 3; // chờ admin xác nhận

    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang xử lý"],
                "CANCEL" => [1, "CANCEL", "Hủy"],
                "COMPLETED" => [2, "COMPLETED", "Hoàn tất"],
                "WAIT_CONFIRM" => [3, "WAIT_CONFIRM", "Chờ admin xác nhận"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Đang xử lý"],
                1 => [1, "CANCEL", "Hủy"],
                2 => [2, "WAIT_CONFIRM", "Chờ admin xác nhận"],
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
