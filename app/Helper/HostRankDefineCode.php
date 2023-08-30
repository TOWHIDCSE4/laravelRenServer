<?php

namespace App\Helper;

class HostRankDefineCode
{

    const NORMAL = 0; // Mức hạng thông thường, uy tín, víp
    const PRESTIGE = 1; // uy tín
    const VIP = 2; // Mức hạng bạc
    // const SUPPER_VIP = 3; // Siêu vip

    static function defineDataHostRank($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "NORMAL" => [0, "NORMAL", "Thường"],
                "PRESTIGE" => [1, "PRESTIGE", "Uy tín"],
                "VIP" => [2, "VIP", "Vip"],
                // "SUPPER_VIP" => [3, "SUPPER_VIP", "Siêu vip"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "NORMAL", "Thường"],
                1 => [1, "PRESTIGE", "Uy tín"],
                2 => [2, "VIP", "Vip"],
                // 3 => [3, "SUPPER_VIP", "Siêu vip"],
            ];
            return $data;
        }
    }

    static function getHostRankNum($status, $get_name = false)
    {
        $data = HostRankDefineCode::defineDataHostRank(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getHostRankCode($status, $get_name = false)
    {
        $data = HostRankDefineCode::defineDataHostRank(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
