<?php

namespace App\Helper;

class StatusReportProblemDefineCode
{
    // status report
    const PROGRESSING = 0;
    const COMPLETED = 2;

    // severity
    const LOW = 2;
    const NORMAL = 1;
    const HIGH = 0;

    static function defineDataStatusReport($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Đang được xử lý"],
                "COMPLETED" => [2, "COMPLETED", "Đã hoàn tất"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Đang được xử lý"],
                2 => [2, "COMPLETED", "Đã hoàn tất"],
            ];
            return $data;
        }
    }

    static function getStatusReportNum($status, $get_name = false)
    {
        $data = StatusReportProblemDefineCode::defineDataStatusReport(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusReportCode($status, $get_name = false)
    {
        $data = StatusReportProblemDefineCode::defineDataStatusReport(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }

    static function defineDataSeverityReport($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "HIGH" => [0, "HIGH", "Mức độ cao"],
                "NORMAL" => [1, "NORMAL", "Mức độ trung bình"],
                "LOW" => [2, "LOW", "Mức độ thấp"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "HIGH", "Mức độ cao"],
                1 => [1, "NORMAL", "Mức độ trung bình"],
                2 => [2, "LOW", "Mức độ thấp"],
            ];
            return $data;
        }
    }

    static function getStatusSeverityNum($status, $get_name = false)
    {
        $data = StatusReportProblemDefineCode::defineDataSeverityReport(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusSeverityCode($status, $get_name = false)
    {
        $data = StatusReportProblemDefineCode::defineDataSeverityReport(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
