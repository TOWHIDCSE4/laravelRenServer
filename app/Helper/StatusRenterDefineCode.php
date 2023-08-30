<?php

namespace App\Helper;

class StatusRenterDefineCode
{

    const RENTER_HAS_NOT_MOTEL = 0; // Người thuê chưa có phòng
    const RENTER_HAS_RENTED_MOTEL = 2; // Người thuê đã có phòng

    //type_from potential
    const FROM_POTENTIAL = 0; // Người thuê đã có phòng


    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "RENTER_HAS_NOT_MOTEL" => [0, "RENTER_HAS_NOT_MOTEL", "Người thuê chưa có phòng"],
                "RENTER_HAS_RENTED_MOTEL" => [2, "RENTER_HAS_RENTED_MOTEL", "Người thuê đã có phòng"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "RENTER_HAS_NOT_MOTEL", "Người thuê chưa có phòng"],
                2 => [2, "RENTER_HAS_RENTED_MOTEL", "Người thuê đã có phòng"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusRenterDefineCode::defineDataStatusMotel(false);

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
        $data = StatusRenterDefineCode::defineDataStatusMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
