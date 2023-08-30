<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\AdminBanner;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminBannerController extends Controller
{
    /**
     * Danh sách banner
     */
    public function getAll(Request $request)
    {
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminBanner::orderBy('created_at', 'desc')->get()
        ]);
    }
    /**
     * Thêm banner
     * 
     * @queryParam title string tiêu đề loại bài đăng
     * @queryParam image_url string tiêu đề loại bài đăng
     * @queryParam action_link string tiêu đề loại bài đăng
     * 
     */

    public function create(Request $request)
    {
        if (!isset($request->image_url)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::BANNER_MUST_REQUIRE_IMAGE[0],
                'msg' => MsgCode::BANNER_MUST_REQUIRE_IMAGE[1]
            ]);
        }

        AdminBanner::create([
            'image_url' => $request->image_url,
            'title' => isset($request->title) ? $request->title : null,
            'action_link' => isset($request->action_link) ? $request->action_link : null
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminBanner::get()
        ]);
    }


    /**
     * Cập nhật banner
     * 
     * @queryParam title string tiêu đề loại bài đăng
     * @queryParam image_url string link ảnh
     * @queryParam action_link string
     * 
     */
    public function update(Request $request, $id)
    {
        $bannerExist = AdminBanner::where('id', $id)->first();

        if ($bannerExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1]
            ]);
        }

        $bannerExist->update([
            'image_url' => isset($request->image_url) ? $request->image_url : $bannerExist->image_url,
            'title' => isset($request->title) ? $request->title :  $bannerExist->title,
            'action_link' => isset($request->action_link) ? $request->action_link :  $bannerExist->action_link
        ]);



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $bannerExist
        ]);
    }

    /**
     * Cập nhật banner
     */
    public function getOne(Request $request, $id)
    {
        $bannerExist = AdminBanner::where('id', $id)->first();

        if ($bannerExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_BANNER_EXISTS[0],
                'msg' => MsgCode::NO_BANNER_EXISTS[1]
            ]);
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $bannerExist
        ]);
    }

    /**
     * Xóa banner
     * 
     * @bodyParam list_id_banner
     * 
     */
    public function delete(Request $request)
    {
        $IdDeleted = [];
        $data = null;
        if (is_array($request->list_id_banner)) {
            foreach ($request->list_id_banner as $bannerId) {
                $existBanner = AdminBanner::where('id', $bannerId)->first();
                if ($existBanner != null) {
                    array_push($IdDeleted, $existBanner->id);
                    $existBanner->delete();
                }
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_ID_BANNER[0],
                'msg' => MsgCode::INVALID_LIST_ID_BANNER[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['list_id_banner_deleted' => $IdDeleted]
        ]);
    }
}
