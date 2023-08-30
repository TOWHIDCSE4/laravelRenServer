<?php

namespace App\Helper;

use App\Models\Customer;
use App\Models\PointHistory;
use App\Models\PointSetting;

class PointCustomerUtils
{
    // const ROLL_CALL_1_DAY = "ROLL_CALL_1_DAY";
    // const ROLL_CALL_7_DAY = "ROLL_CALL_7_DAY";
    const REVIEW_PRODUCT = "REVIEW_PRODUCT"; //ĐÁnh giá
    const ORDER_COMPLETE = "ORDER_COMPLETE"; //Hoàn thành đơn hàng
    const REFERRAL_CUSTOMER = "REFERRAL_CUSTOMER"; //Giới thiệu khách hàng
    const USE_POINT_IN_ORDER = "USE_POINT_IN_ORDER"; //Sử dụng điểm
    const REFUND_ORDER = "REFUND_ORDER"; //Hoan thanh don hang
    const CUSTOMER_CANCEL_ORDER = "CUSTOMER_CANCEL_ORDER"; //Customer hủy đơn và các trạng thái tương tự trả lại xu

    public static function bonus_point_from_order($request, $order)
    {
        $pointSetting = PointSetting::where(
            'store_id',
            $request->store->id
        )->first();

        //tính điểm cho customer
        if ($pointSetting != null) {
            //Thêm đỉm thưởng xu
            if ($pointSetting->money_a_point  > 0 && $pointSetting->percent_refund > 0 && $pointSetting->percent_refund <= 100) {
                $moneyRefund = $order->total_final * ($pointSetting->percent_refund / 100);
                $point = ($moneyRefund / $pointSetting->money_a_point);

                if ($pointSetting->is_set_order_max_point === true &&  $pointSetting->order_max_point <  $point) {
                    $point = $pointSetting->order_max_point;
                }

                if ($point > 0) {
                    PointCustomerUtils::add_sub_point(
                        PointCustomerUtils::ORDER_COMPLETE,
                        $request->customer->id,
                        round($point),
                        $order->id,
                        $order->order_code
                    );

                    $order->update([
                        'points_awarded_to_customer' => (int)$point
                    ]);
                }
            }
        }
    }

    public static function add_sub_point($type, $store_id, $customer_id, $point, $references_id, $references_value)
    {
        $customer = Customer::where("id", $customer_id)->first();

        $current_point = ($point ?? 0) + ($customer->points ?? 0);
        if ($current_point < 0) {
            $current_point = 0;
        }

        if ($customer->is_passersby == true) {
            return;
        }

        if ($type == PointCustomerUtils::USE_POINT_IN_ORDER) {
            $history = PointHistory::where("type", $type)->where("references_value", $references_value)->first();
            if ($history == null) {

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point ?? 0
                    ]);
                }

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);
            }
        }

        if ($type == PointCustomerUtils::REFUND_ORDER) {

            if ($customer != null) {
                $customer->update([
                    "points" =>  $current_point ?? 0
                ]);
            }

            PointHistory::create([
                "store_id" => $store_id,
                "customer_id" => $customer_id,
                "type" => $type,
                "current_point" =>  $current_point,
                "point" => $point,
                "references_id" => $references_id,
                "references_value" => $references_value
            ]);
        }

        if ($type == PointCustomerUtils::ORDER_COMPLETE) {
            $history = PointHistory::where("type", $type)->where("references_value", $references_value)->first();
            if ($history == null) {

                if ($customer != null && $customer->is_passersby == false) {
                    $customer->update([
                        "points" =>  $current_point ?? 0
                    ]);
                }

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);
            }
        }

        if ($type == PointCustomerUtils::REFERRAL_CUSTOMER) {
            $history = PointHistory::where("type", $type)->where("references_value", $references_value)->first();
            if ($history == null) {

                if ($customer != null) {
                    $customer->update([
                        "points" =>  $current_point ?? 0
                    ]);
                }

                PointHistory::create([
                    "store_id" => $store_id,
                    "customer_id" => $customer_id,
                    "type" => $type,
                    "current_point" =>  $current_point,
                    "point" => $point,
                    "references_id" => $references_id,
                    "references_value" => $references_value
                ]);
            }
        }

        if ($type == PointCustomerUtils::REVIEW_PRODUCT) {


            if ($customer != null) {
                $customer->update([
                    "points" =>  $current_point
                ]);
            }

            PointHistory::create([
                "store_id" => $store_id,
                "customer_id" => $customer_id,
                "type" => $type,
                "current_point" =>  $current_point,
                "point" => $point,
                "references_id" => $references_id,
                "references_value" => $references_value
            ]);
        }

        if ($type == PointCustomerUtils::CUSTOMER_CANCEL_ORDER) {


            if ($customer != null) {
                $customer->update([
                    "points" =>  $current_point
                ]);
            }

            PointHistory::create([
                "store_id" => $store_id,
                "customer_id" => $customer_id,
                "type" => $type,
                "current_point" =>  $current_point,
                "point" => $point,
                "references_id" => $references_id,
                "references_value" => $references_value
            ]);
        }
    }
}
