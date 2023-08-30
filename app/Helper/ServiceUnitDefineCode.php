<?php

namespace App\Helper;

class ServiceUnitDefineCode
{

    const SERVICE_INDEX = 0;
    const BY_QUANTITY = 1;
    const PER_MOTEL = 2;
    const PER_USE = 3;
    const ANOTHER = 4;
    const NOT_CAL = 5;

    static function defineDataServiceUnit($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "SERVICE_INDEX" => [0, "SERVICE_INDEX", "Theo chỉ số đầu cuối"],
                "BY_QUANTITY" => [1, "BY_QUANTITY", "Theo số người/số lượng"],
                "PER_MOTEL" => [2, "PER_MOTEL", "Theo phòng"],
                "PER_USE" => [3, "PER_USE", "Theo mỗi lần sử dụng"],
                "ANOTHER" => [4, "ANOTHER", "Khác"],
                "NOT_CAL" => [5, "NOT_CAL", "Không tính"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "SERVICE_INDEX", "Theo chỉ số đầu cuối"],
                1 => [1, "BY_QUANTITY", "Theo số người/số lượng"],
                2 => [2, "PER_MOTEL", "Theo phòng"],
                3 => [3, "PER_USE", "Theo mỗi lần sử dụng"],
                4 => [4, "ANOTHER", "Khác"],
                5 => [5, "NOT_CAL", "Không tính"],
            ];
            return $data;
        }
    }

    static function getServiceUnitNum($status, $get_name = false)
    {
        $data = ServiceUnitDefineCode::defineDataServiceUnit(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getServiceUnitCode($status, $get_name = false)
    {
        $data = ServiceUnitDefineCode::defineDataServiceUnit(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
