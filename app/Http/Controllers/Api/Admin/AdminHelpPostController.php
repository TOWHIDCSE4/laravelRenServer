<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use Illuminate\Support\Facades\DB;
use App\Models\HelpPost;
use App\Helper\ParamUtils;
use App\Models\PostCategoryHelpPost;

/**
 * @group  Admin/Bài đăng trợ giúp
 */
class AdminHelpPostController extends Controller
{

    /**
     * Tạo bài đăng trợ giúp
     * 
     * @bodyParam category_help_post_id int Mã loại bài đăng
     * @bodyParam title string Tiêu đề require
     * @bodyParam image_url string link ảnh
     * @bodyParam is_show boolean default(true)
     * @bodyParam summary Tóm tắt
     * @bodyParam content Nội dung
     * 
     */
    public function create(Request $request)
    {
        $title = $request->title;
        $category_help_post_id = $request->category_help_post_id;

        $categoryHelpPost = DB::table('category_help_posts')->where('id', $category_help_post_id);

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

        $HelpPostCreate = HelpPost::create([
            'title' => $title,
            'image_url' => $request->image_url ?: null,
            'summary' => $request->summary ?: null,
            'content' => $request->content ?: null,
            'is_show' => filter_var($request->is_show, FILTER_VALIDATE_BOOLEAN),
        ]);

        PostCategoryHelpPost::create([
            'help_post_id' => $HelpPostCreate->id,
            'category_help_post_id' => $category_help_post_id
        ]);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $HelpPostCreate
        ]);
    }

    /**
     * Tạo bài đăng trợ giúp
     * 
     * @bodyParam post_category_help_post_id int Mã loại bài đăng
     * @bodyParam title string Tiêu đề require
     * @bodyParam image_url string link ảnh
     * @bodyParam is_show boolean default(true)
     * @bodyParam summary Tóm tắt
     * @bodyParam content Nội dung
     * 
     */
    public function update(Request $request, $id)
    {
        $title = $request->title;
        $category_help_post_id = $request->category_help_post_id;

        // $helpPostExist = HelpPost::where('id', $id);
        $postCategoryHelpPost = DB::table('post_category_help_posts')->where('id', $id);

        if (!$postCategoryHelpPost->exists()) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_HELP_POST_EXISTS[0],
                'msg' => MsgCode::NO_HELP_POST_EXISTS[1],
            ]);
        }

        // if (
        //     DB::table('post_category_help_posts')
        //     ->where([
        //         ['help_post_id', $postCategoryHelpPost->first()->help_post_id],
        //         ['category_help_post_id', $category_help_post_id]
        //     ])
        //     ->exists()
        // ) {
        //     return ResponseUtils::json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::HELP_POST_HELP_CATEGORY_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::HELP_POST_HELP_CATEGORY_ALREADY_EXISTS[1],
        //     ]);
        // }

        $helpPost = HelpPost::where('id', $postCategoryHelpPost->first()->help_post_id);
        $categoryHelpPost = DB::table('category_help_posts')->where('id', $category_help_post_id);

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

        $helpPost->update([
            'title' => $title,
            'image_url' => $request->image_url ?: null,
            'summary' => $request->summary ?: null,
            'content' => $request->content ?: null,
            'is_show' => filter_var($request->is_show, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        ]);

        $postCategoryHelpPost->update([
            'category_help_post_id' => $category_help_post_id
        ]);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $helpPost->first()
        ]);
    }


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

    /**
     * Xóa 1 bài đăng hỗ trợ
     * 
     * @urlParam id mã bài đăng hỗ trợ
     * 
     */
    public function delete(Request $request, $id)
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

        // HelpPost::where('id', $postCategoryHelpPost->first()->help_post_id)->delete();
        $postCategoryHelpPost->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
