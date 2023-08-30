<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\ServiceSell;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class ServiceSellController extends Controller
{
    /**
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
     * Lấy 1 dịch vụ bán
     * 
     */
    public function getOne(Request $request, $id)
    {
        $serviceSellExist = ServiceSell::where('id', $id)->first();

        if ($serviceSellExist == null) {
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
            'data' => $serviceSellExist,
        ]);
    }
}
