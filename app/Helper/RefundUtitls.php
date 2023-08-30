<?php

namespace App\Helper;

use App\Services\BalanceCustomerService;

class RefundUtitls
{


    static function auto_refund_money_for_ctv($orderExists, $request)
    {
        if (
            ($orderExists->order_status ==  StatusDefineCode::CUSTOMER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::OUT_OF_STOCK ||
                $orderExists->order_status ==  StatusDefineCode::USER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::DELIVERY_ERROR ||
                $orderExists->order_status ==  StatusDefineCode::CUSTOMER_RETURNING ||
                $orderExists->order_status ==  StatusDefineCode::REFUNDS) &&
            $orderExists->has_refund_money_for_ctv == false
        ) {
            if ($orderExists->balance_collaborator_used > 0 && $orderExists->customer_id == $orderExists->collaborator_by_customer_id) {
                BalanceCustomerService::change_balance(
                    $orderExists->customer_id,
                    BalanceCustomerService::CTV_CANCEL_ORDER,
                    $orderExists->balance_collaborator_used,
                    $orderExists->id,
                    $orderExists->order_code,
                );
                $orderExists->update([
                    'has_refund_money_for_ctv' => true
                ]);
            }
        }
    }

    static function auto_refund_point_for_customer($orderExists, $request)
    {
        if (
            ($orderExists->order_status ==  StatusDefineCode::CUSTOMER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::OUT_OF_STOCK ||
                $orderExists->order_status ==  StatusDefineCode::USER_CANCELLED ||
                $orderExists->order_status ==  StatusDefineCode::DELIVERY_ERROR ||
                $orderExists->order_status ==  StatusDefineCode::CUSTOMER_RETURNING ||
                $orderExists->order_status ==  StatusDefineCode::REFUNDS) &&
            $orderExists->has_refund_point_for_customer == false
        ) {
            if ($orderExists->total_points_used > 0) {
                PointCustomerUtils::add_sub_point(
                    PointCustomerUtils::CUSTOMER_CANCEL_ORDER,
                    $orderExists->customer_id,
                    $orderExists->total_points_used,
                    $orderExists->id,
                    $orderExists->order_code
                );
            }
            $orderExists->update([
                'has_refund_point_for_customer' => true
            ]);
        }
    }
}
