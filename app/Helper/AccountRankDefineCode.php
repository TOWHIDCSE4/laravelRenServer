<?php

namespace App\Helper;

class AccountRankDefineCode
{

    const NORMAL = 0; // Mức hạng thông thường, Thân thiết, víp
    const LOYAL = 1; // Thân thiết
    const VIP = 2; // Vip

    static function defineDataAccountRank($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "NORMAL" => [0, "NORMAL", "Thường"],
                "LOYAL" => [1, "LOYAL", "Thân thiết"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "NORMAL", "Thường"],
                1 => [1, "LOYAL", "Thân thiết"],
            ];
            return $data;
        }
    }

    static function getAccountRankNum($status, $get_name = false)
    {
        $data = AccountRankDefineCode::defineDataAccountRank(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getAccountRankCode($status, $get_name = false)
    {
        $data = AccountRankDefineCode::defineDataAccountRank(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
