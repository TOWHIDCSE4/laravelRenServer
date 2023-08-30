<?php

namespace App\Helper;

class StatusTowerDefineCode
{

    const TOWER_EMPTY = 0; // Phòng trống
    const TOWER_HIRED = 1; // Phòng đã được thuê

    static function defineDataStatusTower($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "TOWER_EMPTY" => [0, "TOWER_EMPTY", "Phòng trống"],
                "TOWER_HIRED" => [1, "TOWER_HIRED", "Phòng đã được thuê"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "TOWER_EMPTY", "Phòng trống"],
                1 => [1, "TOWER_HIRED", "Phòng đã được thuê"],
            ];
            return $data;
        }
    }

    static function getStatusTowerNum($status, $get_name = false)
    {
        $data = StatusTowerDefineCode::defineDataStatusTower(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusTowerCode($status, $get_name = false)
    {
        $data = StatusTowerDefineCode::defineDataStatusTower(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
