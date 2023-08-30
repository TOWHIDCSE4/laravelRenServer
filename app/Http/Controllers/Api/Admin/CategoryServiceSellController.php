<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\CategoryServiceSell;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class CategoryServiceSellController extends Controller
{
    /**
     * Tạo danh mục dịch vụ bán
     * 
     */
    public function create(Request $request)
    {
        $categoryServiceSell = CategoryServiceSell::create([
            'name' => $request->name,
            'image' => $request->image,
            'is_active' => $request->is_active,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryServiceSell
        ]);
    }
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
            ->when($request->is_active != null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
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

    /**
     * Lấy 1 danh mục dịch vụ bán
     * 
     */
    public function getOne(Request $request)
    {
        $categoryServiceSell = CategoryServiceSell::where('id', $request->category_service_sell_id)->first();

        if ($categoryServiceSell == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryServiceSell,
        ]);
    }

    /**
     * Cập nhật danh mục dịch vụ bán
     * 
     */
    public function update(Request $request)
    {
        $categoryServiceSell = CategoryServiceSell::where('id', $request->category_service_sell_id)->first();

        if ($categoryServiceSell == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
            ]);
        }

        $categoryServiceSell->update([
            'name' => $request->name != null ? $request->name : $categoryServiceSell->name,
            'image' => $request->image != null ? $request->image : $categoryServiceSell->image,
            'is_active' => $request->is_active != null ? $request->is_active : $categoryServiceSell->is_active,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryServiceSell,
        ]);
    }
    /**
     * Xóa danh mục dịch vụ bán
     * 
     */
    public function delete(Request $request)
    {
        $categoryServiceSell = CategoryServiceSell::where('id', $request->category_service_sell_id)->first();

        if ($categoryServiceSell == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
            ]);
        }

        $categoryServiceSell->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryServiceSell,
        ]);
    }
}
