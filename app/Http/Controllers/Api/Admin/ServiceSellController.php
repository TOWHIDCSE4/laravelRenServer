<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusServicesSellDefineCode;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\CategoryServiceSell;
use App\Models\ServiceSell;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

/**
 * @group Admin/Quản lý/Dịch vụ bán
 */

class ServiceSellController extends Controller
{
    /**
     * 
     * Danh sách dịch vụ bán
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: false, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $categoryServiceSellIds = explode(',', $request->category_service_sell_ids);

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $serviceSell = ServiceSell::when(!empty($request->category_service_sell_ids), function ($query) use ($categoryServiceSellIds) {
            $query->whereHas('category_service_sell', function ($q) use ($categoryServiceSellIds) {
                $q->whereIn('category_service_sells.id', $categoryServiceSellIds);
            });
        })
            ->when($sortBy != null && Schema::hasColumn('service_sells', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $serviceSell,
        ]);
    }

    /**
     * 
     * Thêm dịch vụ bán
     * 
     */
    public function create(Request $request)
    {
        if (!CategoryServiceSell::where('id', $request->category_service_sell_id)->exists()) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                '400' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
            ]);
        }

        if (empty($request->name)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                '400' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        if (ServiceSell::where('name', $request->name)->exists()) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ]);
        }


        $created =  ServiceSell::create([
            'name' => $request->name,
            'category_service_sell_id' => $request->category_service_sell_id,
            'name_str_filter' => StringUtils::convert_name_lowercase($request->name),
            'price' => $request->price,
            'images' => isset($request->images) ? json_encode($request->images) : [],
            'status' => StatusServicesSellDefineCode::PROGRESSING,
            'seller_service_name' => $request->seller_service_name,
            'service_sell_icon' => $request->service_sell_icon,
            'description' => $request->description,
            'phone_number_seller_service' => $request->phone_number_seller_service
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $created
        ]);
    }

    /**
     * Cập nhật 1 dịch vụ
     * 
     */
    public function update(Request $request)
    {

        $service_sell_id = request("service_sell_id");

        $serviceSellExists = ServiceSell::where(
            'id',
            $service_sell_id
        )->first();

        if ($serviceSellExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
            ]);
        }


        $serviceSellNameExists = ServiceSell::where(
            'id',
            '!=',
            $service_sell_id
        )->where('name', $request->name)->first();

        if ($serviceSellNameExists  != null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ]);
        }

        if (!empty($request->category_service_sell_id)) {
            if (!CategoryServiceSell::where('id', $request->category_service_sell_id)->exists()) {
                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                    '400' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
                ]);
            }
        }

        $serviceSellExists->update([
            'name' => isset($request->name) ? $request->name : $serviceSellExists->name,
            'category_service_sell_id' => isset($request->category_service_sell_id) ? $request->category_service_sell_id : $serviceSellExists->category_service_sell_id,
            'name_str_filter' => isset($request->name) ? StringUtils::convert_name_lowercase($request->name) : $serviceSellExists->name_str_filter,
            'price' => isset($request->price) ? $request->price : $serviceSellExists->price,
            'images' => isset($request->images) ? json_encode($request->images) : $serviceSellExists->images,
            'status' => isset($request->status) ? $request->status : $serviceSellExists->status,
            'description' => $request->description ?? $serviceSellExists->description,
            'seller_service_name' => isset($request->seller_service_name) ? $request->seller_service_name : $serviceSellExists->seller_service_name,
            'service_sell_icon' => isset($request->service_sell_icon) ? $request->service_sell_icon : $serviceSellExists->service_sell_icon,
            'phone_number_seller_service' => isset($request->phone_number_seller_service) ? $request->phone_number_seller_service : $serviceSellExists->phone_number_seller_service,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $serviceSellExists
        ]);
    }

    /**
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $service_sell_id = request("service_sell_id");

        $serviceSellExists = ServiceSell::where(
            'id',
            $service_sell_id
        )->first();

        if ($serviceSellExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $serviceSellExists,
        ]);
    }

    /**
     * Xóa 1 dịch vụ
     * 
     */
    public function delete(Request $request)
    {

        $service_sell_id = request("service_sell_id");

        $serviceSellExists = ServiceSell::where(
            'id',
            $service_sell_id
        )->first();

        if ($serviceSellExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_SELL_EXISTS[1],
            ]);
        }

        $idDeleted = $serviceSellExists->id;
        $serviceSellExists->delete();



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
