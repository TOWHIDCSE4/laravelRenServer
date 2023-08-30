<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\HelpFindMotel;
use App\Models\MsgCode;
use App\Models\PostCategoryHelpPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpPostController extends Controller
{
    /**
     * Danh sách bài đăng trợ giúp
     * 
     * @queryParam title string tiêu đề của bài đăng
     * @queryParam limit int Số lượng item trong trang
     * @queryParam sort_by string tên cột
     * @queryParam descending boolean
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $title = $request->title;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending = filter_var($request->descending ?: false, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $helpPosts = PostCategoryHelpPost::sortByRelevance(true)
            ->join('help_posts', 'post_category_help_posts.help_post_id', '=', 'help_posts.id')
            ->when($title != null && trim($title) != '', function ($query) use ($title) {
                $query->where('help_posts.title', 'LIKE', '%' . $title . '%');
            })
            ->when($request->type_category != null, function ($query) use ($request) {
                $query->where('post_category_help_posts.category_help_post_id', $request->type_category);
            })
            ->when($sortBy != null && PostCategoryHelpPost::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->select('post_category_help_posts.*')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $helpPosts
        ]);
    }


    /**
     * Lấy 1 bài đăng hỗ trợ
     * 
     * @urlParam id mã bài đăng hỗ trợ
     * 
     */
    public function getOne(Request $request, $id)
    {
        $postCategoryHelpPost = PostCategoryHelpPost::where('id', $id);

        if (!$postCategoryHelpPost->exists()) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_HELP_POST_EXISTS[0],
                'msg' => MsgCode::NO_HELP_POST_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $postCategoryHelpPost->first()
        ]);
    }
}
