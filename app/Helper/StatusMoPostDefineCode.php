<?php

namespace App\Helper;

class StatusMoPostDefineCode
{
    // verified admin
    const VERIFIED_ADMIN = 1;
    const UNVERIFIED_ADMIN = 0;

    // MoPost contracts
    const PROCESSING = 0;
    const CANCEL = 1;
    const COMPLETED = 2;

    // available motel
    const MOTEL_HIRED = 0;
    const MOTEL_AVAILABLE = 1;


    static function defineDataStatusMoPost($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Bài đăng đang chờ duyệt"],
                "CANCEL" => [1, "CANCEL", "Bài đăng đã bị hủy"],
                "COMPLETED" => [2, "COMPLETED", "Bài đăng đang hoạt động"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Bài đăng đang được xử lý"],
                1 => [1, "CANCEL", "Bài đăng đã bị hủy"],
                2 => [2, "COMPLETED", "Bài đăng đang hoạt động"],
            ];
            return $data;
        }
    }

    static function getStatusMoPostNum($status, $get_name = false)
    {
        $data = StatusMoPostDefineCode::defineDataStatusMoPost(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusMoPostCode($status, $get_name = false)
    {
        $data = StatusMoPostDefineCode::defineDataStatusMoPost(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
