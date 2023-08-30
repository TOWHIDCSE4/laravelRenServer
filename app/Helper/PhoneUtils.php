<?php

namespace App\Helper;


class PhoneUtils
{

    static function isNumberPhoneValid($numPhone)
    {
        $lenNumPhone = strlen($numPhone);
        if ($lenNumPhone > 11 || $lenNumPhone < 10) return false;
        if (!is_numeric($numPhone)) return false;
        return  true;
    }

    static function convert($phonenumber, $isAreaCode = false, $areaCode = '84') //chuẩn hóa
    {
        if (!empty($phonenumber)) {
            //1. Xóa ký tự trắng
            $phonenumber = str_replace(' ', '', $phonenumber);
            //2. Xóa các dấu chấm phân cách
            $phonenumber = str_replace('.', '', $phonenumber);
            //3. Xóa các dấu gạch nối phân cách
            $phonenumber = str_replace('-', '', $phonenumber);
            //4. Xóa dấu mở ngoặc đơn
            $phonenumber = str_replace('(', '', $phonenumber);
            //5. Xóa dấu đóng ngoặc đơn
            $phonenumber = str_replace(')', '', $phonenumber);
            //6. Xóa dấu +
            $phonenumber = str_replace('+', '', $phonenumber);
            //7. Chuyển đầu số
            if ($isAreaCode) {
                if (substr($phonenumber, 0, 2) != $areaCode && substr($phonenumber, 0, 1) == '0') {
                    $phonenumber = $areaCode . substr($phonenumber, 1);
                }
            } else {
                if (substr($phonenumber, 0, 2) == $areaCode) {
                    $phonenumber = '0' . substr($phonenumber, 2, strlen($phonenumber) - 2);
                }
                if (substr($phonenumber, 0, 1) != '0') {
                    $phonenumber = '0' . $phonenumber;
                }
            }



            return $phonenumber;
        } else {
            return null;
        }
    }
}
