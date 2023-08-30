<?php

namespace App\Services;

use App\Models\ChangeBalanceCollaborator;
use App\Models\Collaborator;
use App\Models\Customer;
use App\Models\Order;

class BalanceCustomerService
{
    const ORDER_COMPLETED = 0;
    const BONUS_MONTH = 1;
    const PAYMENT_REQUEST = 2;
    const USE_BALANCE_ORDER = 3;
    const ORDER_COMPLETED_T1 = 4; //Cộng tiền cho cộng tác viên T1
    const ORDER_COMPLETED_CTV_RE = 5; //Cộng tiền cho cộng tác viên giới thiệu
    const ORDER_REFUND_CTV = 6; //trừ tiền hoàn đơn hàng
    const CTV_CANCEL_ORDER = 7; //CTV hủy đơn hàng hoàn lại số dư


    //Thay đổi và cập nhật lịch sử số dư
    public static function change_balance($store_id, $customer_id, $type, $money,  $references_id = 0,  $references_value = "")
    { // references_id là id của nơi cung cấp tiền như order


        if ($customer_id === null || $money == 0) {
            return;
        }


        $collaborator  = Collaborator::where('store_id', $store_id)->where('customer_id', $customer_id)->first();

        if ($collaborator != null && $money !== null && $type !== null) {
            $nextBalance = $collaborator->balance + $money;

            $collaborator->update([
                'balance' => $nextBalance
            ]);


            //Đã cộng tiền xử lý lưu db

            if ($type == BalanceCustomerService::ORDER_COMPLETED) { // thanh toan thanh cong cong tien
                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => null,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }

            if ($type == BalanceCustomerService::ORDER_COMPLETED_T1) { // CTV T1
                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }

            if ($type == BalanceCustomerService::ORDER_COMPLETED_CTV_RE) { // CTV GIỚI THIỆU
                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }

            if ($type == BalanceCustomerService::USE_BALANCE_ORDER) { // su dung de thanh toan don hang

                $order = Order::where('store_id', $store_id)->where('id', $references_id)->first();
                $history = ChangeBalanceCollaborator::where('store_id', $store_id)
                    ->where('collaborator_id', $customer_id)
                    ->where('references_id', $references_id)
                    ->where('type', $type)->first();
                //kiểm tra order tồn tại và phải chua có bản ghi

                if ($order !== null &&  $history == null) {
                    //thêm history

                    ChangeBalanceCollaborator::create([
                        'store_id' => $store_id,
                        'collaborator_id' =>  $collaborator->id,
                        "type" => $type,
                        "current_balance" => $nextBalance,
                        "money" => $money,
                        "references_id" => $references_id,
                        "references_value" => $order->order_code,
                        "note" => ""
                    ]);
                }
            }

            if ($type == BalanceCustomerService::BONUS_MONTH) { //thuong thang

                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }

            if ($type == BalanceCustomerService::PAYMENT_REQUEST) { // thanh toan yeu cau thanh toan

                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }

            if ($type == BalanceCustomerService::ORDER_REFUND_CTV) { // trừ tiền hoàn đơn hàng

                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }


            if ($type == BalanceCustomerService::CTV_CANCEL_ORDER) { // cộng tác viên hủy đơn cộng lại tiền cho nó

                ChangeBalanceCollaborator::create([
                    'store_id' => $store_id,
                    'collaborator_id' =>  $collaborator->id,
                    "type" => $type,
                    "current_balance" => $nextBalance,
                    "money" => $money,
                    "references_id" => $references_id,
                    "references_value" => $references_value,
                    "note" => ""
                ]);
            }
        }
    }
}
