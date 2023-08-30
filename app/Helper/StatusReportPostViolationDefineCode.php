<?php

namespace App\Helper;

class StatusReportPostViolationDefineCode
{

    const PROGRESSING = 0; // Chưa xử lý
    const COMPLETED = 2; // Đã xử lý

    static function defineDataStatusReportPostViolation($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Chưa xử lý"],
                "COMPLETED" => [2, "COMPLETED", "Đã xử lý"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Chưa xử lý"],
                2 => [2, "COMPLETED", "Đã xử lý"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusReportPostViolationDefineCode::defineDataStatusReportPostViolation(false);

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
        $data = StatusReportPostViolationDefineCode::defineDataStatusReportPostViolation(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
