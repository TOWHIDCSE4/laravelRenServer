<?php

namespace App\Http\Controllers\Api\User\Community;


use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\ItemCartServiceSell;
use App\Models\LineItemServiceSell;
use App\Models\MsgCode;
use App\Models\ServiceSell;
use Illuminate\Http\Request;

/**
 * @group User/Cộng đồng/Giỏ hàng
 */

class CartServiceSellController extends Controller
{
    /**
     * 
     * Danh sách giỏ hàng
     * 
     */
    static public function data_response(Request $request)
    {

        $total_final = 0;
        $total_before_discount = 0;
        $cart_items = ItemCartServiceSell::where('user_id', $request->user->id)->get();
        $line_items_in_time = []; //lưu sp giá hiện tại


        foreach ($cart_items  as  $cart_item) {
            $total_before_discount +=  $cart_item->service_sell->price * $cart_item->quantity;
            $total_final +=  $cart_item->service_sell->price * $cart_item->quantity;

            array_push(
                $line_items_in_time,
                [
                    "id" => $cart_item->service_sell->id,
                    "quantity" => $cart_item->quantity,
                    "name" => $cart_item->service_sell->name,
                    "image_url" =>  $cart_item->service_sell->images != null && count($cart_item->service_sell->images) > 0 ? $cart_item->service_sell->images[0] : null,
                    "item_price" => $cart_item->service_sell->price,
                ],
            );
        }


        return  [
            'code' => $code ?? 200,
            'success' => $success ?? true,
            'msg_code' => $msg_code ?? MsgCode::SUCCESS[0],
            'msg' => $msg ?? MsgCode::SUCCESS[1],
            'data' =>  [
                'total_before_discount' => $total_before_discount,
                'total_shipping_fee' => $total_shipping_fee ?? 0,
                'total_final' => $total_final,
                'cart_items' =>  $cart_items,
            ],
        ];
    }

    static public function response_info_cart(Request $request)
    {
        $data =  CartServiceSellController::data_response($request);
        return response()->json(
            $data,
            $data['code']
        );
    }

    /**
     * Danh sách sản phẩm trong giỏ hàng
     * 
     */
    public function getAll(Request $request)
    {

        return $this->response_info_cart($request);
    }

    /**
     * 
     * Thêm sp vào giỏ
     * 
     * @bodyParam service_sell_id int id dịch vụ
     * @bodyParam quantity int số lượng
     * 
     */
    public function add(Request $request)
    {

        $serviceExists = ServiceSell::where('id', $request->service_sell_id)->first();
        if ($serviceExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
            ]);
        }

        if (empty($request->quantity) || $request->quantity <= 0) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_QUANTITY[0],
                'msg' => MsgCode::INVALID_QUANTITY[1],
            ]);
        }


        $itemHas = ItemCartServiceSell::where('user_id', $request->user->id)
            ->where('service_sell_id', $request->service_sell_id)->first();
        $lineItemHas = LineItemServiceSell::where('user_id', $request->user->id)
            ->where('order_service_sell_id', $request->service_sell_id)->first();

        if ($itemHas  == null) {
            ItemCartServiceSell::create([
                'service_sell_id' =>  $request->service_sell_id,
                'user_id' => $request->user->id,
                'quantity' => $request->quantity,
                'images' => json_encode($request->quantity) ?? [],
                // 'item_price' => $serviceExists->price * $request->quantity,
                'item_price' => $serviceExists->price,
            ]);
            // LineItemServiceSell::create([
            //     'order_service_sell_id' =>  $request->service_sell_id,
            //     'user_id' => $request->user->id,
            //     'quantity' => $request->quantity,
            //     'item_price' => $serviceExists->price * $request->quantity,
            // ]);
        } else {
            $itemHas->update([
                'service_sell_id' =>  $request->service_sell_id,
                'user_id' => $request->user->id,
                'quantity' => $request->quantity +  $itemHas->quantity,
                'images' => json_encode($request->quantity) ?? [],
                // 'item_price' => $serviceExists->price * ($request->quantity +  $itemHas->quantity),
                'item_price' => $serviceExists->price,
            ]);

            // $itemHas->update([
            //     'order_service_sell_id' =>  $request->service_sell_id,
            //     'user_id' => $request->user->id,
            //     'quantity' => $request->quantity +  $itemHas->quantity,
            //     'item_price' => $serviceExists->price * ($request->quantity +  $itemHas->quantity),
            // ]);
        }

        return CartServiceSellController::response_info_cart($request);
    }


    /**
     * 
     * Cập nhật số lượng
     * 
     * @bodyParam cart_id int cart_id
     * @bodyParam quantity int số lượng
     * 
     */
    public function update(Request $request)
    {
        $itemCartServiceSellExists = ItemCartServiceSell::where([
            ['user_id',  $request->user->id],
            ['id',  $request->cart_item_id],
        ])->first();

        if ($itemCartServiceSellExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ]);
        }

        $serviceExists = ServiceSell::where('id', $itemCartServiceSellExists->service_sell_id)->first();
        if ($serviceExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
            ]);
        }

        if ($request->quantity == 0) {
            $itemCartServiceSellExists->delete();
        } else {
            $itemCartServiceSellExists->update([
                'quantity' => $request->quantity,
                // 'item_price' => $serviceExists->price * $request->quantity,
                'item_price' => $serviceExists->price,
            ]);
        }


        return CartServiceSellController::response_info_cart($request);
    }


    /**
     * 
     * Xóa sp khỏi giỏ
     * 
     * @bodyParam cart_id int cart_id
     * 
     */
    public function delete(Request $request)
    {
        $itemCartServiceSellExists = ItemCartServiceSell::where('user_id',   $request->user->id)->first();

        if ($itemCartServiceSellExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CART_EXISTS[0],
                'msg' => MsgCode::NO_CART_EXISTS[1],
            ]);
        }


        $itemCartServiceSellExists->delete();
        return CartServiceSellController::response_info_cart($request);
    }
}
