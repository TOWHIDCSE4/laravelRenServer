<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\MotelUtils;
use App\Helper\ParamUtils;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\ServiceUnitDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Tower;
use App\Models\TowerMotel;
use App\Models\TowerService;
use App\Helper\StringUtils;
use App\Models\MoPost;
use App\Models\MoService;
use App\Models\Motel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Prophecy\Util\StringUtil;

class TowerController extends Controller
{
    /**
     * 
     * Danh cách tòa nhà
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     * @queryParam has_contract boolean lọc phòng đã thuê
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money ?: 'min_money';
        $hasContract = isset($request->has_contract) ? filter_var($request->has_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $pendingContract = isset($request->pending_contract) ? filter_var($request->pending_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $hasPost = isset($request->has_post) ? filter_var($request->has_post, FILTER_VALIDATE_BOOLEAN) : null;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $towers = Tower::where(function ($query) use ($request) {
            if ($request->user != null) {
                if ($request->is_support_manage == null) {
                    $query->where('towers.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')->where('supporter_id', $request->user->id)->pluck('id');
                        $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                            ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('towers.id');
                        $q->whereIn('towers.id', $towerIds);
                    });
                } else if (filter_var($request->is_supporter_manage, FILTER_VALIDATE_BOOLEAN) == true) {
                    $query->where(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')->where('supporter_id', $request->user->id)->pluck('id');
                        $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                            ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('towers.id');
                        $q->whereIn('towers.id', $towerIds);
                    });
                } else if (filter_var($request->is_supporter_manage, FILTER_VALIDATE_BOOLEAN) == false) {
                    $query->where('towers.user_id', $request->user->id);
                }
            }
        })
            // ->where(function ($query) use ($request) {
            //     if ($request->user != null) {
            //         $query->where('towers.user_id', $request->user->id);
            //     }
            // })
            ->when($request->user_id != null && $request->user->is_admin, function ($query) {
                $query->where('user_id', request('user_id'));
            })
            ->when($request->tower_id != null, function ($query) {
                $query->where('id', request('tower_id'));
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->province != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when($request->has_post != null, function ($query) use ($hasPost) {
                $query->where('has_post', $hasPost);
            })
            ->when($request->type != null, function ($query) {
                $query->where('type', request('type'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            // ->when(isset($hasContract) || isset($pendingContract), function ($query) use ($hasContract, $pendingContract) {
            //     if ($pendingContract == true) {
            //         $query->join('contracts', 'towers.id', 'contracts.tower_id');
            //         $query->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
            //         $query->whereIn('towers.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);
            //         $query->select('towers.*');
            //     } else if ($hasContract) {
            //         $query->join('contracts', 'towers.id', 'contracts.tower_id');
            //         $query->where([
            //             ['contracts.status', StatusContractDefineCode::COMPLETED],
            //             ['towers.status', StatusMotelDefineCode::MOTEL_HIRED]
            //         ]);
            //         $query->select('towers.*');
            //     } else {
            //         $query->where([
            //             ['towers.status', '<>', StatusMotelDefineCode::MOTEL_HIRED]
            //         ]);
            //         $listIdMotelHasContract = DB::table('contracts')
            //             ->whereNotIn(
            //                 'contracts.status',
            //                 [
            //                     StatusContractDefineCode::PROGRESSING,
            //                     StatusContractDefineCode::TERMINATION,
            //                     StatusContractDefineCode::WAITING_CONFIRM,
            //                     StatusContractDefineCode::UNCONFIRMED_BY_HOST
            //                 ]
            //             )
            //             ->pluck('tower_id');
            //         $query->whereNotIn('towers.id', $listIdMotelHasContract);
            //     }
            //     $query->distinct('towers.id');
            // })
            ->select('towers.*')
            ->when(!empty($sortBy) && Schema::hasColumn('towers', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy('towers.' . $sortBy, $descending);
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
            'data' => $towers,
        ]);
    }


    /**
     * 
     * Thêm 1 tòa nhà
     * 
     * @bodyParam mo_services array danh sách dịch vụ phòng
     * @bodyParam images array ảnh của phòng
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam tower_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam sex int 0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
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
     * 
     */
    public function create(Request $request)
    {
        $moServices = $request->tower_service;

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

        $towerNameExists = DB::table('towers')
            ->where('tower_name', $request->tower_name)
            ->when($request->user_id != null && $request->user->is_admin, function ($query) use ($request) {
                $query->where('user_id', $request->user->id);
            })
            ->exists();

        if ($towerNameExists) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_TOWER_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_TOWER_ALREADY_EXISTS[1],
            ], 400);
        }

        // $towerNameAddressExists = DB::table('towers')
        //     ->where([
        //         ['tower_name_filter', StringUtils::convert_lowercase_a_underscore($request->tower_name)],
        //         ['province', $request->province],
        //         ['district', $request->district],
        //         ['wards', $request->wards],
        //         ['address_detail', $request->address_detail],
        //     ])
        //     ->when($request->user_id != null && $request->user->is_admin, function ($query) use ($request) {
        //         $query->where('user_id', $request->user->id);
        //     })
        //     ->first();

        // if (
        //     $towerNameAddressExists != null &&
        //     StringUtils::convert_lowercase_a_underscore($towerNameAddressExists->tower_name) == StringUtils::convert_lowercase_a_underscore($request->tower_name)
        // ) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::TOWER_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::TOWER_ALREADY_EXISTS[1],
        //     ], 400);
        // }


        if (!is_array($request->images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        } else {
            // if (count($request->images) < 2) {
            //     return ResponseUtils::json([
            //         'code' => Response::HTTP_BAD_REQUEST,
            //         'success' => false,
            //         'msg_code' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[0],
            //         'msg' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[1],
            //     ]);
            // }

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

        // handle furniture
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
        } else if ($request->furniture != null && !is_array($request->furniture)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_FURNITURE[0],
                'msg' => MsgCode::INVALID_LIST_FURNITURE[1],
            ]);
        }

        $towerCreated = Tower::create([
            "user_id" => $request->user->id,
            "type" => $request->type,
            "status" => $request->status,
            "phone_number" => $request->phone_number,
            "description" => $request->description,
            "tower_name" => $request->tower_name,
            "tower_name_filter" => StringUtils::convert_lowercase_a_underscore($request->tower_name),
            "capacity" => $request->capacity,
            "sex" => $request->sex ?? 0,
            "area" => $request->area,
            "money" => $request->money ?? 0,
            "deposit" => $request->deposit ?? 0,
            "electric_money" => $request->electric_money ?? 0,
            "water_money"  => $request->water_money ?? 0,
            "has_wifi"  => $request->has_wifi ?? false,
            "wifi_money" => $request->wifi_money ?? 0,
            "has_park" => $request->has_park ?? false,
            "park_money" => $request->park_money,
            "province" => $request->province,
            "district" => $request->district,
            "video_link" => $request->video_link,
            "wards" => $request->wards,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => $request->address_detail,
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
            "number_floor" => $request->number_floor ?? 0,
            "quantity_vehicle_parked" => $request->quantity_vehicle_parked ?? 0,
            "furniture" => $request->furniture != null ?  json_encode($request->furniture) : json_encode([]),
            "images" => $request->images != null ?  json_encode($request->images) : json_encode([]),
            "money_commission_admin" => $request->money_commission_admin
        ]);

        // handle motel service
        if ($moServices != null && is_array($moServices)) {
            foreach ($moServices as $moServiceItem) {
                if (empty($moServiceItem['service_name']) && (trim($moServiceItem['service_name']) == '')) {
                    TowerService::where('tower_id', $towerCreated->id)->delete();
                    // Tower::where('id', $towerCreated->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (DB::table('tower_services')->where([['tower_id', $towerCreated->id], ['service_name', 'LIKE', '%' . $moServiceItem['service_name'] . '%']])->exists()) {
                    TowerService::where('tower_id', $towerCreated->id)->delete();
                    // Tower::where('id', $towerCreated->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[0] . ': ' . $moServiceItem['service_name'],
                        'msg' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[1] . ': ' . $moServiceItem['service_name']
                    ]);
                }

                if (empty($moServiceItem['service_charge']) && is_numeric($moServiceItem['service_charge']) < 0) {
                    TowerService::where('tower_id', $towerCreated->id)->delete();
                    // Tower::where('id', $towerCreated->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (ServiceUnitDefineCode::getServiceUnitCode($moServiceItem['type_unit'], false) == null) {
                    TowerService::where('tower_id', $towerCreated->id)->delete();
                    // Tower::where('id', $towerCreated->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                        'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1]
                    ]);
                }

                TowerService::create([
                    "tower_id" => $towerCreated->id,
                    "service_name"  => $moServiceItem['service_name'],
                    "service_icon"  => $moServiceItem['service_icon'] ?? '',
                    "service_unit"  => $moServiceItem['service_unit'] ?? '',
                    "service_charge" => $moServiceItem['service_charge'],
                    "note" => $moServiceItem['note'] ?? null,
                    "type_unit" => $moServiceItem['type_unit'],
                ]);
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $towerCreated,
        ]);
    }


    /**
     * Thong tin 1 tòa nhà
     * 
     */
    public function getOne(Request $request)
    {

        $tower_id = request("tower_id");

        $towerExists = Tower::where('id', $tower_id)
            ->where(function ($query) use ($request) {
                if ($request->user != null && $request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();
        if ($towerExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        // handle update price motel, mo_post
        try {
            MotelUtils::handleMinMaxTower($tower_id);
            MotelUtils::handleMinMaxMoPost($tower_id);
        } catch (\Throwable $th) {
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $towerExists,
        ]);
    }

    /**
     * Cập nhật 1 tòa nhà
     * 
     * @bodyParam mo_services array danh sách dịch vụ phòng
     * @bodyParam images array danh sách ảnh
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam tower_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam sex int  0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
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
     * 
     */
    public function update(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $moServices = $request->tower_service;
        $tower_id = request("tower_id");
        $towerMotelTemp = [];

        $towerExists = Tower::where('id', $tower_id)
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($towerExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_TOWER_EXISTS[1]
            ]);
        }

        $motelsTower = Motel::where('tower_id', $towerExists->id)->get();


        if (isset($moServices) && is_array($moServices)) {
            TowerService::where('tower_id', $towerExists->id)->delete();
            $listServicePerMotel = [];
            foreach ($moServices as $moServiceItem) {

                if (empty($moServiceItem['service_name']) && (trim($moServiceItem['service_name']) == '')) {
                    TowerService::where('tower_id', $towerExists->id)->delete();
                    Tower::where('id', $towerExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (TowerService::where([['tower_id', $towerExists->id], ['service_name', 'LIKE', '%' . $moServiceItem['service_name'] . '%']])->exists()) {
                    TowerService::where('tower_id', $towerExists->id)->delete();
                    Tower::where('id', $towerExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[0] . ': ' . $moServiceItem['service_name'],
                        'msg' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[1] . ': ' . $moServiceItem['service_name']
                    ]);
                }

                if (empty($moServiceItem['service_charge']) && is_numeric($moServiceItem['service_charge']) < 0) {
                    TowerService::where('tower_id', $towerExists->id)->delete();
                    Tower::where('id', $towerExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (ServiceUnitDefineCode::getServiceUnitCode($moServiceItem['type_unit'], false) == null) {
                    TowerService::where('tower_id', $towerExists->id)->delete();
                    Tower::where('id', $towerExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                        'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1]
                    ]);
                }

                foreach ($motelsTower as $motelItem) {
                    MoService::where('motel_id', $motelItem->id)->delete();

                    array_push($listServicePerMotel, [
                        "motel_id" => $motelItem->id,
                        "service_name"  => $moServiceItem['service_name'] ?? '',
                        "service_icon"  => $moServiceItem['service_icon'] ?? '',
                        "service_unit"  => $moServiceItem['service_unit'] ?? '',
                        "service_charge" => $moServiceItem['service_charge'] ?? 0,
                        "note" => $moServiceItem['note'] ?? null,
                        "type_unit" => $moServiceItem['type_unit'] ?? null,
                        "created_at" => $now->format('y-m-d H:i:s'),
                        "updated_at" => $now->format('y-m-d H:i:s'),
                    ]);
                }

                TowerService::create([
                    "tower_id" => $tower_id,
                    "service_name"  => $moServiceItem['service_name'] ?? '',
                    "service_icon"  => $moServiceItem['service_icon'] ?? '',
                    "service_unit"  => $moServiceItem['service_unit'] ?? '',
                    "service_charge" => $moServiceItem['service_charge'] ?? 0,
                    "note" => $moServiceItem['note'] ?? null,
                    "type_unit" => $moServiceItem['type_unit'] ?? null,
                ]);
            }

            MoService::insert($listServicePerMotel);
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                'msg' => MsgCode::INVALID_LIST_SERVICE[1],
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

        if ($request->furniture != null && !is_array($request->furniture)) {
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

        if (!is_array($request->images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if ($request->list_motel_id != null && is_array($request->list_motel_id)) {
            $checkValidMotelExists = DB::table('motels')->whereIn('id', $request->list_motel_id)->count();

            if ($checkValidMotelExists != count($request->list_motel_id)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_MOTEL_ID[0],
                    'msg' => MsgCode::INVALID_LIST_MOTEL_ID[1]
                ]);
            }

            foreach ($request->list_motel_id as $motelId) {
                array_push($towerMotelTemp, [
                    'tower_id' => $towerExists->id,
                    'motel_id' => $motelId,
                    'status' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        } else if ($request->list_motel_id != null && !is_array($request->list_motel_id)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_MOTEL_ID[0],
                'msg' => MsgCode::INVALID_LIST_MOTEL_ID[1]
            ]);
        }

        // if (isset($request->status) && StatusMotelDefineCode::getStatusMotelCode($request->status) == null) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_MOTEL_STATUS[0],
        //         'msg' => MsgCode::INVALID_MOTEL_STATUS[1],
        //     ]);
        // }

        $towerPostExists = MoPost::where('tower_id', $tower_id)->first();

        DB::beginTransaction();
        try {
            $towerExists->update(Helper::removeItemArrayIfNullValue(
                [
                    "type"  => $request->type,
                    "status"  => $request->status,
                    "phone_number"  => $request->phone_number,
                    "title" => $request->title,
                    "description"  => $request->description,
                    "tower_name"  => $request->tower_name,
                    "tower_name_filter"  => str_replace(' ', '_', strtolower($request->tower_name)),
                    "capacity"  => $request->capacity,
                    "sex"  => $request->sex,
                    "area"  => $request->area,
                    "money"  => $request->money ?? 0,
                    "deposit" => $request->deposit ?? 0,
                    "electric_money" => $request->electric_money ?? 0,
                    "water_money"  => $request->water_money ?? 0,
                    "has_wifi"  => $request->has_wifi ?? false,
                    "wifi_money" => $request->wifi_money,
                    "has_park" => $request->has_park ?? false,
                    "park_money" => $request->park_money,
                    "province" => $request->province,
                    "district" => $request->district,
                    "video_link" => $request->video_link,
                    "wards" => $request->wards,
                    "province_name" => Place::getNameProvince($request->province),
                    "district_name" => Place::getNameDistrict($request->district),
                    "wards_name" => Place::getNameWards($request->wards),
                    "address_detail" => $request->address_detail,
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
                    "number_floor" => $request->number_floor ?? 0,
                    "quantity_vehicle_parked" => $request->quantity_vehicle_parked,
                    "furniture" =>  $request->furniture != null ? json_encode($request->furniture) : json_encode([]),
                    "images" => $request->images != null ? json_encode($request->images) : json_encode([]),
                    "money_commission_admin" => $request->money_commission_admin,
                    "money_commission_user" => $request->user->is_admin == true ? $request->money_commission_user : $towerExists->money_commission_user
                ]
            ));

            if (count($towerMotelTemp) > 0) {;
                TowerMotel::where([
                    ['tower_id' => $towerExists->id],
                    ['motel_id' => $motelId]
                ])->delete();

                DB::table('tower_motels')->insert($towerMotelTemp);
            }

            if ($towerPostExists) {
                $towerPostExists->update(
                    [
                        "type"  => $request->type != null ? $request->type : $towerPostExists->type,
                        "phone_number"  => $request->phone_number != null ? $request->phone_number : $towerPostExists->phone_number,
                        "title" => $request->title != null ? $request->title : $towerPostExists->title,
                        "description"  => $request->description != null ? $request->description : $towerPostExists->description,
                        "motel_name"  => $request->motel_name != null ? $request->motel_name : $towerPostExists->motel_name,
                        "capacity"  => $request->capacity != null ? $request->capacity : $towerPostExists->capacity,
                        "sex"  => $request->sex != null ? $request->sex : $towerPostExists->sex,
                        "link_video" => $request->link_video,
                        "area"  => $request->area != null ? $request->area : $towerPostExists->area,
                        "money"  => $request->money != null ? $request->money : $towerPostExists->money,
                        "deposit" => $request->deposit != null ? $request->deposit : $towerPostExists->deposit,
                        "electric_money" => $request->electric_money != null ? $request->electric_money : $towerPostExists->electric_money,
                        "water_money"  => $request->water_money != null ? $request->water_money : $towerPostExists->water_money,
                        "has_wifi"  => $request->has_wifi != null ? $request->has_wifi : $towerPostExists->has_wifi,
                        "wifi_money" => $request->wifi_money != null ? $request->wifi_money : $towerPostExists->wifi_money,
                        "has_park" => $request->has_park != null ? $request->has_park : $towerPostExists->has_park,
                        "park_money" => $request->park_money != null ? $request->park_money : $towerPostExists->park_money,
                        "province" => $request->province != null ? $request->province : $towerPostExists->province,
                        "district" => $request->district != null ? $request->district : $towerPostExists->district,
                        "wards" => $request->wards != null ? $request->wards : $towerPostExists->wards,
                        "province_name" => Place::getNameProvince($request->province != null ? $request->province : $towerPostExists->province),
                        "district_name" => Place::getNameDistrict($request->district != null ? $request->district : $towerPostExists->district),
                        "wards_name" => Place::getNameWards($request->wards != null ? $request->wards : $towerPostExists->wards),
                        "address_detail" => $request->address_detail,
                        "has_wc" => $request->has_wc != null ? $request->has_wc : $towerPostExists->has_wc,
                        "has_window" => $request->has_window != null ? $request->has_window : $towerPostExists->has_window,
                        "has_security" => $request->has_security != null ? $request->has_security : $towerPostExists->has_security,
                        "has_free_move" => $request->has_free_move != null ? $request->has_free_move : $towerPostExists->has_free_move,
                        "has_own_owner" => $request->has_own_owner != null ? $request->has_own_owner : $towerPostExists->has_own_owner,
                        "has_air_conditioner" => $request->has_air_conditioner != null ? $request->has_air_conditioner : $towerPostExists->has_air_conditioner,
                        "has_water_heater" => $request->has_water_heater != null ? $request->has_water_heater : $towerPostExists->has_water_heater,
                        "has_kitchen" => $request->has_kitchen != null ? $request->has_kitchen : $towerPostExists->has_kitchen,
                        "has_fridge" => $request->has_fridge != null ? $request->has_fridge : $towerPostExists->has_fridge,
                        "has_washing_machine" => $request->has_washing_machine != null ? $request->has_washing_machine : $towerPostExists->has_washing_machine,
                        "has_mezzanine" => $request->has_mezzanine != null ? $request->has_mezzanine : $towerPostExists->has_mezzanine,
                        "has_bed" => $request->has_bed != null ? $request->has_bed : $towerPostExists->has_bed,
                        "has_wardrobe" => $request->has_wardrobe != null ? $request->has_wardrobe : $towerPostExists->has_wardrobe,
                        "has_tivi" => $request->has_tivi != null ? $request->has_tivi : $towerPostExists->has_tivi,
                        "has_pet" => $request->has_pet != null ? $request->has_pet : $towerPostExists->has_pet,
                        "has_balcony" => $request->has_balcony != null ? $request->has_balcony : $towerPostExists->has_balcony,
                        "has_finger_print" => $request->has_finger_print != null ? $request->has_finger_print : $towerPostExists->has_finger_print,
                        "has_kitchen_stuff" => $request->has_kitchen_stuff != null ? $request->has_kitchen_stuff : $towerPostExists->has_kitchen_stuff,
                        "has_table" => $request->has_table != null ? $request->has_table : $towerPostExists->has_table,
                        "has_picture" => $request->has_picture != null ? $request->has_picture : $towerPostExists->has_picture,
                        "has_decorative_lights" => $request->has_decorative_lights != null ? $request->has_decorative_lights : $towerPostExists->has_decorative_lights,
                        "has_tree" => $request->has_tree != null ? $request->has_tree : $towerPostExists->has_tree,
                        "has_pillow" => $request->has_pillow != null ? $request->has_pillow : $towerPostExists->has_pillow,
                        "has_mattress" => $request->has_mattress != null ? $request->has_mattress : $towerPostExists->has_mattress,
                        "has_shoes_rasks" => $request->has_shoes_rasks != null ? $request->has_shoes_rasks : $towerPostExists->has_shoes_rasks,
                        "has_curtain" => $request->has_curtain != null ? $request->has_curtain : $towerPostExists->has_curtain,
                        "has_mirror" => $request->has_mirror != null ? $request->has_mirror : $towerPostExists->has_mirror,
                        "has_sofa" => $request->has_sofa != null ? $request->has_sofa : $towerPostExists->has_sofa,
                        "has_ceiling_fans" => $request->has_ceiling_fans != null ? $request->has_ceiling_fans : $towerPostExists->has_ceiling_fans,
                        "hour_open" => $request->hour_open != null ? $request->hour_open : $towerPostExists->hour_open,
                        "minute_open" => $request->minute_open != null ? $request->minute_open : $towerPostExists->minute_open,
                        "hour_close" => $request->hour_close != null ? $request->hour_close : $towerPostExists->hour_close,
                        "minute_close" => $request->minute_close != null ? $request->minute_close : $towerPostExists->minute_close,
                        "number_floor" => $request->number_floor != null ? $request->number_floor : $towerPostExists->number_floor,
                        "quantity_vehicle_parked" => $request->quantity_vehicle_parked != null ? $request->quantity_vehicle_parked : $towerPostExists->quantity_vehicle_parked,
                        "furniture" =>  $request->furniture != null ? json_encode($request->furniture) : $towerPostExists->furniture,
                        "images" => $request->images != null ? json_encode($request->images) : $towerPostExists->images,
                        "money_commission_admin" => $request->money_commission_admin != null ? $request->money_commission_admin : $towerPostExists->money_commission_admin,
                        "money_commission_user" => $request->money_commission_user != null ? $request->money_commission_user : $towerPostExists->money_commission_user,
                        "admin_verified" => $towerPostExists->admin_verified,
                        "percent_commission" => $request->percent_commission != null ? $request->percent_commission : $towerPostExists->percent_commission,
                        "percent_commission_collaborator" => $request->percent_commission_collaborator != null ? $request->percent_commission_collaborator : $towerPostExists->percent_commission_collaborator
                    ]
                );
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Tower::where('id', $towerExists->id)->first(),
        ]);
    }

    /**
     * Xóa 1 tòa nhà
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $tower_id = request("tower_id");
        $towerExists = Tower::where([
            ['id', $tower_id]
        ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($towerExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $idDeleted = $towerExists->id;
        $towerExists->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }

    public function getUserWiseAllTower($userId)
    {
 // $towers = Tower::where('user_id', $userId)->get();
        $limit = request()->limit ?: 20;
        $towers = Tower::where('user_id', $userId)->paginate($limit);
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => 'SUCCESS',
            'msg' => "Tower list",
            'data' => $towers,
        ]);
    }
}