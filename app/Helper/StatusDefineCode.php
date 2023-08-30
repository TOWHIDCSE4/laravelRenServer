<?php

namespace App\Helper;

class StatusDefineCode
{

    const WAITING_FOR_PROGRESSING = 0;
    const PACKING = 1;
    const OUT_OF_STOCK = 2;
    const USER_CANCELLED = 3;
    const CUSTOMER_CANCELLED = 4;
    const SHIPPING = 5;
    const DELIVERY_ERROR = 6;
    const CUSTOMER_RETURNING = 7;
    const CUSTOMER_HAS_RETURNS = 8;
    const WAIT_FOR_PAYMENT = 9;
    const COMPLETED = 10;

    const UNPAID = 0;
    const WAITING_FOR_PROGRESSING_PAYMENT = 1;
    const PAID = 2;
    const PARTIALLY_PAID = 3;
    const REFUNDS = 5;

    static function defineDataOrder($input_is_num = false)
    {

        if ($input_is_num == false) {
            $data = [
                "WAITING_FOR_PROGRESSING" => [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"],
                "PACKING" => [1, "PACKING", "Đang chuẩn bị hàng"],
                "OUT_OF_STOCK" => [2, "OUT_OF_STOCK", "Hết hàng"],
                "USER_CANCELLED" => [3, "USER_CANCELLED", "Shop huỷ"],
                "CUSTOMER_CANCELLED" => [4, "CUSTOMER_CANCELLED", "Khách đã hủy"],
                "SHIPPING" => [5, "SHIPPING", "Đang giao hàng"],
                "DELIVERY_ERROR" => [6, "DELIVERY_ERROR", "Lỗi giao hàng"],
                "CUSTOMER_RETURNING" => [7, "CUSTOMER_RETURNING", "Chờ trả hàng"],
                "CUSTOMER_HAS_RETURNS" => [8, "CUSTOMER_HAS_RETURNS", "Đã trả hàng"],
                "WAIT_FOR_PAYMENT" => [9, "WAIT_FOR_PAYMENT", "Đợi thanh toán"],
                "COMPLETED" => [10, "COMPLETED", "Đã hoàn thành"],
            ];
            return $data;
        } else {
            $data = [
                0 => [0, "WAITING_FOR_PROGRESSING", "Chờ xử lý"],
                1 => [1, "PACKING", "Đang chuẩn bị hàng"],
                2 => [2, "OUT_OF_STOCK", "Hết hàng"],
                3 => [3, "USER_CANCELLED", "Shop huỷ"],
                4 => [4, "CUSTOMER_CANCELLED", "Khách đã hủy"],
                5 => [5, "SHIPPING", "Đang giao hàng"],
                6 => [6, "DELIVERY_ERROR", "Lỗi giao hàng"],
                7 => [7, "CUSTOMER_RETURNING", "Chờ trả hàng"],
                8 => [8, "CUSTOMER_HAS_RETURNS", "Đã trả hàng"],
                9 => [9, "WAIT_FOR_PAYMENT", "Đợi thanh toán"],
                10 => [10, "COMPLETED", "Đã hoàn thành"],
            ];
            return $data;
        }
    }

    static function getOrderStatusNum($status, $get_name = false)
    {
        $data = StatusDefineCode::defineDataOrder(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getOrderStatusCode($status, $get_name = false)
    {
        $data = StatusDefineCode::defineDataOrder(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }


    static function defineDataPayment($input_is_num = false)
    {

        if ($input_is_num == false) {
            $data = [
                "UNPAID" => [0, "UNPAID", "Chưa thanh toán"],
                "WAITING_FOR_PROGRESSING" => [1, "WAITING_FOR_PROGRESSING", "Chờ xử lý"],
                "PAID" => [2, "PAID", "Đã thanh toán"],
                "PARTIALLY_PAID" => [3, "PARTIALLY_PAID", "Đã thanh toán một phần"],
                "CUSTOMER_CANCELLED" => [4, "CUSTOMER_CANCELLED", "Khách đã hủy"],
                "REFUNDS" => [5, "REFUNDS", "Đã hoàn tiền"],
            ];
            return $data;
        } else {
            $data = [
                StatusDefineCode::UNPAID => [0, "UNPAID", "Chưa thanh toán"],
                StatusDefineCode::WAITING_FOR_PROGRESSING_PAYMENT => [1, "WAITING_FOR_PROGRESSING", "Chờ xử lý"],
                StatusDefineCode::PAID => [2, "PAID", "Đã thanh toán"],
                StatusDefineCode::PARTIALLY_PAID => [3, "PARTIALLY_PAID", "Đã thanh toán một phần"],
                StatusDefineCode::CUSTOMER_CANCELLED => [4, "CUSTOMER_CANCELLED", "Khách đã hủy"],
                StatusDefineCode::REFUNDS => [5, "REFUNDS", "Đã hoàn tiền"],
            ];
            return $data;
        }
    }

    static function getPaymentStatusNum($status, $get_name = false)
    {
        $data = StatusDefineCode::defineDataPayment(false);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][0];
        }
        return null;
    }

    static function getPaymentStatusCode($status, $get_name = false)
    {
        $data = StatusDefineCode::defineDataPayment(true);

        if (isset($data[$status])) {
            if ($get_name == true) {
                return $data[$status][2];
            }

            return $data[$status][1];
        }
        return null;
    }
}
