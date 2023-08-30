<?php

namespace App\Helper;


class TypeAction
{
    const NEW_ORDER = "NEW_ORDER"; //Đơn hàng mới
    const NEW_POST = "NEW_POST"; //Bai viet mới
    const SEND_ALL = "SEND_ALL";
    const ORDER_STATUS = "ORDER_STATUS"; //Bai viet mới
    const NEW_MESSAGE = "NEW_MESSAGE"; //Tin nhắn mới
    const CUSTOMER_CANCELLED_ORDER = "CUSTOMER_CANCELLED_ORDER"; //Khách đã hủy đơn hàng
    const CUSTOMER_PAID = "CUSTOMER_PAID"; //Khách đã thanh toán
    const TO_ADMIN = "TO_ADMIN";//gửi đến admin
}