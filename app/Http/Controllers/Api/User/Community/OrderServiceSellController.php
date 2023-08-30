<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusDefineCode;
use App\Helper\StatusOrderServicesSellDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Jobs\NotificationUserJob;
use App\Models\AddressAddition;
use App\Models\ItemCartServiceSell;
use App\Models\LineItemServiceSell;
use App\Models\MsgCode;
use App\Models\OrderServiceSell;
use App\Models\ServiceSell;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Cộng đồng/Đơn hàng
 */

class OrderServiceSellController extends Controller
{

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
    public function sendCart(Request $request)
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
                'category_service_sell_id' => $serviceSellExist->category_service_sell_id,
                'order_service_sell_id' => $create->id,
                'service_sell_id' => $cart_item->service_sell_id,
                'quantity' => $cart_item->quantity,
                'total_price' => $cart_item->item_price * $cart_item->quantity,
                'images' => isset($serviceSellExist->images) ? $serviceSellExist->images : json_encode([]),
                'item_price' => isset($serviceSellExist->price) ? $serviceSellExist->price : 0,
                'name_service_sell' => isset($serviceSellExist->name) ? $serviceSellExist->name : ''
            ]);
        }


        $getAddressAddition = AddressAddition::where('user_id', $request->user->id)
            ->first();
        if (!$getAddressAddition) {
            AddressAddition::create([
                "user_id" => $request->user->id,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "address_detail" => $request->address_detail,
                "note" => $request->note,
            ]);
        } else {
            $getAddressAddition->update(
                [
                    "province" => $request->province,
                    "district" => $request->district,
                    "wards" => $request->wards,
                    "address_detail" => $request->address_detail,
                    "note" => $request->note,
                ]
            );
        }

        ItemCartServiceSell::where('user_id', $request->user->id)->delete();
        // setup notifications
        NotificationAdminJob::dispatch(
            null,
            "Đơn hàng mới",
            'Đơn hàng mới từ người dùng ' . $request->user->name,
            TypeFCM::NEW_ORDER,
            NotiUserDefineCode::USER_IS_ADMIN,
            $create->id,
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $create
        ]);
    }


    /**
     * 
     * Danh sách đơn hàng
     * 
     * 
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $search = $request->search;
        $sortBy = $request->sort_by ?? 'created_at';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        $all = OrderServiceSell::orderBy('created_at', 'desc')
            ->where('user_id', $request->user->id)
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
     * Lấy 1 đơn hàng
     * 
     */
    public function getOne(Request $request)
    {
        $orderServiceSell = OrderServiceSell::where([
            ['user_id', $request->user->id],
            ['order_code', request('order_code')]
        ])
            ->first();

        if ($orderServiceSell == null && $request->user->is_admin == true) {
            $orderServiceSell = OrderServiceSell::where([
                ['order_code', request('order_code')]
            ])
                ->first();
        }

        if ($orderServiceSell == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_SERVICE_EXISTS[1]
            ]);
        }
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $orderServiceSell,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $orderServiceSellExist = OrderServiceSell::where('order_code', request('order_code'))->first();

        if ($orderServiceSellExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_EXISTS[1],
            ]);
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

        // if (StatusOrderServicesSellDefineCode::COMPLETED == $orderServiceSellExist->order_status) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::ORDER_HAS_BEEN_COMPLETED_BEFORE[0],
        //         'msg' => MsgCode::ORDER_HAS_BEEN_COMPLETED_BEFORE[1],
        //     ]);
        // }
        // if (StatusOrderServicesSellDefineCode::CANCEL_ORDER == $orderServiceSellExist->order_status) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::ORDER_HAS_BEEN_CANCELED_BEFORE[0],
        //         'msg' => MsgCode::ORDER_HAS_BEEN_CANCELED_BEFORE[1],
        //     ]);
        // }

        $orderServiceSellExist->update([
            "order_status" => !empty($request->order_status) ? $request->order_status : $orderServiceSellExist->order_status,
        ]);

        // setup notifications
        if ($request->order_status != null && $request->order_status == StatusOrderServicesSellDefineCode::CANCEL_ORDER) {
            NotificationAdminJob::dispatch(
                null,
                "Đơn hàng đã bị hủy",
                'Đơn hàng ' . $orderServiceSellExist->order_code . ' đã bị hủy',
                TypeFCM::CANCEL_ORDER,
                NotiUserDefineCode::USER_IS_ADMIN,
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
    public function sendCartImmediate(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $serviceSell = ServiceSell::where('id', $request->service_sell_id)->first();

        if (!$serviceSell) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
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
            "total_final" =>  $serviceSell->price * $request->quantity,
            "total_before_discount" =>  $serviceSell->price * $request->quantity,
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


        LineItemServiceSell::create([
            'user_id' => $request->user->id,
            'category_service_sell_id' => $serviceSell->category_service_sell_id,
            'order_service_sell_id' => $create->id,
            'service_sell_id' => $serviceSell->id,
            'quantity' => $request->quantity,
            'total_price' => $serviceSell->price * $request->quantity,
            'images' => json_encode($serviceSell->images),
            'item_price' =>  $serviceSell->price,
            'name_service_sell' =>  $serviceSell->name
        ]);

        // setup notifications
        NotificationAdminJob::dispatch(
            null,
            "Đơn hàng mới",
            'Đơn hàng mới từ người dùng ' . $request->user->name,
            TypeFCM::NEW_ORDER,
            NotiUserDefineCode::USER_IS_ADMIN,
            $create->id,
        );

        $getAddressAddition = AddressAddition::where('user_id', $request->user->id)
            ->first();
        if (!$getAddressAddition) {
            AddressAddition::create([
                "user_id" => $request->user->id,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "address_detail" => $request->address_detail,
                "note" => $request->note,
            ]);
        } else {
            $getAddressAddition->update(
                [
                    "province" => $request->province,
                    "district" => $request->district,
                    "wards" => $request->wards,
                    "address_detail" => $request->address_detail,
                    "note" => $request->note,
                ]
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $create
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
    public function reorder(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $orderServiceSellExists = OrderServiceSell::where([
            ['order_code', $request->order_code],
            ['user_id', $request->user->id],
        ])->first();

        if (!$orderServiceSellExists) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::NO_ORDER_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_ORDER_SERVICE_EXISTS[1],
            ]);
        }

        DB::beginTransaction();
        try {

            $create =  OrderServiceSell::create([
                "user_id" => $request->user->id,
                "order_code" => $now->format('dmy') . Helper::generateRandomString(8),
                "order_status" => StatusDefineCode::WAITING_FOR_PROGRESSING,
                "payment_status" => StatusDefineCode::WAITING_FOR_PROGRESSING_PAYMENT,
                "total_shipping_fee" => 0,
                "total_final" =>  $orderServiceSellExists->total_final,
                "total_before_discount" =>  $orderServiceSellExists->total_before_discount,
                "name" => $orderServiceSellExists->name,
                "email" => $orderServiceSellExists->email,
                "phone_number" => $orderServiceSellExists->phone_number,

                "province_name" => $orderServiceSellExists->province_name,
                "district_name" => $orderServiceSellExists->district_name,
                "wards_name" => $orderServiceSellExists->wards_name,

                "province" => $orderServiceSellExists->province,
                "district" => $orderServiceSellExists->district,
                "wards" => $orderServiceSellExists->wards,

                "address_detail" => $orderServiceSellExists->address_detail,
                "note" => $orderServiceSellExists->note,
            ]);

            $lineItemOrderServiceSellExists = LineItemServiceSell::where('order_service_sell_id', $orderServiceSellExists->id)->get();

            foreach ($lineItemOrderServiceSellExists  as  $lineItem) {
                LineItemServiceSell::create([
                    'user_id' => $request->user->id,
                    'category_service_sell_id' => $lineItem->category_service_sell_id,
                    'order_service_sell_id' => $create->id,
                    'service_sell_id' => $lineItem->service_sell_id,
                    'quantity' => $lineItem->quantity,
                    'total_price' => $lineItem->total_price * $lineItem->quantity,
                    'images' => json_encode($lineItem->images),
                    'item_price' => $lineItem->item_price,
                    'name_service_sell' => $lineItem->name_service_sell
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        // setup notifications
        NotificationAdminJob::dispatch(
            null,
            "Đơn hàng mới",
            'Đơn hàng mới từ người dùng ' . $request->user->name,
            TypeFCM::NEW_ORDER,
            NotiUserDefineCode::USER_IS_ADMIN,
            $create->id,
        );


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   OrderServiceSell::where('order_code', $create->order_code)->first()
        ]);
    }
}
