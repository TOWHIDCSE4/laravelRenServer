<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\Motel;
use App\Models\MotelFavorite;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group User/Cộng đồng/Phòng yêu thích
 */

class MotelFavoriteController extends Controller
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
        $ids = MotelFavorite::where('user_id', $request->user->id)->get()
            ->pluck('motel_id');

        $all = Motel::whereIn('id', $ids)
            ->search(request('search'))
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
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
    public function favorite(Request $request, $motel_id)
    {

        $motel = Motel::where('id', $motel_id)->first();

        if ($motel == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        $is_favorite = filter_var($request->is_favorite, FILTER_VALIDATE_BOOLEAN);

        if ($is_favorite  == true) {
            $has = MotelFavorite::where('user_id', $request->user->id)
                ->where('motel_id', $motel_id)
                ->first();

            if ($has == null) {
                MotelFavorite::create([
                    'user_id' => $request->user->id,
                    'motel_id' =>  $motel_id
                ]);
            }
        } else {
            MotelFavorite::where('user_id', $request->user->id)
                ->where('motel_id', $motel_id)
                ->delete();
        }

        $amountFavorites = MotelFavorite::where('motel_id', $motel_id)
            ->count();
        $motel->total_motel_likes = $amountFavorites;


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motel,
        ]);
    }

    /**
     * 
     * Lấy số lượng yêu thích của phòng
     * 
     * @queryParam is_favorite yêu thích hay không
     * 
     */
    public function getFavoritesMotel(Request $request, $motel_id)
    {

        $motel = Motel::where('id', $motel_id);
        if (!$motel->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        $amountFavorites = MotelFavorite::where('motel_id', $motel_id)
            ->count();
        $motel->total_motel_likes = $amountFavorites;

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motel,
        ]);
    }
}
