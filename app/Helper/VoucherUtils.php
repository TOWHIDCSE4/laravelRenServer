<?php

namespace App\Helper;

use App\Models\MsgCode;
use App\Models\Voucher;

class VoucherUtils
{

    static function data_voucher($codeVoucher, $allCart, $request, $total_after_discount)
    {
        $now = Helper::getTimeNowString();
        $used_voucher = null;
        //Tinh giam gia voucher
        if (!empty($codeVoucher)) {
            $voucher = Voucher::where('is_end', false)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->where('code', $codeVoucher)
                ->first();

            if (empty($voucher)) {

                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }
            if ($voucher->amount - $voucher->used <= 0 && $voucher->set_limit_amount == true) {
                return [
                    'msg_code' => MsgCode::VOUCHERS_ARE_SOLD_OUT
                ];
            }

            $listIdProduct = [];
            foreach ($allCart as $lineItem) {
                $listIdProduct[$lineItem->product->id] = [
                    "id"  => $lineItem->product->id,
                    "quantity" => $lineItem->quantity,
                    "price_or_discount" => $lineItem->item_price
                ];
            }
            if (!empty($voucher)) {


                $totalAvalibleForVoucher = 0;
                $used_voucher = $voucher;

                $product_in_voucher = [];
                foreach ($voucher->products as $product) {
                    $product_in_voucher[$product->id] = true;
                }

                if ($voucher->voucher_type == 0) { //tat ca san pham


                    foreach ($allCart as $lineItem) {

                        $totalAvalibleForVoucher += $lineItem->item_price * $lineItem->quantity;
                    }
                } else { //mot so san pham trong voucher
                    foreach ($allCart as $lineItem) {
                        if (isset($product_in_voucher[$lineItem->product->id])) {
                            $totalAvalibleForVoucher += $lineItem->item_price * $lineItem->quantity;
                        }
                    }
                }


                if (($voucher->set_limit_total == true && $totalAvalibleForVoucher >= $voucher->value_limit_total) ||
                    $voucher->set_limit_total == false
                ) {


                    $used_voucher = $voucher;

                    if ($voucher->discount_type == 0) {
                        $voucher_discount_amount = $voucher->value_discount;
                    }
                    if ($voucher->discount_type == 1) {
                        $totalDiscounnt = $totalAvalibleForVoucher  * ($voucher->value_discount / 100);

                        if ($totalDiscounnt > $voucher->max_value_discount && $voucher->set_limit_value_discount == true) {
                            $totalDiscounnt = $voucher->max_value_discount;
                        }
                        $voucher_discount_amount = $totalDiscounnt;
                    }
                } else {
                    return [
                        'msg_code' => MsgCode::NOT_ENOUGH_USE_VOUCHER
                    ];
                }


                //-----chốt giảm voucher
                $total_after_discount = $total_after_discount - $voucher_discount_amount < 0 ? 0 :  $total_after_discount - $voucher_discount_amount;
            } else {
                return [
                    'msg_code' => MsgCode::NO_VOUCHER_EXISTS
                ];
            }


            return [
                'voucher_discount_amount' =>  $voucher_discount_amount,
                'used_voucher'  => $used_voucher
            ];
        }
    }
}
