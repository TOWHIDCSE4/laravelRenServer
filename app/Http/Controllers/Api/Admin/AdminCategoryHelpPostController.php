<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MsgCode;
use App\Helper\ResponseUtils;
use App\Models\CategoryHelpPost;
use App\Helper\ParamUtils;

/**
 * @group  Admin/Loại bài đăng trợ giúp
 */
class AdminCategoryHelpPostController extends Controller
{
    /**
     * Tạo loại bài đăng trợ giúp
     * 
     * @bodyParam is_show boolean default(true)
     * @bodyParam image_url string link ảnh
     * @bodyParam title string Tiêu đề require
     * @bodyParam description Mô tả
     * 
     */
    public function create(Request $request)
    {
        $title = $request->title;

        if ($title == null || trim($title) == '') {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[0],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ]);
        }

        $categoryHelpPostCreated = CategoryHelpPost::create([
            'is_show' => filter_var($request->is_show, FILTER_VALIDATE_BOOLEAN),
            'image_url' => $request->image_url ?: null,
            'title' => $title,
            'description' => $request->description ?: null
        ]);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryHelpPostCreated
        ]);
    }

    /**
     * Cập nhật loại bài đăng trợ giúp
     * 
     * @urlParam category_help_post_id int require
     * @bodyParam is_show boolean default(true)
     * @bodyParam image_url string link ảnh
     * @bodyParam title string Tiêu đề require
     * @bodyParam description Mô tả
     * 
     */
    public function update(Request $request, $id)
    {
        $title = $request->title;

        $categoryHelpPost = CategoryHelpPost::where('id', $id);

        if (!$categoryHelpPost->exists()) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[1],
            ]);
        }

        if ($title == null || trim($title) == '') {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_IS_REQUIRED[0],
                'msg' => MsgCode::TITLE_IS_REQUIRED[1],
            ]);
        }

        $categoryHelpPost->update([
            'is_show' => $request->is_show ?: 1,
            'image_url' => $request->image_url ?: null,
            'title' => $title,
            'description' => $request->description ?: null
        ]);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryHelpPost->first()
        ]);
    }

    /**
     * Lấy 1
     */
    public function getOne(Request $request, $id)
    {
        $categoryHelpPost = CategoryHelpPost::where('id', $id)->first();

        if ($categoryHelpPost == null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[1],
            ]);
        }
        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $categoryHelpPost
        ]);
    }

    /**
     * Xóa loại bài đăng trợ giúp
     * 
     * @urlParam category_help_post_id int require
     * 
     */
    public function delete(Request $request, $id)
    {
        $categoryHelpPost = CategoryHelpPost::where('id', $id);

        if (!$categoryHelpPost->exists()) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_HELP_POST_EXISTS[1],
            ]);
        }

        $categoryHelpPost->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }

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
