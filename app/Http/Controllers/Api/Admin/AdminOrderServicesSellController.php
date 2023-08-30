<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusDefineCode;
use App\Helper\StatusOrderServicesSellDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\User\Community\CartServiceSellController;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\ItemCartServiceSell;
use App\Models\LineItemServiceSell;
use App\Models\MsgCode;
use App\Models\OrderServiceSell;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminOrderServicesSellController extends Controller
{
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $search = $request->search;
        $sortBy = $request->sort_by ?? 'created_at';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        $all = OrderServiceSell::orderBy('created_at', 'desc')
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney) {
                $query->where('total_final', '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney) {
                $query->where('total_final', '<=', $toMoney);
            })
            ->when($request->order_status != null, function ($query) {
                $query->where('order_status', request('order_status'));
            })
            ->when($request->payment_status != null, function ($query) {
                $query->where('payment_status', request('payment_status'));
            })
            ->when($request->province != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->when(!empty($sortBy) && OrderServiceSell::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            // ->search($search)
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $all,
        ]);
    }

    /**
     * 
     * Đặt hàng
     * 
     * @bodyParam phone_number string Tên
     * @bodyParam name string Tên
     * @bodyParam province string Tên
     * @bodyParam district string Tên
     * @bodyParam wards string Tên
     * @bodyParam email string Tên
     * @bodyParam phone string Tên
     * @bodyParam address_detail string Tên
     * 
     * 
     */
    public function create(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $cartInfo = CartServiceSellController::data_response(
            $request,
        );

        $cart_items = $cartInfo['data']['cart_items'];

        if (count($cart_items) == 0) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::NO_LINE_ITEMS[0],
                'msg' => MsgCode::NO_LINE_ITEMS[1],
            ]);
        }

        if (empty($request->name)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        if (empty($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        $create =  OrderServiceSell::create([
            "user_id" => $request->user->id,
            "order_code" => $now->format('dmy') . Helper::generateRandomString(8),
            "order_status" => StatusDefineCode::WAITING_FOR_PROGRESSING,
            "payment_status" => StatusDefineCode::WAITING_FOR_PROGRESSING_PAYMENT,
            "total_shipping_fee" => 0,
            "total_final" =>  $cartInfo['data']['total_final'],
            "total_before_discount" =>  $cartInfo['data']['total_before_discount'],
            "name" => $request->name,
            "email" => $request->email,
            "phone_number" => $request->phone_number,

            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),

            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,

            "address_detail" => $request->address_detail,
            "note" => $request->note,
        ]);

        $cart_items = ItemCartServiceSell::where('user_id', $request->user->id)->get();

        foreach ($cart_items  as  $cart_item) {
            $serviceSellExist = DB::table('service_sells')->where('id', $cart_item->service_sell_id)->first();

            LineItemServiceSell::create([
                'user_id' => $cart_item->user_id,
                'order_service_sell_id' => $create->id,
                'category_service_sell_id' => $serviceSellExist->category_service_sell_id,
                'service_sell_id' => $cart_item->service_sell_id,
                'quantity' => $cart_item->quantity,
                'total_price' => $cart_item->item_price,
                'images' => isset($serviceSellExist->images) ? $serviceSellExist->images : json_encode([]),
                'item_price' => isset($serviceSellExist->price) ? $serviceSellExist->price : 0,
                'name_service_sell' => isset($serviceSellExist->name) ? $serviceSellExist->name : ''
            ]);
        }

        ItemCartServiceSell::where('user_id', $request->user->id)->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $create
        ]);
    }

    public function update(Request $request)
    {
        $orderServiceSellExist = OrderServiceSell::where('id', request('order_service_sell_id'))->first();

        if ($orderServiceSellExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ]);
        }

        if (isset($request->name)) {
            if (empty($request->name)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                    'msg' => MsgCode::NAME_IS_REQUIRED[1],
                ]);
            }
        }

        if (isset($request->phone_number)) {
            if (empty($request->phone_number)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                    'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
                ]);
            }
        }

        if (isset($request->order_status)) {
            if (StatusOrderServicesSellDefineCode::getStatusOrderCode($request->order_status, false) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_ORDER_STATUS_CODE[0],
                    'msg' => MsgCode::INVALID_ORDER_STATUS_CODE[1],
                ]);
            }
        }

        $orderServiceSellExist->update([
            "order_status" => isset($request->order_status) ? $request->order_status : $orderServiceSellExist->order_status,
            "payment_status" => isset($request->payment_status) ? $request->payment_status : $orderServiceSellExist->payment_status,
            "name" => isset($request->name) ? $request->name : $orderServiceSellExist->name,
            "email" => isset($request->email) ? $request->email : $orderServiceSellExist->email,
            "phone_number" => isset($request->phone_number) ? $request->phone_number : $orderServiceSellExist->phone_number,
            "date_payment" => $request->order_status == StatusOrderServicesSellDefineCode::COMPLETED ? Helper::getTimeNowDateTime() : $orderServiceSellExist->date_payment,
            "province_name" => isset($request->province_name) ? Place::getNameProvince($request->province) : $orderServiceSellExist->province_name,
            "district_name" => isset($request->district_name) ? Place::getNameProvince($request->district) : $orderServiceSellExist->district_name,
            "wards_name" => isset($request->wards_name) ? Place::getNameProvince($request->wards) : $orderServiceSellExist->wards_name,

            "province" => isset($request->province) ? $request->province : $orderServiceSellExist->province,
            "district" => isset($request->district) ? $request->district : $orderServiceSellExist->district,
            "wards" => isset($request->wards) ? $request->wards : $orderServiceSellExist->wards,

            "address_detail" => isset($request->address_detail) ? $request->address_detail : $orderServiceSellExist->address_detail,
            "note" => isset($request->note) ? $request->note : $orderServiceSellExist->note,
        ]);

        // setup notifications
        if ($request->order_status != null && $request->order_status == StatusOrderServicesSellDefineCode::COMPLETED) {
            NotificationUserJob::dispatch(
                $orderServiceSellExist->user_id,
                "Đơn hàng đã hoàn thành",
                'Đơn hàng đã hoàn thành',
                TypeFCM::ORDER_SUCCESS,
                NotiUserDefineCode::USER_NORMAL,
                $orderServiceSellExist->order_code,
            );
        } else if ($request->order_status != null && $request->order_status == StatusOrderServicesSellDefineCode::CANCEL_ORDER) {
            NotificationUserJob::dispatch(
                $orderServiceSellExist->user_id,
                "Đơn hàng đã bị hủy",
                'Đơn hàng đã bị hủy',
                TypeFCM::CANCEL_ORDER,
                NotiUserDefineCode::USER_NORMAL,
                $orderServiceSellExist->order_code,
            );
        } else if ($request->order_status != null && $request->order_status == StatusOrderServicesSellDefineCode::SHIPPING) {
            NotificationUserJob::dispatch(
                $orderServiceSellExist->user_id,
                "Đơn hàng đã được xác nhận",
                'Đơn hàng đã được xác nhận',
                TypeFCM::ORDER_SHIPPING,
                NotiUserDefineCode::USER_NORMAL,
                $orderServiceSellExist->order_code,
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $orderServiceSellExist
        ]);
    }

    public function getOne(Request $request)
    {
        $orderServiceSellExist = OrderServiceSell::where('id', request('order_service_sell_id'))->first();

        if ($orderServiceSellExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $orderServiceSellExist
        ]);
    }


    public function delete(Request $request)
    {
        $orderServiceSellExist = DB::table('order_service_sells')->where('id', request('order_service_sell_id'))->first();

        if ($orderServiceSellExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1]
            ]);
        }

        $orderServiceSellExist->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
