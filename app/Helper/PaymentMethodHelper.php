<?php

namespace App\Helper;


class PaymentMethodHelper
{
    static function handleFile()
    {
        $methods = config('saha.payment_method.payment_method');
        return $methods;
    }

    static function getNamePaymentMethod($id)
    {

        if (RevenueExpenditureUtils::PAYMENT_TYPE_CASH == $id) {
            return "Tiền mặt";
        }
        if (RevenueExpenditureUtils::PAYMENT_TYPE_SWIPE == $id) {
            return "Quẹt thẻ";
        }
        if (RevenueExpenditureUtils::PAYMENT_TYPE_COD == $id) {
            return "Thanh toán khi nhận hàng";
        }
        if (RevenueExpenditureUtils::PAYMENT_TYPE_TRANSFER == $id) {
            return "Chuyển khoản";
        }

        $methods = PaymentMethodHelper::handleFile();
        foreach ($methods as $method) {
            if ($method['id'] == $id) {
                return $method['name'];
            }
        }
        return null;
    }

    static function getNamePaymentPartner($id)
    {

        $methods = PaymentMethodHelper::handleFile();
        foreach ($methods as $method) {
            if ($method['id'] == $id) {
                return $method['name'];
            }
        }
        return null;
    }
}