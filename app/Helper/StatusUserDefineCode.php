<?php

namespace App\Helper;

class StatusUserDefineCode
{
    // type check user
    const TYPE_REGISTER = 0;
    const TYPE_LOGIN = 1;

    // role
    const USER_IS_NORMAL = 2;
    const USER_IS_HOST = 0;
    const USER_IS_ADMIN = 1;

    // status account
    const NORMAL_ACCOUNT = 2;
    const BANNED_ACCOUNT = 0;

    static function defineDataStatusMotel($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "USER_IS_HOST" => [0, "USER_IS_HOST", "Người thuê chưa có phòng"],
                "USER_IS_ADMIN" => [1, "USER_IS_ADMIN", "Người thuê đã có phòng"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "USER_IS_HOST", "Người thuê chưa có phòng"],
                1 => [1, "USER_IS_ADMIN", "Người thuê đã có phòng"],
            ];
            return $data;
        }
    }

    static function getStatusMotelNum($status, $get_name = false)
    {
        $data = StatusUserDefineCode::defineDataStatusMotel(false);

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
        $data = StatusUserDefineCode::defineDataStatusMotel(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }

    static function defineDataStatusAccount($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "BANNED_ACCOUNT" => [0, "BANNED_ACCOUNT", "Tài khoản đã bị khóa"],
                "NORMAL_ACCOUNT" => [2, "NORMAL_ACCOUNT", "Tài khoản bình thường"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "BANNED_ACCOUNT", "Tài khoản đã bị khóa"],
                2 => [2, "NORMAL_ACCOUNT", "Tài khoản bình thường"],
            ];
            return $data;
        }
    }

    static function getStatusAccountNum($status, $get_name = false)
    {
        $data = StatusUserDefineCode::defineDataStatusAccount(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusAccountCode($status, $get_name = false)
    {
        $data = StatusUserDefineCode::defineDataStatusAccount(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
