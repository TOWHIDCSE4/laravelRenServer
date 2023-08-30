<?php

namespace App\Helper;

use App\Models\Customer;
use App\Models\HistoryPayOrder;
use App\Models\RevenueExpenditure;
use App\Models\Supplier;

class RevenueExpenditureUtils
{

    const TYPE_PAYMENT_ORDERS = 0; //Thanh toán cho đơn hàng
    const TYPE_OTHER_INCOME = 1; //Thu nhập khác
    const TYPE_BONUS = 2; //Tiền thưởng
    const TYPE_INDEMNIFICATION = 3; //Khởi tạo kho
    const TYPE_RENTAL_PROPERTY = 4; //cho thuê tài sản
    const TYPE_SALE_AND_LIQUIDATION_OF_ASSETS = 5; //Nhượng bán thanh lý tài sản
    const TYPE_DEBT_COLLECTION_CUSTOMERS = 6; //Thu nợ khách hàng

    const TYPE_PAYMENT_IMPORT_STOCK = 17; // Thanh toán cho đơn nhập hàng
    const TYPE_OTHER_COSTS = 10; // Chi phí khác 
    const TYPE_PRODUCTION_COST = 11; // Chi phí san pham 
    const TYPE_COST_OF_RAW_MATERIALS = 12; //chi phí nguyên vật liệu
    const TYPE_COST_OF_LIVING = 13; // Chi  phí sinh hoạt
    const TYPE_LABOR_COSTS = 14; // Chi phí nhân công
    const TYPE_SELLING_EXPENSES = 15; // chi phí bán hàng
    const TYPE_STORE_MANAGEMENT_COSTS = 16; // Chi phí quản lý cửa hàng

    const RECIPIENT_GROUP_CUSTOMER = 0; //Nhóm khách hàng
    const RECIPIENT_GROUP_SUPPLIER = 1; //Nhóm nhà cung cấp
    const RECIPIENT_GROUP_STAFF = 2; //Nhóm nhân viên
    const RECIPIENT_GROUP_OTHER = 3; //Đối tượng khác

    const PAYMENT_TYPE_CASH = 0; //Tiền mặt
    const PAYMENT_TYPE_SWIPE = 1; // Quẹt
    const PAYMENT_TYPE_COD = 2; //COD
    const PAYMENT_TYPE_TRANSFER = 3; //Chuyển khoản
    const PAYMENT_TYPE_ELECTRONIC_WALLET = 4; //Ví điện tử

    const ACTION_CREATE_DEFAULT_REVENUE = 0; //Tạo phiếu thu
    const ACTION_CREATE_DEFAULT_EXPENDITURE = 1; //Tạo phiếu chi
    const ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE = 2; //Nhập hàng từ nhà cung cấp
    const ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE = 3; //Trả tiền hàng cho ncc
    const ACTION_CREATE_SUPPLIER_DEBT_IMPORT_STOCK_EXPENDITURE = 9; //Thanh toán đơn nhập hàng đang nợ
    const ACTION_CREATE_SUPPLIER_REFUND_EXPENDITURE = 4; //Tạo phiếu chi trả hàng ncc
    const ACTION_CREATE_CUSTOMER_DEBT_ORDER_REVENUE = 5; //Thu tiền nợ
    const ACTION_CREATE_CUSTOMER_ORDER_EXPENDITURE = 6; //Khách hàng nợ mua hàng
    const ACTION_CREATE_CUSTOMER_ORDER_COMPLETED_REVENUE = 7; //Thu tiền hoàn thành đơn hàng
    const ACTION_CREATE_CUSTOMER_ORDER_PAY_REVENUE = 8; //Khách hàng trả tiền đơn hàng
    const ACTION_CREATE_CUSTOMER_REFUND_REVENUE = 10; //Khách hàng trả hàng
    const ACTION_CREATE_CUSTOMER_REFUND_EXPENDITURE = 11; //Trả tiền lại cho khách trả hàng

    const ACTION_NAME_BY_ID = [
        RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_REVENUE => "Tạo phiếu thu",
        RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_EXPENDITURE  => "Tạo phiếu chi",
        RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE  => "Tạo phiếu thu nhập hàng",
        RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE => "Tạo phiếu chi nhập hàng",
        RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_REFUND_EXPENDITURE => "Tạo phiếu chi trả hàng ncc",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_REVENUE => "Tạo phiếu thu khách hàng trả hàng",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_EXPENDITURE => "Tạo phiếu chi trả tiền hàng hoàn trả",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_DEBT_ORDER_REVENUE => "Tạo phiếu thu khách trả nợ",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_EXPENDITURE => "Phiếu chi đưa hàng cho khách",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_COMPLETED_REVENUE  => "Tạo phiếu thu khách mua hàng",
        RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_PAY_REVENUE => "Phiếu thu trả tiền đơn hàng",
        RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_DEBT_IMPORT_STOCK_EXPENDITURE => "Thanh toán đơn nhập hàng đang nợ"
    ];

    const ARR_RECIPIENT_GROUP = [
        RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER,
        RevenueExpenditureUtils::RECIPIENT_GROUP_STAFF,
        RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER,
        RevenueExpenditureUtils::RECIPIENT_GROUP_OTHER,
    ];


    const ARR_PAYMENT = [
        RevenueExpenditureUtils::PAYMENT_TYPE_CASH,
        RevenueExpenditureUtils::PAYMENT_TYPE_COD,
        RevenueExpenditureUtils::PAYMENT_TYPE_SWIPE,
        RevenueExpenditureUtils::PAYMENT_TYPE_TRANSFER,
    ];

    static function add_new_revenue_expenditure(
        $request,
        $type,
        $recipient_group,
        $recipient_references_id,
        $code,
        $references_id,
        $references_value,
        $reference_name,
        $action_create,
        $change_money,
        $is_revenue,
        $description,
        $payment_method,
        $branch_id = null
    ) {

        $change_money = round($change_money);

        $current_money = 0;
        $debt = 0;

        if ($recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER) {

            $supplier = Supplier::where('id', $recipient_references_id)->first();
            $current_money = $supplier->debt;
            //trả tiền hàng
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_EXPENDITURE) {

                $debt = $current_money - $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }


            //nhập hàng
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_IMPORT_STOCK_REVENUE) {

                $debt = $current_money + $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }

            //trả hàng cho ncc
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_REFUND_EXPENDITURE) {
                $debt = $current_money - $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }


            //tạo phiếu thu thủ công
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_REVENUE) {

                $debt = $current_money + $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }

            //tạo phiếu chi thủ công
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_EXPENDITURE) {

                $debt = $current_money - $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }
            //thanh toán đơn nhập hàng đang nợ
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_SUPPLIER_DEBT_IMPORT_STOCK_EXPENDITURE) {

                $debt = $current_money - $change_money;
                $supplier->update([
                    "debt"  => $debt
                ]);
            }
        }

        if ($recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER) {

            $customer = Customer::where('id', $recipient_references_id)->first();
            $current_money = $customer->debt;


            //tạo phiếu thu thủ công
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_REVENUE) {

                $debt = $current_money - $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }

            //tạo phiếu chi thủ công
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_DEFAULT_EXPENDITURE) {

                $debt = $current_money + $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }

            //tạo phiếu thu trả khách hàng trả hàng
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_REVENUE) {

                $debt = $current_money - $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }

            //tạo phiếu thu trả khách hàng trả hàng
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_REFUND_EXPENDITURE) {

                $debt = $current_money + $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }


            //tạo phiếu chi khi giao hàng cho khách
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_EXPENDITURE) {

                $debt = $current_money + $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }

            //tạo phiếu thu khách hàng trả tiền
            if ($action_create == RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_PAY_REVENUE) {

                $debt = $current_money - $change_money;
                $customer->update([
                    "debt"  => $debt
                ]);
            }
        }

        if ($branch_id != null || $request->branch != null) {
            $revenueExpenditureCreate =  RevenueExpenditure::create([
                "branch_id" =>  $branch_id != null ?  $branch_id  : $request->branch->id,
                "type" =>  $type,
                "code" => $code ?? Helper::getRandomRevenueExpenditureString(),
                "recipient_group" => $recipient_group,
                "recipient_references_id" => $recipient_references_id,
                "references_id" => $references_id,
                "references_value" => $references_value,
                "reference_name" => $reference_name,
                "change_money" =>  $change_money,
                "current_money" =>  round($debt),
                "action_create" => $action_create,
                "is_revenue" =>  $is_revenue,
                "description" =>  $description,
                "payment_method" =>  $payment_method,
                'user_id' => $request->user != null ? $request->user->id : null,
                'staff_id' =>  $request->staff != null ? $request->staff->id : null,
            ]);
        }


        return  $revenueExpenditureCreate;
    }

    static function auto_add_expenditure_order($orderExists, $request)
    {
        if (
            $orderExists->order_status == StatusDefineCode::SHIPPING
            ||
            $orderExists->order_status == StatusDefineCode::COMPLETED
        ) {
            //Phieu  chi
            $lastRE =   RevenueExpenditure::where(
                'references_value',
                $orderExists->order_code,
            )->where(
                "store_id",
                $orderExists->store_id
            )
                ->where('is_revenue', false)->first();
            if ($lastRE == null) {

                $k =  RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_SELLING_EXPENSES,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER,
                    $orderExists->customer_id,
                    null,
                    $orderExists->id,
                    $orderExists->order_code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_EXPENDITURE,
                    $orderExists->total_final,
                    false,
                    "Giao hàng cho khách hàng",
                    RevenueExpenditureUtils::PAYMENT_TYPE_CASH,
                    $orderExists->branch_id,
                );
            }
        }
    }

    static function auto_add_revenue_order($orderExists, $request)
    {
        $sum_revenue = RevenueExpenditure::where('references_value', $orderExists->order_code)->where('is_revenue', true)
            ->sum('change_money');

        if ($sum_revenue < $orderExists->total_final) {

            $paid =  $orderExists->total_final - $orderExists->remaining_amount - $sum_revenue;

            if ($paid > 0) {
                $TCcreated =  RevenueExpenditureUtils::add_new_revenue_expenditure(
                    $request,
                    RevenueExpenditureUtils::TYPE_PAYMENT_ORDERS,
                    RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER,
                    $orderExists->customer_id,
                    null,
                    $orderExists->id,
                    $orderExists->order_code,
                    null,
                    RevenueExpenditureUtils::ACTION_CREATE_CUSTOMER_ORDER_PAY_REVENUE,
                    $paid,
                    true,
                    "Thu tiền thanh toán đơn hàng",
                    RevenueExpenditureUtils::PAYMENT_TYPE_CASH,
                    $orderExists->branch_id,
                );

                if ($TCcreated != null) {
                    HistoryPayOrder::create([
                        "order_id" => $orderExists->id,
                        "payment_method_id" => $request->payment_method_id,
                        "money" => $paid,
                        'remaining_amount' =>   $orderExists->remaining_amount,
                        'revenue_expenditure_id' => $TCcreated->id
                    ]);
                }
            }
        }
    }
}
