<?php

namespace App\Helper;

class DefineFolderSaveFile
{

    const MO_POST_FILES_FOLDER = "MO_POST_FILES_FOLDER";
    const MOTEL_FILES_FOLDER = "MOTEL_FILES_FOLDER";
    const CONTRACT_FILES_FOLDER = "CONTRACT_FILES_FOLDER";
    const BILL_FILES_FOLDER = "BILL_FILES_FOLDER";
    const RENTER_FILES_FOLDER = "RENTER_FILES_FOLDER";
    const PROBLEM_FILES_FOLDER = "PROBLEM_FILES_FOLDER";
    const ANOTHER_FILES_FOLDER  = "ANOTHER_FILES_FOLDER";

    const listFolder = [
        "MO_POST_FILES_FOLDER",
        "MOTEL_FILES_FOLDER",
        "CONTRACT_FILES_FOLDER",
        "BILL_FILES_FOLDER",
        "RENTER_FILES_FOLDER",
        "PROBLEM_FILES_FOLDER",
        "ANOTHER_FILES_FOLDER",
    ];

    static public function checkContainFolder($nameFolder = null)
    {
        if (in_array($nameFolder, DefineFolderSaveFile::listFolder)) {
            return $nameFolder;
        } else {
            return null;
        }
    }


    // static function defineDataStatusMotel($input_is_num = false)
    // {
    //     if ($input_is_num == false) {
    //         $data = [
    //             "MOTEL_FILES_FOLDER" => [0, "MOTEL_FILES_FOLDER", "Người thuê chưa có phòng"],
    //             "CONTRACT_FILES_FOLDER" => [1, "CONTRACT_FILES_FOLDER", "Người thuê đã có phòng"],
    //         ];
    //         return $data;
    //     } else {
    //         $data = [
    //             0 => [0, "MOTEL_FILES_FOLDER", "Người thuê chưa có phòng"],
    //             1 => [1, "CONTRACT_FILES_FOLDER", "Người thuê đã có phòng"],
    //         ];
    //         return $data;
    //     }
    // }

    // static function getStatusMotelNum($status, $get_name = false)
    // {
    //     $data = StatusUserDefineCode::defineDataStatusMotel(false);

    //     if (isset($data[$status])) {
    //         if ($get_name == true) {
    //             return $data[$status][2];
    //         }

    //         return $data[$status][0];
    //     }
    //     return null;
    // }

    // static function getStatusMotelCode($status, $get_name = false)
    // {
    //     $data = StatusUserDefineCode::defineDataStatusMotel(true);

    //     if (isset($data[$status])) {
    //         if ($get_name == true) {
    //             return $data[$status][2];
    //         }

    //         return $data[$status][1];
    //     }
    //     return null;
    // }
}
