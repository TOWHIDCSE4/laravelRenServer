<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusMoPostDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MoPostRoommate;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoPostRoommateController extends Controller
{
    /**
     * 
     * Danh cách phòng
     * 
     * @queryParam title tìm theo tiêu đề
     * @queryParam money_from tiền tối thiểu
     * @queryParam money_to tiền tối đa
     * @queryParam province tỉnh
     * @queryParam district quận
     * @queryParam wards huyện
     * @queryParam sex giới tính
     * @queryParam has boolean tất cả field có kiểu bool
     * @queryParam sort_by string tên column
     * @queryParam search string 
     * @queryParam descending boolean
     * 
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $listTypeMotels = $request->list_type != null ? json_decode($request->list_type) : null;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $moPostRoommates = MoPostRoommate::when(request('money_from') != null, function ($query) {
            $query->where('money', '>', request('money_from'));
        })
            ->when(request('money_to') != null, function ($query) {
                $query->where('money', '<', request('money_to'));
            })
            ->when(request('status') != null, function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('sex') != null, function ($query) {
                $query->where('sex', request('sex'));
            })
            ->when(request('number_floor') != null, function ($query) {
                $query->where('number_floor', request('number_floor'));
            })
            ->when(request('quantity_vehicle_parked') != null, function ($query) {
                $query->where('quantity_vehicle_parked', request('quantity_vehicle_parked'));
            })
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when(request('wards') != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->when(request('has_wc') != null, function ($query) {
                $query->where('has_wc', filter_var(request('has_wc'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wifi') != null, function ($query) {
                $query->where('has_wifi', filter_var(request('has_wifi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_park') != null, function ($query) {
                $query->where('has_park', filter_var(request('has_park'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_window') != null, function ($query) {
                $query->where('has_window', filter_var(request('has_window'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_security') != null, function ($query) {
                $query->where('has_security', filter_var(request('has_security'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_free_move') != null, function ($query) {
                $query->where('has_free_move', filter_var(request('has_free_move'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_own_owner') != null, function ($query) {
                $query->where('has_own_owner', filter_var(request('has_own_owner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_air_conditioner') != null, function ($query) {
                $query->where('has_air_conditioner', filter_var(request('has_air_conditioner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_water_heater') != null, function ($query) {
                $query->where('has_water_heater', filter_var(request('has_water_heater'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen') != null, function ($query) {
                $query->where('has_kitchen', filter_var(request('has_kitchen'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_fridge') != null, function ($query) {
                $query->where('has_fridge', filter_var(request('has_fridge'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_washing_machine') != null, function ($query) {
                $query->where('has_washing_machine', filter_var(request('has_washing_machine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mezzanine') != null, function ($query) {
                $query->where('has_mezzanine', filter_var(request('has_mezzanine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wardrobe') != null, function ($query) {
                $query->where('has_wardrobe', filter_var(request('has_wardrobe'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tivi') != null, function ($query) {
                $query->where('has_tivi', filter_var(request('has_tivi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pet') != null, function ($query) {
                $query->where('has_pet', filter_var(request('has_pet'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_balcony') != null, function ($query) {
                $query->where('has_balcony', filter_var(request('has_balcony'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_finger_print') != null, function ($query) {
                $query->where('has_finger_print', filter_var(request('has_finger_print'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen_stuff') != null, function ($query) {
                $query->where('has_kitchen_stuff', filter_var(request('has_kitchen_stuff'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_table') != null, function ($query) {
                $query->where('has_table', filter_var(request('has_table'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_picture') != null, function ($query) {
                $query->where('has_picture', filter_var(request('has_picture'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_decorative_lights') != null, function ($query) {
                $query->where('has_decorative_lights', filter_var(request('has_decorative_lights'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tree') != null, function ($query) {
                $query->where('has_tree', filter_var(request('has_tree'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pillow') != null, function ($query) {
                $query->where('has_pillow', filter_var(request('has_pillow'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mattress') != null, function ($query) {
                $query->where('has_mattress', filter_var(request('has_mattress'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_shoes_rasks') != null, function ($query) {
                $query->where('has_shoes_rasks', filter_var(request('has_shoes_rasks'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_curtain') != null, function ($query) {
                $query->where('has_curtain', filter_var(request('has_curtain'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mirror') != null, function ($query) {
                $query->where('has_mirror', filter_var(request('has_mirror'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_sofa') != null, function ($query) {
                $query->where('has_sofa', filter_var(request('has_sofa'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_ceiling_fans') != null, function ($query) {
                $query->where('has_ceiling_fans', filter_var(request('has_ceiling_fans'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when($listTypeMotels != null && is_array($listTypeMotels), function ($query) use ($listTypeMotels) {
                $query->whereIn('type', $listTypeMotels);
            })
            ->when(request('phone_number') != null, function ($query) {
                $query->join('users', 'mo_post_roommates.user_id', '=', 'users.id');
                $query->where('users.phone_number', request('phone_number'));
                $query->select('mo_post_roommates.*');
            })
            ->when(request('is_my_post') != null && $request->user != null, function ($query) use ($request) {
                $query->where('mo_post_roommates.user_id', $request->user->id);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('mo_post_roommates', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search, null, true, true);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostRoommates,
        ]);
    }


    /**
     * 
     * Thông tin 1 bài đăng
     * 
     * @urlParam id bài dăng
     * 
     */
    public function getOne(Request $request)
    {
        $mo_post_roommate_id = request("mo_post_roommate_id");

        $moPostRoommate = MoPostRoommate::where('id', $mo_post_roommate_id)->first();
        if ($moPostRoommate == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        // if ($request->user != null) {
        //     //update last seen mo post
        //     $lengthListLastSeen = DB::table('last_seen_mo_posts')
        //         ->where('user_id', $request->user->id)->count();

        //     if ($lengthListLastSeen > 1) {
        //         LastSeenMoPost::where('user_id', $request->user->id)
        //             ->orderBy('created_at', 'asc')
        //             ->first()
        //             ->delete();
        //     }

        //     LastSeenMoPost::create([
        //         'mo_post_roommate_id' => request("mo_post_roommate_id"),
        //         'user_id' => $request->user->id
        //     ]);

        //     $moPostRoommate->previous_mo_post_roommate_id = DB::table('last_seen_mo_posts')
        //         ->where('user_id', $request->user->id)
        //         ->orderBy('created_at', 'asc')
        //         ->first()->mo_post_roommate_id;
        // }

        // $moPostRoommate->user = User::where('id', $moPostRoommate->user_id)->first();

        //update view a post
        // if ($request->user != null) {

        //     $viewUserExist = ViewerPost::where([
        //         ['user_id', $request->user->id],
        //         ['mo_post_roommate_id', $mo_post_roommate_id]
        //     ])
        //         ->first();

        //     if ($viewUserExist == null) {

        //         ViewerPost::create([
        //             'mo_post_roommate_id' => $mo_post_roommate_id,
        //             'user_id' => $request->user->id
        //         ]);
        //     }
        // }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostRoommate,
        ]);
    }

    /**
     * Cập nhật bài đăng tìm 1 phòng trọ
     * 
     * @bodyParam status int trạng thái
     * 
     */
    public function update(Request $request)
    {
        $post_id = request("mo_post_roommate_id");

        $mpPostRoommateExists = MoPostRoommate::where('id', $post_id)->first();

        if ($mpPostRoommateExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        if (StatusMoPostDefineCode::getStatusMoPostCode($request->status, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ]);
        }

        $mpPostRoommateExists->update([
            "status" => $request->status,
        ]);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $mpPostRoommateExists,
        ]);
    }
    /**
     * Cập nhật bài đăng tìm 1 phòng trọ
     * 
     * @bodyParam status int trạng thái
     * 
     */
    public function updateStatus(Request $request)
    {
        $post_id = request("mo_post_roommate_id");

        $mpPostRoommateExists = MoPostRoommate::where('id', $post_id)->first();

        if ($mpPostRoommateExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        if (StatusMoPostDefineCode::getStatusMoPostCode($request->status, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ]);
        }

        $mpPostRoommateExists->update([
            "status" => $request->status,
        ]);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $mpPostRoommateExists,
        ]);
    }

    /**
     * 
     * Bài đăng tương tự
     * 
     * @urlParam id bài dăng
     * 
     */
    public function getSimilarPost(Request $request)
    {
        $mo_post_roommate_id = request("mo_post_roommate_id");
        $limit = $request->limit ?: 20;

        $moPostCompareExist = DB::table('mo_post_roommates')->where('id', $mo_post_roommate_id)->first();
        if ($moPostCompareExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        $listMoPost = MoPostRoommate::where([
            ['province', $moPostCompareExist->province],
            ['district', $moPostCompareExist->district],
            // ['wards', $moPostCompareExist->wards],
            ['type', $moPostCompareExist->type],
            ['id', '<>', $moPostCompareExist->id],
        ])
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listMoPost,
        ]);
    }

    /**
     * Xóa bài đăng tìm 1 phòng trọ
     * 
     * @queryParam mo_post_roommate_id
     * 
     */
    public function delete(Request $request)
    {
        $post_id = request("mo_post_roommate_id");

        $modelPostExists = MoPostRoommate::where('id', $post_id)
            ->first();

        if ($modelPostExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_FIND_ROOMMATE_EXISTS[0],
                'msg' => MsgCode::NO_POST_FIND_ROOMMATE_EXISTS[1],
            ], 404);
        }

        $modelPostExists->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
