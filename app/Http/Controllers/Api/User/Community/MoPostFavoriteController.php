<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ResponseUtils;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Http\Controllers\Controller;
use App\Models\HistoryPotentialUser;
use App\Models\MoPost;
use App\Models\MoPostFavorite;
use App\Models\MsgCode;
use App\Models\PotentialUser;
use App\Utils\PotentialUserUtil;
use Illuminate\Http\Request;

/**
 * @group User/Cộng đồng/Phòng yêu thích
 */

class MoPostFavoriteController extends Controller
{
    /**
     * 
     * Danh cách phòng yêu thích
     * 
     * @queryParam title tìm theo tiêu đề
     * @queryParam money_from tiền tối thiểu
     * @queryParam money_to tiền tối đa
     * @queryParam province tỉnh
     * @queryParam district quận
     * @queryParam wards huyện
     * @queryParam sex giới tính
     * @queryParam has boolean tất cả field có kiểu bool
     * 
     */
    public function getAll(Request $request)
    {
        $ids = MoPostFavorite::where('user_id', $request->user->id)->get()
            ->pluck('mo_post_id');

        $all = MoPost::whereIn('id', $ids)
            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        // $all = MoPost::join('motels')

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $all,
        ]);
    }


    /**
     * 
     * Yêu thích 1 phòng
     * 
     * @queryParam is_favorite yêu thích hay không
     * 
     */
    public function favorite(Request $request)
    {
        $mo_post_id = request("mo_post_id");

        $moPost = MoPost::where('id',  $mo_post_id)->first();
        if ($moPost == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        $is_favorite = filter_var($request->is_favorite, FILTER_VALIDATE_BOOLEAN);

        if ($is_favorite  == true) {
            $has = MoPostFavorite::where('user_id', $request->user->id)
                ->where('mo_post_id', $mo_post_id)
                ->first();

            if ($has == null) {
                MoPostFavorite::create([
                    'user_id' => $request->user->id,
                    'mo_post_id' =>  $mo_post_id
                ]);
            }
        } else {
            MoPostFavorite::where('user_id', $request->user->id)
                ->where('mo_post_id', $mo_post_id)
                ->delete();
        }

        // handle user potential
        PotentialUserUtil::updatePotential(
            $request->user->id,
            $moPost->user_id,
            $mo_post_id,
            $moPost->title,
            StatusHistoryPotentialUserDefineCode::TYPE_FROM_LIKE
        );

        HistoryPotentialUser::create([
            'user_guest_id' => $request->user->id,
            'user_host_id' => $moPost->user_id,
            'value_reference' => $request->mo_post_id,
            'type_from' => StatusHistoryPotentialUserDefineCode::TYPE_FROM_LIKE,
            'title' => $moPost->title
        ]);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPost,
        ]);
    }
}
