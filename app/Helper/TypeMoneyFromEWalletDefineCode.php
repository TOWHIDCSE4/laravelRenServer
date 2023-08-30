<?php

namespace App\Helper;

class TypeMoneyFromEWalletDefineCode
{
    const TAKE_OUT_MONEY = 0; // Lấy or sử dụng tiền ở ví
    const USER_REFERRED = 0;
    const USER_REFERRAL = 1;
    // const DECREASE_BALANCE = 2;

    static function defineDataTypeMoneyFromEWallet($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "USER_REFERRED" => [0, "USER_REFERRED", "Người được giới thiệu"],
                "USER_REFERRAL" => [1, "USER_REFERRAL", "Người giới thiệu"],
                // "DECREASE_BALANCE" => [2, "DECREASE_BALANCE", "Giảm số dư"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "USER_REFERRED", "Người được giới thiệu"],
                1 => [1, "USER_REFERRAL", "Người giới thiệu"],
                // 2 => [2, "DECREASE_BALANCE", "Giảm số dư"],
            ];
            return $data;
        }
    }

    static function getTypeMoneyFromEWalletNum($status, $get_name = false)
    {
        $data = TypeMoneyFromEWalletDefineCode::defineDataTypeMoneyFromEWallet(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getTypeMoneyFromEWalletCode($status, $get_name = false)
    {
        $data = TypeMoneyFromEWalletDefineCode::defineDataTypeMoneyFromEWallet(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
