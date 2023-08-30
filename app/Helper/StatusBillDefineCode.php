<?php

namespace App\Helper;

class StatusBillDefineCode
{
    //status bill
    const PROGRESSING = 0; // trạng thái hóa đơn, chờ người thuê
    const WAIT_FOR_CONFIRM = 1; // người dùng đã thanh toán chờ chủ nhà xác nhận
    const COMPLETED = 2; // Hoàn tất hóa đơn - Khi mà hóa đơn đã được thanh toán, đc chủ nhà xác nhận thanh toán
    const CANCEL_BY_RENTER = 3; // hóa đơn bị hủy bởi người thuê 
    const CANCEL_BY_HOST = 4; // hóa đơn bị hủy bởi chủ nhà

    //type bill
    const BILL_BY_MONTH = 0;
    const ANOTHER_BILL = 1;

    //bill init
    const NOT_INIT_BILL = 0;
    const INIT_BILL = 1;

    static function defineDataStatusBill($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "PROGRESSING" => [0, "PROGRESSING", "Chờ thanh toán"],
                "WAIT_FOR_CONFIRM" => [1, "WAIT_FOR_CONFIRM", "Chờ xác nhận"],
                "COMPLETED" => [2, "COMPLETED", "Đã thanh toán"],
                "CANCEL_BY_RENTER" => [3, "CANCEL_BY_RENTER", "Hủy bởi người thuê"],
                "CANCEL_BY_HOST" => [4, "CANCEL_BY_HOST", "Hủy bởi chủ nhà"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "PROGRESSING", "Chờ thanh toán"],
                1 => [1, "WAIT_FOR_CONFIRM", "Chờ xác nhận"],
                2 => [2, "COMPLETED", "Đã thanh toán"],
                3 => [3, "CANCEL_BY_RENTER", "Hủy bởi người thuê"],
                4 => [4, "CANCEL_BY_HOST", "Hủy bởi chủ nhà"],
            ];
            return $data;
        }
    }

    static function getStatusBillNum($status, $get_name = false)
    {
        $data = StatusBillDefineCode::defineDataStatusBill(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getStatusBillCode($status, $get_name = false)
    {
        $data = StatusBillDefineCode::defineDataStatusBill(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }

    static function defineDataTypeBill($input_is_num = false)
    {
        if ($input_is_num == false) {
            $data = [
                "BILL_BY_MONTH" => [0, "BILL_BY_MONTH", "Hóa đơn theo tháng"],
                "ANOTHER_BILL" => [1, "ANOTHER_BILL", "Hóa đơn khác"]
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "BILL_BY_MONTH", "Hóa đơn theo tháng"],
                1 => [1, "ANOTHER_BILL", "Hóa đơn khác"],
            ];
            return $data;
        }
    }

    static function getTypeBillNum($status, $get_name = false)
    {
        $data = StatusBillDefineCode::defineDataStatusBill(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getTypeBillCode($status, $get_name = false)
    {
        $data = StatusBillDefineCode::defineDataStatusBill(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
