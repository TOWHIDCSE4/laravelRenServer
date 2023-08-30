<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\CategoryHelpPost;
use App\Models\MsgCode;
use Illuminate\Http\Request;

class CategoryHelpPostController extends Controller
{
    /**
     * Lấy loại danh sách đăng trợ giúp
     * 
     * @queryParam title string tiêu đề loại bài đăng
     * @queryParam limit int số item trong trang
     * @queryParam sort_by string tên cột (title, created_at)
     * @queryParam descending string tên cột (title, created_at)
     * 
     */
    public function getAll(Request $request)
    {
        $title = $request->title;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: false, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $categoryHelpPost = CategoryHelpPost::sortByRelevance(true)
            ->when($sortBy != null && CategoryHelpPost::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->where('is_show', true)
            ->when($title != null && trim($title) != '', function ($query) use ($title) {
                $query->where('title', 'LIKE', '%' . $title . '%');
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryHelpPost
        ]);
    }
}
