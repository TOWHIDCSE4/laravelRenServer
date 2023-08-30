<?php

namespace App\Helper;

class StatusContractDefineCode
{
    // user_contracts
    const NOT_REPRESENT = 0;
    const IS_REPRESENT = 1;

    // contracts
    const PROGRESSING = 0; // Người thuê chưa xác nhận
    const TERMINATION = 1; // Chấm dứt hợp đồng - Khi mà hợp đồng đã được thanh toán hoàn tất các loại phí
    const COMPLETED = 2; // Hoàn tất hợp đồng 
    const WAITING_CONFIRM = 3; // chờ chủ nhà xác nhận 
    const UNCONFIRMED_BY_HOST = 4; // chủ nhà không xác nhận hợp đồng


    static function defineDataStatusContract($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Hợp đồng đang được xử lý"],
                "TERMINATION" => [1, "TERMINATION", "Hợp đồng đã kết thúc"],
                "COMPLETED" => [2, "COMPLETED", "Hoàn tất hợp đồng"],
                "WAITING_CONFIRM" => [3, "WAITING_CONFIRM", "Chờ chủ nhà xác nhận"],
                "UNCONFIRMED_BY_HOST" => [4, "UNCONFIRMED_BY_HOST", "Chủ nhà không xác nhận hợp đồng"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Hợp đồng đang được xử lý"],
                1 => [1, "TERMINATION", "Hợp đồng đã kết thúc"],
                2 => [2, "COMPLETED", "Hoàn tất hợp đồng"],
                3 => [3, "WAITING_CONFIRM", "Chờ chủ nhà xác nhận"],
                4 => [4, "UNCONFIRMED_BY_HOST", "Chủ nhà không xác nhận hợp đồng"],
            ];
            return $data;
        }
    }

    static function getStatusContractNum($status, $get_name = false)
    {
        $data = StatusContractDefineCode::defineDataStatusContract(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusContractCode($status, $get_name = false)
    {
        $data = StatusContractDefineCode::defineDataStatusContract(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
