<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\CategoryServiceSell;
use App\Models\MsgCode;
use App\Models\ServiceSell;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class CategoryServiceSellController extends Controller
{
    /**
     * Danh sách danh mục dịch vụ bán
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: false, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $categoryServiceSell = CategoryServiceSell::when($sortBy != null && Schema::hasColumn('category_service_sells', $sortBy), function ($query) use ($sortBy, $descending) {
            $query->orderBy($sortBy, $descending);
        })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryServiceSell
        ]);
    }
}
