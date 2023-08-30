<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\AccountRankDefineCode;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Models\MoPostFindMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoPostFindMotelController extends Controller
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

        $moPostFindMotels = MoPostFindMotel::where(function ($query) use ($request) {
            if (filter_var($request->is_all ?: false, FILTER_VALIDATE_BOOLEAN)) {
                $query->where('mo_post_find_motels.status', StatusMoPostDefineCode::COMPLETED);
            } else {
                $query->where('mo_post_find_motels.user_id', $request->user->id);
                $query->when(request('status') != null, function ($query) {
                    $query->where('mo_post_find_motels.status', request('status'));
                });
            }
        })
            ->when(request('money_from') != null, function ($query) {
                $query->where('mo_post_find_motels.money_from', '>=', request('money_from'));
            })
            ->when(request('money_to') != null, function ($query) {
                $query->where('mo_post_find_motels.money_to', '<=', request('money_to'));
            })
            ->when(request('status') != null, function ($query) {
                $query->where('mo_post_find_motels.status', request('status'));
            })
            ->when(request('sex') != null, function ($query) {
                $query->where('mo_post_find_motels.sex', request('sex'));
            })
            ->when(request('number_floor') != null, function ($query) {
                $query->where('mo_post_find_motels.number_floor', request('number_floor'));
            })
            ->when(request('quantity_vehicle_parked') != null, function ($query) {
                $query->where('mo_post_find_motels.quantity_vehicle_parked', request('quantity_vehicle_parked'));
            })
            ->when(request('province') != null, function ($query) {
                $query->where('mo_post_find_motels.province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('mo_post_find_motels.district', request('district'));
            })
            ->when(request('wards') != null, function ($query) {
                $query->where('mo_post_find_motels.wards', request('wards'));
            })
            ->when(request('has_wc') != null, function ($query) {
                $query->where('mo_post_find_motels.has_wc', filter_var(request('has_wc'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
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
                $query->join('users', 'mo_post_find_motels.user_id', '=', 'users.id');
                $query->where('users.phone_number', request('phone_number'));
                $query->select('mo_post_find_motels.*');
            })
            ->when(request('is_my_post') != null && $request->user != null, function ($query) use ($request) {
                $query->where('mo_post_find_motels.user_id', $request->user->id);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('mo_post_find_motels', $sortBy), function ($query) use ($sortBy, $descending) {
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
            'data' => $moPostFindMotels,
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
        $mo_post_find_motel_id = request("mo_post_find_motel_id");

        $moPostFindMotels = MoPostFindMotel::where('id', $mo_post_find_motel_id)->first();
        if ($moPostFindMotels == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostFindMotels,
        ]);
    }

    /**
     * 
     * Thêm 1 phòng đăng tìm phòng trọ
     * 
     * @bodyParam user_id
     * @bodyParam motel_id
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam motel_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam images array danh sách link ảnh
     * @bodyParam sex int  0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
     * @bodyParam mo_services array chứa các dịch vụ phòng
     * @bodyParam deposit double đặt cọc 
     * @bodyParam electric_money double tiền điện - 0 là free
     * @bodyParam water_money double tiền nước  tiền nước - 0 là free
     * @bodyParam has_wifi có wifi ko 
     * @bodyParam wifi_money có
     * @bodyParam has_park có
     * @bodyParam park_money có
     * @bodyParam province có
     * @bodyParam district có
     * @bodyParam wards có
     * @bodyParam address_detail có 
     * @bodyParam has_wc có
     * @bodyParam has_window có
     * @bodyParam has_security có
     * @bodyParam has_free_move có
     * @bodyParam has_own_owner có
     * @bodyParam has_air_conditioner có
     * @bodyParam has_water_heater có 
     * @bodyParam has_kitchen có
     * @bodyParam has_fridge có
     * @bodyParam has_washing_machine có
     * @bodyParam has_mezzanine có
     * @bodyParam has_bed có
     * @bodyParam has_wardrobe có
     * @bodyParam has_tivi có
     * @bodyParam has_pet có
     * @bodyParam has_balcony có
     * @bodyParam hour_open có
     * @bodyParam minute_open có
     * @bodyParam hour_close có
     * @bodyParam minute_close có
     * @bodyParam number_floor number
     * @bodyParam quantity_vehicle_parked number
     * 
     */
    public function create(Request $request)
    {
        $availableMotel = false;
        $adminVerified = 0;
        $statusPost = 0;

        // check place
        if ($request->province != null && Place::getNameProvince($request->province) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ]);
        }

        if ($request->district != null && Place::getNameDistrict($request->district) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ]);
        }

        if ($request->wards != null && Place::getNameWards($request->wards) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ]);
        }

        if ($request->images != null && !is_array($request->images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }
        // if (count($request->images) < 2) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[0],
        //         'msg' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[1],
        //     ]);
        // }

        // if (count($request->images) > 0) {
        //     foreach ($request->images as $imageItem) {
        //         if ($imageItem == null || empty($imageItem)) {
        //             return ResponseUtils::json([
        //                 'code' => Response::HTTP_BAD_REQUEST,
        //                 'success' => false,
        //                 'msg_code' => MsgCode::INVALID_IMAGES[0],
        //                 'msg' => MsgCode::INVALID_IMAGES[1],
        //             ]);
        //         }
        //     }
        // }

        if ($request->furniture != null && !is_array($request->furniture)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_FURNITURE[0],
                'msg' => MsgCode::INVALID_LIST_FURNITURE[1],
            ]);
        }

        if ($request->furniture != null && is_array($request->furniture)) {
            foreach ($request->furniture as $furnitureItem) {
                if (empty($furnitureItem['name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_FURNITURE_IS_REQUIRED[0],
                        'msg' => MsgCode::NAME_FURNITURE_IS_REQUIRED[1],
                    ]);
                }
                if (empty($furnitureItem['quantity'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::QUANTITY_IS_REQUIRED[0],
                        'msg' => MsgCode::QUANTITY_IS_REQUIRED[1],
                    ]);
                }

                if ($furnitureItem['quantity'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_QUANTITY[0],
                        'msg' => MsgCode::INVALID_QUANTITY[1],
                    ]);
                }

                if (!is_array($furnitureItem['images'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
        }

        if ($request->user->is_admin == true) {
            $adminVerified = true;
            $statusPost = StatusMoPostDefineCode::COMPLETED;
        }
        //  else if ($request->user->host_rank == HostRankDefineCode::VIP) {
        //     $statusPost = StatusMoPostDefineCode::PROCESSING;
        //     $adminVerified = true;
        // } 
        else {
            $statusPost = StatusMoPostDefineCode::PROCESSING;
            $adminVerified = false;
        }

        // check account rank post
        $countPostInDay = DB::table('mo_post_find_motels')->where([
            ['user_id', $request->user->id],
            // ['account_rank', $request->user->account_rank]
        ])
            ->count();

        // if ($request->user->account_rank == AccountRankDefineCode::NORMAL && $countPostInDay >= 1) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_NORMAL[0],
        //         'msg' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_NORMAL[1],
        //     ]);
        // }
        // if ($request->user->account_rank == AccountRankDefineCode::LOYAL && $countPostInDay >= 3) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_LOYAL[0],
        //         'msg' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_LOYAL[1],
        //     ]);
        // }
        // if ($request->user->account_rank == AccountRankDefineCode::VIP && $countPostInDay >= 6) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_VIP[0],
        //         'msg' => MsgCode::LIMIT_A_POST_WITH_ACCOUNT_RANK_VIP[1],
        //     ]);
        // }

        $moPostFindMotelExists = MoPostFindMotel::create([
            "user_id" => $request->user->id,
            "motel_id"  => isset($request->motel_id) ? $request->motel_id : null,
            "phone_number"  => $request->phone_number,
            "title" => $request->title,
            "description"  => $request->description,
            "motel_name"  => $request->motel_name,
            "capacity"  => $request->capacity,
            "images"  => json_encode($request->images),
            "mo_services"  => json_encode($request->mo_services),
            "furniture"  => $request->furniture != null ? json_encode($request->furniture) : json_encode([]),
            "sex"  => $request->sex,
            "area"  => $request->area,
            "money"  => $request->money,
            "money_to"  => $request->money_to,
            "money_from"  => $request->money_from,
            "deposit" => $request->deposit,
            "electric_money" => $request->electric_money,
            "water_money"  => $request->water_money,
            "has_wifi"  => $request->has_wifi,
            "wifi_money" => $request->wifi_money,
            "has_park" => $request->has_park,
            "park_money" => $request->park_money,
            "link_video" => $request->link_video,
            "type" => $request->type,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => $request->address_detail,
            "has_wc" => $request->has_wc,
            "has_window" => $request->has_window,
            "has_security" => $request->has_security,
            "has_free_move" => $request->has_free_move,
            "has_own_owner" => $request->has_own_owner,
            "has_air_conditioner" => $request->has_air_conditioner,
            "has_water_heater" => $request->has_water_heater,
            "has_kitchen" => $request->has_kitchen,
            "has_fridge" => $request->has_fridge,
            "has_washing_machine" => $request->has_washing_machine,
            "has_mezzanine" => $request->has_mezzanine,
            "has_bed" => $request->has_bed,
            "has_wardrobe" => $request->has_wardrobe,
            "has_tivi" => $request->has_tivi,
            "has_pet" => $request->has_pet,
            "has_balcony" => $request->has_balcony,
            "hour_open" => $request->hour_open,
            "minute_open" => $request->minute_open,
            "hour_close" => $request->hour_close,
            "minute_close" => $request->minute_close,
            "has_finger_print" => $request->has_finger_print,
            "has_kitchen_stuff" => $request->has_kitchen_stuff,
            "has_table" => $request->has_table,
            "has_picture" => $request->has_picture,
            "has_decorative_lights" => $request->has_decorative_lights,
            "has_tree" => $request->has_tree,
            "has_pillow" => $request->has_pillow,
            "has_mattress" => $request->has_mattress,
            "has_shoes_rasks" => $request->has_shoes_rasks,
            "has_curtain" => $request->has_curtain,
            "has_mirror" => $request->has_mirror,
            "has_sofa" => $request->has_sofa,
            "has_ceiling_fans" => $request->has_ceiling_fans,
            "number_floor" => $request->number_floor,
            "quantity_vehicle_parked" => $request->quantity_vehicle_parked,
            "available_motel" => $availableMotel,
            "admin_verified" => $adminVerified,
            "status" => $statusPost,
            "percent_commission" => $request->percent_commission,
            "note" => $request->note,
        ]);

        // handle notification
        if ($request->user != null) {
            NotificationAdminJob::dispatch(
                null,
                "Thông báo bài đăng tìm phòng mới",
                'Có bài đăng tìm phòng mới cần duyệt từ tài khoản ' . $request->user->name,
                TypeFCM::NEW_MO_POST_FIND_MOTEL,
                NotiUserDefineCode::USER_IS_ADMIN,
                $moPostFindMotelExists->id,
            );
        }

        // update user
        // if ($request->user->has_post == false) {
        //     // $userExist = User::join('mo_post_find_motels', 'users.id', '=', 'mo_post_find_motels.user_id')
        //     //     ->where([
        //     //         ['users.id', $request->user->id],
        //     //         ['mo_post_find_motels.id', $motel_created->id]
        //     //     ])
        //     //     ->first();
        //     // if ($userExist != null) {
        //     //     $userExist->update([
        //     //         'has_post' => true
        //     //     ]);
        //     // }

        //     $request->user->update([
        //         'has_post' => true
        //     ]);
        // }

        // if ($moPostFindMotelExists != null) {
        //     $moPostFindMotelExists->update([
        //         'has_post' => true
        //     ]);
        // }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostFindMotelExists,
        ]);
    }

    /**
     * Cập nhật bài đăng tìm 1 phòng trọ
     * 
     * @bodyParam user_id
     * @bodyParam motel_id
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam motel_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam sex int  0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
     * @bodyParam deposit double đặt cọc 
     * @bodyParam electric_money double tiền điện - 0 là free
     * @bodyParam water_money double tiền nước  tiền nước - 0 là free
     * @bodyParam images array danh sách ảnh
     * @bodyParam mo_services array danh sách dịch vụ phòng
     * @bodyParam has_wifi có wifi ko 
     * @bodyParam wifi_money có
     * @bodyParam has_park có
     * @bodyParam park_money có
     * @bodyParam province có
     * @bodyParam district có
     * @bodyParam wards có
     * @bodyParam address_detail có 
     * @bodyParam has_wc có
     * @bodyParam has_window có
     * @bodyParam has_security có
     * @bodyParam has_free_move có
     * @bodyParam has_own_owner có
     * @bodyParam has_air_conditioner có
     * @bodyParam has_water_heater có 
     * @bodyParam has_kitchen có
     * @bodyParam has_fridge có
     * @bodyParam has_washing_machine có
     * @bodyParam has_mezzanine có
     * @bodyParam has_bed có
     * @bodyParam has_wardrobe có
     * @bodyParam has_tivi có
     * @bodyParam has_pet có
     * @bodyParam has_balcony có
     * @bodyParam hour_open có
     * @bodyParam minute_open có
     * @bodyParam hour_close có
     * @bodyParam minute_close có
     * @bodyParam number_floor number
     * @bodyParam quantity_vehicle_parked number
     * @bodyParam number_floor number
     * @bodyParam quantity_vehicle_parked number
     * 
     */
    public function update(Request $request)
    {
        $moPostFindMotelId = request("mo_post_find_motel_id");
        $adminVerified = 0;
        $statusPost = 0;

        $moPostFindMotelExists = MoPostFindMotel::where('id', $moPostFindMotelId)
            ->where(function ($query) use ($request) {
                if (!$request->user->is_admin) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($moPostFindMotelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[1],
            ], 404);
        }

        if ($request->images != null || !empty($request->images)) {
            if (!is_array($request->images)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }

            if (count($request->images) > 0) {
                foreach ($request->images as $imageItem) {
                    if ($imageItem == null || empty($imageItem)) {
                        return ResponseUtils::json([
                            'code' => Response::HTTP_BAD_REQUEST,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_IMAGES[0],
                            'msg' => MsgCode::INVALID_IMAGES[1],
                        ]);
                    }
                }
            }
        }

        if (StatusMoPostDefineCode::getStatusMoPostCode($request->status, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ]);
        }

        $moPostSameTitle = DB::table('mo_post_find_motels')->where([
            ['title', $request->title],
            ['id', '!=', $moPostFindMotelId],
        ])
            ->where(function ($query) use ($request) {
                if (!$request->user->is_admin) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($moPostSameTitle != null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ]);
        }

        if ($request->furniture != null && !is_array($request->furniture)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_FURNITURE[0],
                'msg' => MsgCode::INVALID_LIST_FURNITURE[1],
            ]);
        }

        if ($request->furniture != null && is_array($request->furniture)) {
            foreach ($request->furniture as $furnitureItem) {
                if (empty($furnitureItem['name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_FURNITURE_IS_REQUIRED[0],
                        'msg' => MsgCode::NAME_FURNITURE_IS_REQUIRED[1],
                    ]);
                }
                if (empty($furnitureItem['quantity'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::QUANTITY_IS_REQUIRED[0],
                        'msg' => MsgCode::QUANTITY_IS_REQUIRED[1],
                    ]);
                }

                if ($furnitureItem['quantity'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_QUANTITY[0],
                        'msg' => MsgCode::INVALID_QUANTITY[1],
                    ]);
                }

                if (!is_array($furnitureItem['images'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
        }

        // if ($request->user->is_admin == true) {
        //     $adminVerified = true;
        //     $statusPost = StatusMoPostDefineCode::COMPLETED;
        // }
        // // else if ($request->user->host_rank == HostRankDefineCode::VIP) {
        // //     $statusPost = StatusMoPostDefineCode::PROCESSING;
        // //     $adminVerified = true;
        // // } 
        // else {
        //     $statusPost = StatusMoPostDefineCode::PROCESSING;
        //     $adminVerified = false;
        // }

        $moPostFindMotelExists->update([
            "phone_number"  => $request->phone_number ?? $moPostFindMotelExists->phone_number,
            "title" => $request->title ?? $moPostFindMotelExists->title,
            "description"  => $request->description ?? $moPostFindMotelExists->description,
            "motel_name"  => $request->motel_name ?? $moPostFindMotelExists->motel_name,
            "capacity"  => $request->capacity ?? $moPostFindMotelExists->capacity,
            "sex"  => $request->sex ?? $moPostFindMotelExists->sex,
            "area"  => $request->area ?? $moPostFindMotelExists->area,
            "available_motel"  => $request->available_motel ?? $moPostFindMotelExists->available_motel,
            "money"  => $request->money ?? $moPostFindMotelExists->money,
            "money_to"  => $request->money_to ?? $moPostFindMotelExists->money_to,
            "money_from"  => $request->money_from ?? $moPostFindMotelExists->money_from,
            'status' => $request->status ?? $moPostFindMotelExists->status,
            "deposit" => $request->deposit ?? $moPostFindMotelExists->deposit,
            "mo_services"  => json_encode($request->mo_services),
            "furniture"  => json_encode($request->furniture),
            "electric_money" => $request->electric_money ?? $moPostFindMotelExists->electric_money,
            "water_money"  => $request->water_money ?? $moPostFindMotelExists->water_money,
            "has_wifi"  => $request->has_wifi ?? $moPostFindMotelExists->has_wifi,
            "wifi_money" => $request->wifi_money ?? $moPostFindMotelExists->wifi_money,
            "has_park" => $request->has_park ?? $moPostFindMotelExists->has_park,
            "park_money" => $request->park_money ?? $moPostFindMotelExists->park_money,
            "province" => $request->province ?? $moPostFindMotelExists->province,
            "district" => $request->district ?? $moPostFindMotelExists->district,
            "wards" => $request->wards ?? $moPostFindMotelExists->wards,
            "type" => $request->type ?? $moPostFindMotelExists->type,
            "link_video" => $request->link_video ?? $moPostFindMotelExists->link_video,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => $request->address_detail ?? $moPostFindMotelExists->address_detail,
            "images"  => json_encode($request->images),
            "has_wc" => $request->has_wc ?? $moPostFindMotelExists->has_wc,
            "has_window" => $request->has_window ?? $moPostFindMotelExists->has_window,
            "has_security" => $request->has_security ?? $moPostFindMotelExists->has_security,
            "has_free_move" => $request->has_free_move ?? $moPostFindMotelExists->has_free_move,
            "has_own_owner" => $request->has_own_owner ?? $moPostFindMotelExists->has_own_owner,
            "has_air_conditioner" => $request->has_air_conditioner ?? $moPostFindMotelExists->has_air_conditioner,
            "has_water_heater" => $request->has_water_heater ?? $moPostFindMotelExists->has_water_heater,
            "has_kitchen" => $request->has_kitchen ?? $moPostFindMotelExists->has_kitchen,
            "has_fridge" => $request->has_fridge ?? $moPostFindMotelExists->has_fridge,
            "has_washing_machine" => $request->has_washing_machine ?? $moPostFindMotelExists->has_washing_machine,
            "has_mezzanine" => $request->has_mezzanine ?? $moPostFindMotelExists->has_mezzanine,
            "has_bed" => $request->has_bed ?? $moPostFindMotelExists->has_bed,
            "has_wardrobe" => $request->has_wardrobe ?? $moPostFindMotelExists->has_wardrobe,
            "has_tivi" => $request->has_tivi ?? $moPostFindMotelExists->has_tivi,
            "has_pet" => $request->has_pet ?? $moPostFindMotelExists->has_pet,
            "has_balcony" => $request->has_balcony ?? $moPostFindMotelExists->has_balcony,
            "has_finger_print" => $request->has_finger_print ?? $moPostFindMotelExists->has_finger_print,
            "has_kitchen_stuff" => $request->has_kitchen_stuff ?? $moPostFindMotelExists->has_kitchen_stuff,
            "has_table" => $request->has_table ?? $moPostFindMotelExists->has_table,
            "has_picture" => $request->has_picture ?? $moPostFindMotelExists->has_picture,
            "has_decorative_lights" => $request->has_decorative_lights ?? $moPostFindMotelExists->has_decorative_lights,
            "has_tree" => $request->has_tree ?? $moPostFindMotelExists->has_tree,
            "has_pillow" => $request->has_pillow ?? $moPostFindMotelExists->has_pillow,
            "has_mattress" => $request->has_mattress ?? $moPostFindMotelExists->has_mattress,
            "has_shoes_rasks" => $request->has_shoes_rasks ?? $moPostFindMotelExists->has_shoes_rasks,
            "has_curtain" => $request->has_curtain ?? $moPostFindMotelExists->has_curtain,
            "has_mirror" => $request->has_mirror ?? $moPostFindMotelExists->has_mirror,
            "has_sofa" => $request->has_sofa ?? $moPostFindMotelExists->has_sofa,
            "has_ceiling_fans" => $request->has_ceiling_fans ?? $moPostFindMotelExists->has_ceiling_fans,
            "hour_open" => $request->hour_open ?? $moPostFindMotelExists->hour_open,
            "minute_open" => $request->minute_open ?? $moPostFindMotelExists->minute_open,
            "hour_close" => $request->hour_close ?? $moPostFindMotelExists->hour_close,
            "minute_close" => $request->minute_close ?? $moPostFindMotelExists->minute_close,
            "quantity_vehicle_parked" => $request->quantity_vehicle_parked ?? $moPostFindMotelExists->quantity_vehicle_parked,
            "number_floor" => $request->number_floor ?? $moPostFindMotelExists->number_floor,
            "admin_verified" => $adminVerified,
            "percent_commission" => $request->percent_commission ?? $moPostFindMotelExists->percent_commission,
            "note" => $request->note ?? $moPostFindMotelExists->note,
        ]);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostFindMotelExists,
        ]);
    }

    /**
     * Xóa bài đăng tìm 1 phòng
     * 
     * @queryParam mo_post_roommate_id
     * 
     */
    public function delete(Request $request)
    {
        $moPostFindMotelId = request("mo_post_find_motel_id");

        $moPostFindMotelExists = MoPostFindMotel::where('id', $moPostFindMotelId)
            ->where('user_id', $request->user->id)
            ->first();

        if ($moPostFindMotelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[1],
            ], 404);
        }


        $moPostFindMotelExists->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    // /**
    //  * 
    //  * Bài đăng tương tự
    //  * 
    //  * @urlParam id bài dăng
    //  * 
    //  */
    // public function getSimilarPost(Request $request)
    // {
    //     $mo_post_id = request("mo_post_id");
    //     $limit = $request->limit ?: 20;

    //     $moPostCompareExist = DB::table('mo_post_find_motels')->where('id', $mo_post_id)->first();
    //     if ($moPostCompareExist == null) {
    //         return ResponseUtils::json([
    //             'code' => Response::HTTP_NOT_FOUND,
    //             'success' => false,
    //             'msg_code' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[0],
    //             'msg' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[1],
    //         ]);
    //     }

    //     $listMoPost = MoPost::where([
    //         ['province', $moPostCompareExist->province],
    //         ['district', $moPostCompareExist->district],
    //         // ['wards', $moPostCompareExist->wards],
    //         ['type', $moPostCompareExist->type],
    //         ['id', '<>', $moPostCompareExist->id],
    //     ])
    //         ->paginate($limit);

    //     return ResponseUtils::json([
    //         'code' => Response::HTTP_OK,
    //         'success' => true,
    //         'msg_code' => MsgCode::SUCCESS[0],
    //         'msg' => MsgCode::SUCCESS[1],
    //         'data' => $listMoPost,
    //     ]);
    // }
}
