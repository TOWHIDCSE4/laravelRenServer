<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ResponseUtils;
use App\Helper\StatusStatisticAdminDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MoPost;
use App\Models\MoPostFindMotel;
use App\Models\MoPostRoommate;
use App\Models\MsgCode;
use App\Models\StatisticAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group  Community/thống kê admin
 *
 * APIs thống kê admin
 */
class StatisticAdminController extends Controller
{
    /**
     * 
     * Thêm 1 thống kê bài đăng phòng
     * 
     * @bodyParam type int loại thống kê
     * 
     */
    public function addCallMoPost(Request $request, $post_id)
    {
        $modelPostExists = MoPost::where('id', $post_id)->first();

        if ($modelPostExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        if ($modelPostExists != null) {
            $modelPostExists->update([
                'number_calls' => $modelPostExists->number_calls + 1
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    /**
     * 
     * Thêm 1 thống kê bài đăng phòng
     * 
     * @bodyParam type int loại thống kê
     * 
     */
    public function addCallMoPostFindMotel(Request $request, $post_find_motel_id)
    {
        $modelPostExists = MoPostFindMotel::where('id', $post_find_motel_id)->first();

        if ($modelPostExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        if ($modelPostExists != null) {
            $modelPostExists->update([
                'number_calls' => $modelPostExists->number_calls + 1
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    /**
     * 
     * Thêm 1 thống kê bài đăng phòng
     * 
     * @bodyParam type int loại thống kê
     * 
     */
    public function addCallMoPostFindRoommate(Request $request, $post_roommate_id)
    {
        $moPostRoommate = MoPostRoommate::where('id', $post_roommate_id)->first();

        if ($moPostRoommate == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        if ($moPostRoommate != null) {
            $moPostRoommate->update([
                'number_calls' => $moPostRoommate->number_calls + 1
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
