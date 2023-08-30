<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\MotelUtils;
use App\Helper\NotiUserDefineCode;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MoPost;
use App\Models\Motel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Helper\ParamUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationAdminJob;
use App\Models\MoPostMultipleMotel;
use App\Models\Tower;
use App\Models\User;
use App\Models\ViewerPost;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @group User/Quản lý/Bài đăng tìm phòng trọ
 */

class MotelPostController extends Controller
{
    /**
     * 
     * Danh cách phòng đăng tìm phòng trọ
     * 
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
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }
        $moPost = MoPost::where(function ($query) use ($request) {
            if ($request->user != null && $request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                        ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('towers.id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->when($request->user != null && $request->user->is_admin == true && $request->user_id != null, function ($query) {
                $query->where('user_id', request('user_id'));
            })
            ->when(request('money_from') != null, function ($query) {
                $query->where('money', '>=', request('money_from'));
            })
            ->when(request('money_to') != null, function ($query) {
                $query->where('money', '<=', request('money_to'));
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
            ->when(request('has_wifi') != null, function ($query) {
                $query->where('has_wifi', request('has_wifi')  ? 1 : 0);
            })
            ->when(request('has_park') != null, function ($query) {
                $query->where('has_park', request('has_park')  ? 1 : 0);
            })
            ->when(request('has_window') != null, function ($query) {
                $query->where('has_window', request('has_window')  ? 1 : 0);
            })
            ->when(request('has_security') != null, function ($query) {
                $query->where('has_security', request('has_security')  ? 1 : 0);
            })
            ->when(request('has_free_move') != null, function ($query) {
                $query->where('has_free_move', request('has_free_move')  ? 1 : 0);
            })
            ->when(request('has_own_owner') != null, function ($query) {
                $query->where('has_own_owner', request('has_own_owner')  ? 1 : 0);
            })
            ->when(request('has_air_conditioner') != null, function ($query) {
                $query->where('has_air_conditioner', request('has_air_conditioner')  ? 1 : 0);
            })
            ->when(request('has_water_heater') != null, function ($query) {
                $query->where('has_water_heater', request('has_water_heater')  ? 1 : 0);
            })
            ->when(request('has_kitchen') != null, function ($query) {
                $query->where('has_kitchen', request('has_kitchen')  ? 1 : 0);
            })
            ->when(request('has_fridge') != null, function ($query) {
                $query->where('has_fridge', request('has_fridge')  ? 1 : 0);
            })
            ->when(request('has_washing_machine') != null, function ($query) {
                $query->where('has_washing_machine', request('has_washing_machine')  ? 1 : 0);
            })
            ->when(request('has_mezzanine') != null, function ($query) {
                $query->where('has_mezzanine', request('has_mezzanine')  ? 1 : 0);
            })
            ->when(request('has_wardrobe') != null, function ($query) {
                $query->where('has_wardrobe', request('has_wardrobe')  ? 1 : 0);
            })
            ->when(request('has_tivi') != null, function ($query) {
                $query->where('has_tivi', request('has_tivi')  ? 1 : 0);
            })
            ->when(request('has_pet') != null, function ($query) {
                $query->where('has_pet', request('has_pet')  ? 1 : 0);
            })
            ->when(request('has_balcony') != null, function ($query) {
                $query->where('has_balcony', request('has_balcony')  ? 1 : 0);
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
            ->when(request('type') != null, function ($query) {
                $query->where('type', request('type'));
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('mo_posts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when(request('search') != null, function ($query) {
                $query->search(request('search'));
            })
            ->paginate($limit);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPost,
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
        $now = Helper::getTimeNowDateTime();
        $availableMotel = false;
        $adminVerified = 0;
        $statusPost = 0;
        $motelExists = null;
        $tower = null;
        $arrPostMultipleMotel = [];
        // if ($motelExist == null) {
        //     return ResponseUtils::json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
        //         'msg' => MsgCode::NO_MOTEL_EXISTS[1],
        //     ]);
        // }

        if (isset($request->motel_id)) {
            $motelExists = Motel::where(
                [
                    ['id', $request->motel_id]
                ]
            )
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin == false) {
                        $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                                ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('towers.id');
                            $q->whereIn('motels.tower_id', $towerIds);
                        });
                    }
                })
                ->first();

            if ($motelExists == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }

            if (DB::table('mo_posts')->where([['motel_id', $request->motel_id], ['user_id', $request->user->id]])->count() >= 1) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::PER_MOTEL_JUST_HAVE_A_POST[0],
                    'msg' => MsgCode::PER_MOTEL_JUST_HAVE_A_POST[1],
                ]);
            }

            if ($motelExists->status != StatusMotelDefineCode::MOTEL_HIRED) {
                $availableMotel = true;
            } else {
                $availableMotel = false;
            }
        }

        if ($request->user->is_host == false) {
            if (DB::table('mo_posts')->where('user_id', $request->user->id)->count() > 1) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::LIMIT_A_POST_WITH_RENTER[0],
                    'msg' => MsgCode::LIMIT_A_POST_WITH_RENTER[1],
                ]);
            }
        }

        $moPostExists = MoPost::where('title', $request->title)->where('user_id', $request->user->id)->first();
        if ($moPostExists != null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ]);
        }

        if ($request->tower_id != null) {
            $moPostTowerExists = MoPost::where('tower_id', $request->tower_id)
                ->where(function ($query) use ($request) {
                    $query->where('mo_posts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');

                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('motels.id');
                        $q->whereIn('mo_posts.motel_id', $motelIds);
                    });
                })
                ->first();

            if ($moPostTowerExists != null) {
                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::MO_POST_EXISTS[0],
                    'msg' => MsgCode::MO_POST_EXISTS[1],
                ]);
            }

            $tower = Tower::where('id', $request->tower_id)
                ->where(function ($query) use ($request) {
                    $query->where('towers.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');

                        $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                            ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('towers.id');
                        $q->whereIn('towers.id', $towerIds);
                    });
                })
                ->first();
        }

        // check place
        if (Place::getNameProvince($request->province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (Place::getNameWards($request->wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }

        if (!is_array($request->images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);

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

        // validate & check data motel
        if ($request->motel && is_array($request->motel)) {
            $listMotelId = array_column($request->motel, 'id');

            $numListMotelId = DB::table('motels')
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin != true) {
                        $query->where('motels.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                                ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('motels.id');
                            $q->whereIn('motels.id', $motelIds);
                        });
                    }
                })
                ->whereIn('motels.id', $listMotelId)
                ->count();

            if ($numListMotelId != count($request->motel)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_MOTEL_ID[0],
                    'msg' => MsgCode::INVALID_LIST_MOTEL_ID[1],
                ]);
            }

            // check motel has contract active 
            $checkMotelHasContract = DB::table('motels')->join('contracts', 'motels.id', '=', 'contracts.motel_id')
                ->whereIn('motels.id', $listMotelId)
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusContractDefineCode::COMPLETED],
                    ['contracts.rent_to', '<', $now]
                ])
                ->first();

            if ($checkMotelHasContract != null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::MOTEL_HAS_ACTIVE_CONTRACT_UNABLE_CREATE_MO_POST[0],
                    'msg' => MsgCode::MOTEL_HAS_ACTIVE_CONTRACT_UNABLE_CREATE_MO_POST[1],
                ]);
            }

            foreach ($request->motel as $motel) {
                if (!isset($motel['motel_name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_MOTEL_NAME[0],
                        'msg' => MsgCode::INVALID_MOTEL_NAME[1],
                    ]);
                }

                if (!isset($motel['number_floor']) || $motel['number_floor'] < 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_FLOOR[0],
                        'msg' => MsgCode::INVALID_FLOOR[1],
                    ]);
                }

                if (!isset($motel['area']) || $motel['area'] < 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::AREA_IS_REQUIRED[0],
                        'msg' => MsgCode::AREA_IS_REQUIRED[1],
                    ]);
                }

                if (!isset($motel['money']) || $motel['money'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::MONEY_MUST_GREATER_THAN_ZERO[0],
                        'msg' => MsgCode::MONEY_MUST_GREATER_THAN_ZERO[1],
                    ]);
                }

                array_push($arrPostMultipleMotel, [
                    'mo_post_id' => null,
                    'motel_id' => $motel['id'],
                    'motel_name' => $motel['motel_name'],
                    'floor' => $motel['number_floor'],
                    'area' => $motel['area'],
                    'money' => $motel['money'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }

        if ($request->user->is_admin == true) {
            $adminVerified = true;
            $statusPost = StatusMoPostDefineCode::COMPLETED;
        } else if ($request->user->host_rank == HostRankDefineCode::VIP) {
            $statusPost = StatusMoPostDefineCode::PROCESSING;
            $adminVerified = true;
        } else {
            $statusPost = StatusMoPostDefineCode::PROCESSING;
            $adminVerified = false;
        }


        DB::beginTransaction();
        try {

            $motel_created = MoPost::create([
                "user_id" => $tower != null ? $tower->user_id : $request->user->id,
                "tower_id"  => $request->tower_id,
                "motel_id"  => isset($request->motel_id) ? $request->motel_id : null,
                "phone_number"  => $request->phone_number,
                "title" => $request->title,
                "description"  => $request->description,
                "motel_name"  => $request->motel_name,
                "tower_name"  => $request->tower_name,
                "capacity"  => $request->capacity,
                "images"  => json_encode($request->images),
                "furniture" => $request->furniture != null ? json_encode($request->furniture) : json_encode([]),
                "mo_services"  => json_encode($request->mo_services),
                "available_motel"  => $request->available_motel ?? true,
                "sex"  => $request->sex,
                "area"  => $request->area,
                "money"  => $request->money,
                "min_money"  => $request->money,
                "max_money"  => $request->money,
                "deposit" => $request->deposit,
                "electric_money" => $request->electric_money,
                "water_money"  => $request->water_money,
                "wifi_money" => $request->wifi_money,
                "park_money" => $request->park_money,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "type" => $request->type,
                "link_video" => $request->link_video,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
                "address_detail" => $request->address_detail,
                "number_floor" => $request->number_floor,
                "has_wifi"  => $request->has_wifi ?? false,
                "has_park" => $request->has_park ?? false,
                "has_wc" => $request->has_wc ?? false,
                "has_window" => $request->has_window ?? false,
                "has_security" => $request->has_security ?? false,
                "has_free_move" => $request->has_free_move ?? false,
                "has_own_owner" => $request->has_own_owner ?? false,
                "has_air_conditioner" => $request->has_air_conditioner ?? false,
                "has_water_heater" => $request->has_water_heater ?? false,
                "has_kitchen" => $request->has_kitchen ?? false,
                "has_fridge" => $request->has_fridge ?? false,
                "has_washing_machine" => $request->has_washing_machine ?? false,
                "has_mezzanine" => $request->has_mezzanine ?? false,
                "has_bed" => $request->has_bed ?? false,
                "has_wardrobe" => $request->has_wardrobe ?? false,
                "has_tivi" => $request->has_tivi ?? false,
                "has_pet" => $request->has_pet ?? false,
                "has_balcony" => $request->has_balcony ?? false,
                "has_finger_print" => $request->has_finger_print ?? false,
                "has_kitchen_stuff" => $request->has_kitchen_stuff ?? false,
                "has_table" => $request->has_table ?? false,
                "has_picture" => $request->has_picture ?? false,
                "has_decorative_lights" => $request->has_decorative_lights ?? false,
                "has_tree" => $request->has_tree ?? false,
                "has_pillow" => $request->has_pillow ?? false,
                "has_mattress" => $request->has_mattress ?? false,
                "has_shoes_rasks" => $request->has_shoes_rasks ?? false,
                "has_curtain" => $request->has_curtain ?? false,
                "has_mirror" => $request->has_mirror ?? false,
                "has_sofa" => $request->has_sofa ?? false,
                "has_ceiling_fans" => $request->has_ceiling_fans ?? false,
                "hour_open" => $request->hour_open ?? false,
                "minute_open" => $request->minute_open ?? false,
                "hour_close" => $request->hour_close ?? false,
                "minute_close" => $request->minute_close ?? false,
                "available_motel" => $availableMotel,
                "quantity_vehicle_parked" => $request->quantity_vehicle_parked ?? false,
                "available_motel" => $availableMotel,
                "admin_verified" => $adminVerified,
                "status" => $statusPost,
                "money_commission_admin" => $request->money_commission_admin,
                "money_commission_user" => $request->money_commission_user,
                "percent_commission" => $request->percent_commission,
                "percent_commission_collaborator" => $request->percent_commission_collaborator,
                "note" => $request->note,
            ]);
            // update user
            if ($request->user->has_post == false) {
                $request->user->update([
                    'has_post' => true
                ]);
            }

            // update post
            if ($motelExists != null) {
                $motelExists->update([
                    'has_post' => true
                ]);
            }

            // handle add multiple post motel
            if (!empty($arrPostMultipleMotel)) {
                // array_walk($arrPostMultipleMotel, function (&$item, $motel_created) {
                //     dd($motel_created);
                //     $item['mo_post_id'] = $motel_created->id;
                // });
                foreach ($arrPostMultipleMotel as &$postMotelItem) {
                    $postMotelItem['mo_post_id'] = $motel_created->id;
                }
                DB::table('mo_post_multiple_motels')->insert($arrPostMultipleMotel);
            }

            // handle update price mo_post
            try {
                if (!empty($request->tower_id)) {
                    MotelUtils::handleMinMaxMoPost($request->tower_id);
                }
            } catch (\Throwable $th) {
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        // handle notification
        if ($request->user != null) {
            NotificationAdminJob::dispatch(
                null,
                "Thông báo bài đăng mới",
                'Có bài đăng mới cần duyệt từ chủ nhà ' . $request->user->name,
                TypeFCM::NEW_MO_POST,
                NotiUserDefineCode::USER_IS_ADMIN,
                $motel_created->id,
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motel_created,
        ]);
    }


    /**
     * Thong tin bài đăng tìm 
     * 
     */
    public function getOne(Request $request)
    {
        $post_id = request("mo_post_id");

        $moPost = MoPost::where(
            'id',
            $post_id
        )->where(function ($query) use ($request) {
            if ($request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('motels.tower_id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->first();

        if ($moPost == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        $moPost->user = User::where('id', $request->user->id)->first();

        //update view a post
        $viewUserExist = ViewerPost::where([
            ['user_id', $request->user->id],
            ['mo_post_id', $post_id]
        ])
            ->first();

        if ($viewUserExist == null) {
            ViewerPost::create([
                'mo_post_id' => $post_id,
                'user_id' => $request->user->id
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPost,
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
     */
    public function update(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $post_id = request("mo_post_id");
        $adminVerified = 0;
        $statusPost = 0;
        $motelExist = null;
        $arrPostMultipleMotel = [];

        $modelPostExists = MoPost::where('id', $post_id)->where(function ($query) use ($request) {
            if ($request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('motels.tower_id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->first();

        if ($modelPostExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        $moneyCommissionUserMoPost = DB::table('mo_posts')
            ->where('id', $post_id)
            ->first();


        if ($request->motel_id != null) {
            $motelExist = Motel::where(
                'id',
                $request->motel_id
            )
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin == false) {
                        $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                                ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('towers.id');
                            $q->whereIn('motels.tower_id', $towerIds);
                        });
                    }
                })
                ->first();
        }

        if ($request->tower_id != null && $modelPostExists->tower_id != $request->tower_id) {
            $moPostTowerExists = MoPost::where([
                ['tower_id', $request->tower_id],
                ['id', '<>', $post_id]
            ])
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin == false) {
                        $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                                ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('towers.id');
                            $q->whereIn('motels.tower_id', $towerIds);
                        });
                    }
                })
                ->first();
            if ($moPostTowerExists != null) {
                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::MO_POST_EXISTS[0],
                    'msg' => MsgCode::MO_POST_EXISTS[1],
                ]);
            }
        }

        // if ($motelExist == null) {
        //     return ResponseUtils::json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
        //         'msg' => MsgCode::NO_MOTEL_EXISTS[1],
        //     ]);
        // }

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


        if (StatusMoPostDefineCode::getStatusMoPostCode($request->status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ]);
        }

        $moPostExists = MoPost::where('title', $request->title)->where('id', '!=',  $post_id)
            ->where(function ($q) use ($request, $modelPostExists) {
                if ($request->user->is_admin == false) {
                    $q->where('user_id', $request->user->id);
                }
            })
            ->first();
        if ($moPostExists != null) {
            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ]);
        }

        if ($request->user->is_admin == false) {
            $contractExist = DB::table('contracts')->where([
                ['status', '<>', StatusContractDefineCode::TERMINATION],
                ['motel_id', $modelPostExists->motel_id],
                ['user_id', $modelPostExists->user_id]
            ])->first();

            if ($contractExist != null) {
                if ($contractExist->status == StatusContractDefineCode::COMPLETED && $request->status == StatusMoPostDefineCode::PROCESSING) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[0],
                        'msg' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[1],
                    ]);
                }
            }
        } else if ($request->user->is_host == true) {
            $contractExist = DB::table('contracts')->where([
                ['status', '<>', StatusContractDefineCode::TERMINATION],
                ['motel_id', $modelPostExists->motel_id],
                ['user_id', $modelPostExists->user_id]
            ])->first();

            if ($contractExist != null) {
                if ($contractExist->status == StatusContractDefineCode::COMPLETED && $request->status == StatusMoPostDefineCode::PROCESSING) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[0],
                        'msg' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[1],
                    ]);
                }
            }
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

        // validate & check data motels
        if ($request->motel && is_array($request->motel)) {
            $listMotelId = array_column($request->motel, 'id');

            $numListMotelId = DB::table('motels')
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin == false) {
                        $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                                ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('towers.id');
                            $q->whereIn('motels.tower_id', $towerIds);
                        });
                    }
                })
                ->whereIn('id', $listMotelId)
                ->count();

            if ($numListMotelId != count($request->motel)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_MOTEL_ID[0],
                    'msg' => MsgCode::INVALID_LIST_MOTEL_ID[1],
                ]);
            }

            // check motel has contract active 
            $checkMotelHasContract = DB::table('motels')->join('contracts', 'motels.id', '=', 'contracts.motel_id')
                ->whereIn('motels.id', $listMotelId)
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusContractDefineCode::COMPLETED],
                    ['contracts.rent_to', '<', $now]
                ])
                ->first();

            if ($checkMotelHasContract != null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::MOTEL_HAS_ACTIVE_CONTRACT_UNABLE_CREATE_MO_POST[0],
                    'msg' => MsgCode::MOTEL_HAS_ACTIVE_CONTRACT_UNABLE_CREATE_MO_POST[1],
                ]);
            }

            foreach ($request->motel as $motel) {
                if (!isset($motel['motel_name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_MOTEL_NAME[0],
                        'msg' => MsgCode::INVALID_MOTEL_NAME[1],
                    ]);
                }

                if (!isset($motel['number_floor']) || $motel['number_floor'] < 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_FLOOR[0],
                        'msg' => MsgCode::INVALID_FLOOR[1],
                    ]);
                }
                if (!isset($motel['area']) || $motel['area'] < 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::AREA_IS_REQUIRED[0],
                        'msg' => MsgCode::AREA_IS_REQUIRED[1],
                    ]);
                }

                if (!isset($motel['money']) || $motel['money'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::MONEY_MUST_GREATER_THAN_ZERO[0],
                        'msg' => MsgCode::MONEY_MUST_GREATER_THAN_ZERO[1],
                    ]);
                }

                array_push($arrPostMultipleMotel, [
                    'mo_post_id' => $modelPostExists->id,
                    'motel_id' => $motel['id'],
                    'motel_name' => $motel['motel_name'],
                    'floor' => $motel['number_floor'],
                    'area' => $motel['area'],
                    'money' => $motel['money'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }

        if ($request->user->is_admin == true) {
            $adminVerified = $modelPostExists->admin_verified;
            $statusPost = $modelPostExists->status;
        } else if ($request->user->is_host == true && $request->user->is_admin == false) {
            $adminVerified = $modelPostExists->admin_verified;
            $statusPost = StatusMoPostDefineCode::PROCESSING;
        } else if ($request->user->host_rank == HostRankDefineCode::VIP) {
            $statusPost = StatusMoPostDefineCode::PROCESSING;
            $adminVerified = true;
        } else {
            $statusPost = StatusMoPostDefineCode::PROCESSING;
            $adminVerified = false;
        }

        DB::beginTransaction();
        try {

            $modelPostExists->update([
                "tower_id"  => $request->tower_id,
                "motel_id"  => $motelExist != null ? $motelExist->id : $modelPostExists->motel_id,
                "phone_number"  => $request->phone_number,
                "title" => $request->title,
                "description"  => $request->description,
                "motel_name"  => $request->motel_name,
                "tower_name"  => $request->tower_name,
                "capacity"  => $request->capacity,
                "sex"  => $request->sex,
                "area"  => $request->area,
                "available_motel"  => $request->available_motel,
                "money"  => $request->money,
                "deposit" => $request->deposit,
                "mo_services"  => json_encode($request->mo_services),
                "furniture" =>   json_encode($request->furniture),
                "electric_money" => $request->electric_money,
                "water_money"  => $request->water_money,
                "wifi_money" => $request->wifi_money,
                "park_money" => $request->park_money,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "type" => $request->type,
                "link_video" => $request->link_video,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
                "address_detail" => $request->address_detail,
                "images"  => json_encode($request->images),
                "has_wifi"  => $request->has_wifi ?? false,
                "has_park" => $request->has_park ?? false,
                "has_wc" => $request->has_wc ?? false,
                "has_window" => $request->has_window ?? false,
                "has_security" => $request->has_security ?? false,
                "has_free_move" => $request->has_free_move ?? false,
                "has_own_owner" => $request->has_own_owner ?? false,
                "has_air_conditioner" => $request->has_air_conditioner ?? false,
                "has_water_heater" => $request->has_water_heater ?? false,
                "has_kitchen" => $request->has_kitchen ?? false,
                "has_fridge" => $request->has_fridge ?? false,
                "has_washing_machine" => $request->has_washing_machine ?? false,
                "has_mezzanine" => $request->has_mezzanine ?? false,
                "has_bed" => $request->has_bed ?? false,
                "has_wardrobe" => $request->has_wardrobe ?? false,
                "has_tivi" => $request->has_tivi ?? false,
                "has_pet" => $request->has_pet ?? false,
                "has_balcony" => $request->has_balcony ?? false,
                "has_finger_print" => $request->has_finger_print ?? false,
                "has_kitchen_stuff" => $request->has_kitchen_stuff ?? false,
                "has_table" => $request->has_table ?? false,
                "has_picture" => $request->has_picture ?? false,
                "has_decorative_lights" => $request->has_decorative_lights ?? false,
                "has_tree" => $request->has_tree ?? false,
                "has_pillow" => $request->has_pillow ?? false,
                "has_mattress" => $request->has_mattress ?? false,
                "has_shoes_rasks" => $request->has_shoes_rasks ?? false,
                "has_curtain" => $request->has_curtain ?? false,
                "has_mirror" => $request->has_mirror ?? false,
                "has_sofa" => $request->has_sofa ?? false,
                "has_ceiling_fans" => $request->has_ceiling_fans ?? false,
                "hour_open" => $request->hour_open,
                "minute_open" => $request->minute_open,
                "hour_close" => $request->hour_close,
                "minute_close" => $request->minute_close,
                "quantity_vehicle_parked" => $request->quantity_vehicle_parked,
                "number_floor" => $request->number_floor,
                "admin_verified" => $adminVerified,
                "status" => $statusPost,
                "percent_commission" => $request->percent_commission,
                "percent_commission_collaborator" => $request->percent_commission_collaborator,
                "money_commission_admin" => $request->money_commission_admin,
                "money_commission_user" => $request->user->is_admin == true ? $request->money_commission_user : $moneyCommissionUserMoPost->money_commission_user,
                "note" => $request->note,
            ]);

            // handle add multiple post motel
            if (!empty($arrPostMultipleMotel)) {
                MoPostMultipleMotel::where('mo_post_id', $modelPostExists->id)->delete();
                DB::table('mo_post_multiple_motels')->insert($arrPostMultipleMotel);
            }

            // handle update price mo_post
            try {
                if (!empty($request->tower_id)) {
                    MotelUtils::handleMinMaxMoPost($request->tower_id);
                }
            } catch (\Throwable $th) {
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $modelPostExists,
        ]);
    }

    /**
     * Xóa 1 phòng trọ
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {
        $post_id = request("mo_post_id");
        $modelPostExists = MoPost::where(
            'id',
            $post_id
        )->where(function ($query) use ($request) {
            if ($request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('motels.tower_id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->first();
        if ($modelPostExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        $motelExist = Motel::where([
            ['id', $modelPostExists->motel_id],
            ['user_id', $request->user->id]
        ])->first();
        if ($motelExist != null) {
            $motelExist->update([
                'has_post' => 0
            ]);
        }

        $idDeleted = $modelPostExists->id;
        $modelPostExists->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }

    /**
     * Cập nhật trạng thái bài đăng
     * 
     * @bodyParam status
     */
    public function updateStatus(Request $request)
    {
        $post_id = request("mo_post_id");
        $moPostExists = MoPost::where(function ($query) use ($request) {
            if ($request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                        ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('towers.id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->where('id', $post_id)
            ->first();

        if ($moPostExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if ($moPostExists->motel_id != null) {
            $contractExist = DB::table('contracts')->where([
                ['status', '<>', StatusContractDefineCode::TERMINATION],
                ['motel_id', $moPostExists->motel_id],
                ['user_id', $moPostExists->user_id]
            ])->first();

            if ($contractExist != null) {
                if ($contractExist->status == StatusContractDefineCode::COMPLETED && $request->status == StatusMoPostDefineCode::PROCESSING) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[0],
                        'msg' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[1],
                    ]);
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

        $moPostExists->update([
            'status' => $request->status ?? 0,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostExists->first()
        ]);
    }

    public function pushMoPost(Request $request)
    {
        $post_id = request("mo_post_id");
        $moPostExists = MoPost::where(function ($query) use ($request) {
            if ($request->user->is_admin == false) {
                $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                        ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('towers.id');
                    $q->whereIn('mo_posts.tower_id', $towerIds);
                });
            }
        })
            ->where('id', $post_id)
            ->first();

        if ($moPostExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if ($moPostExists->motel_id != null) {
            $contractExist = DB::table('contracts')->where([
                ['status', '<>', StatusContractDefineCode::TERMINATION],
                ['motel_id', $moPostExists->motel_id],
                ['user_id', $moPostExists->user_id]
            ])->first();

            if ($contractExist != null) {
                if ($contractExist->status == StatusContractDefineCode::COMPLETED && $request->status == StatusMoPostDefineCode::PROCESSING) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[0],
                        'msg' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[1],
                    ]);
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

        $moPostExists->update([
            'status' => $request->status ?? 0,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPostExists->first()
        ]);
    }
}
