<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\MotelUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MoService;
use App\Models\Motel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Helper\Place;
use App\Helper\ServiceUnitDefineCode;
use App\Helper\ParamUtils;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StringUtils;
use App\Models\ConfigCommission;
use App\Models\MoPost;
use App\Models\Tower;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Quản lý/Phòng trọ
 */

class MotelController extends Controller
{
    /**
     * 
     * Danh cách phòng trọ
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
        $typeMoney = $request->type_money;
        $search = $request->search;
        $hasContract = isset($request->has_contract) ? filter_var($request->has_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $pendingContract = isset($request->pending_contract) ? filter_var($request->pending_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $hasPost = isset($request->has_post) ? filter_var($request->has_post, FILTER_VALIDATE_BOOLEAN) : null;
        $isHaveTower = filter_var($request->is_have_tower, FILTER_VALIDATE_BOOLEAN) ?? false;
        $isDraftMotel = $request->status == StatusMotelDefineCode::MOTEL_DRAFT ? true : false;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $motels = Motel::where(function ($query) use ($request) {
            if ($request->user != null) {
                $supporterManageTowerIds = DB::table('supporter_manage_towers')
                    ->where('supporter_id', $request->user->id)
                    ->pluck('id');
                $motelSupporterIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                    ->distinct()
                    ->pluck('motels.id')->toArray();
                $myMotelIds = Motel::where('motels.user_id', $request->user->id)
                    ->pluck('motels.id')->toArray();

                $listMotelIds = array_merge($motelSupporterIds, $myMotelIds);
                $query->whereIn('motels.id', $listMotelIds);
            }
        })
            ->where(function ($query) use ($isDraftMotel) {
                if ($isDraftMotel) {
                    $query->where('motels.status', StatusMotelDefineCode::MOTEL_DRAFT);
                } else {
                    $query->where('motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT);
                }
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->floor_from != null && is_numeric($request->floor_from), function ($query) use ($request) {
                $query->where('number_floor', '>=', $request->floor_from);
            })
            ->when($request->floor_to != null && is_numeric($request->floor_to), function ($query) use ($request) {
                $query->where('number_floor', '<=', $request->floor_to);
            })
            ->when($isDraftMotel, function ($query) {
                $query->where('status', StatusMotelDefineCode::MOTEL_DRAFT);
            })
            ->where(function ($query) use ($request, $isHaveTower) {
                if ($isHaveTower && $request->tower_id != null) {
                    $query->where('motels.tower_id', $request->tower_id);
                } else if ($isHaveTower && $request->tower_id == null) {
                    $query->whereNotNull('motels.tower_id');
                } else if (!$isHaveTower) {
                    $query->whereNull('motels.tower_id');
                }
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
            ->when(isset($hasContract) || isset($pendingContract), function ($query) use ($hasContract, $pendingContract) {
                if ($pendingContract == true) {
                    $query->join('contracts', 'motels.id', '=', 'contracts.motel_id');
                    $query->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                    $query->whereIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);
                    $query->select('motels.*');
                } else if ($hasContract) {
                    $query->join('contracts', 'motels.id', 'contracts.motel_id');
                    $query->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                    ]);
                    $query->select('motels.*');
                } else {
                    $listIdMotelHasContract = DB::table('contracts')
                        ->whereIn(
                            'contracts.status',
                            [
                                StatusContractDefineCode::COMPLETED,
                            ]
                        )
                        ->pluck('motel_id');
                    $query->whereNotIn('motels.id', $listIdMotelHasContract);
                }
                $query->distinct('motels.id');
            })
            ->when(isset($hasUsedCommission), function ($query) {
                $query->join('collaborator_refer_motels', 'motels.id', '=', 'collaborator_refer_motels.motel_id');
                $query->where('collaborator_refer_motels.status', StatusCollaboratorReferMotelDefineCode::COMPLETED);
                $query->select('motels.*');
                $query->distinct('motels.id');
            })
            ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->search($search)
            ->paginate($limit);


        // $custom = collect(
        //     MotelUtils::getBadgesMotels($request->user->id)
        // );
        // $data = $custom->merge($motels);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels,
        ]);
    }
    /**
     * 
     * Danh cách phân phòng trọ quản lý
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     * @queryParam has_contract boolean lọc phòng đã thuê
     */
    public function getAllMotelManageTower(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $search = $request->search;
        $hasContract = isset($request->has_contract) ? filter_var($request->has_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $pendingContract = isset($request->pending_contract) ? filter_var($request->pending_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $hasPost = isset($request->has_post) ? filter_var($request->has_post, FILTER_VALIDATE_BOOLEAN) : null;
        $isHaveTower = filter_var($request->is_have_tower, FILTER_VALIDATE_BOOLEAN) ?? false;
        $isDraftMotel = $request->status == StatusMotelDefineCode::MOTEL_DRAFT ? true : false;
        $isHaveSupporter = isset($request->is_have_supporter) ? filter_var($request->is_have_supporter, FILTER_VALIDATE_BOOLEAN) : $request->is_have_supporter;
        $isSupportManage = filter_var($request->is_supporter_manage, FILTER_VALIDATE_BOOLEAN) ?: null;
        $isSupporter = filter_var($request->is_supporter, FILTER_VALIDATE_BOOLEAN) ?: null;
        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $motels = Motel::where(function ($query) use ($request, $isHaveSupporter, $isSupportManage, $isSupporter) {
            if ($request->user != null) {
                // $motelIds = [];
                // if ($isSupporter === null) {
                //     $supporterManageTowerIds = DB::table('supporter_manage_towers')
                //         ->when($request->is_have_supporter != null || $isSupporter != null, function ($sq) use ($request) {
                //             $sq->where('host_id', $request->user->id);
                //         })
                //         ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                //             $sq->where('id', $request->manage_supporter_id);
                //         })
                //         ->pluck('id');
                //     $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                //         ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                //         ->distinct()
                //         ->pluck('motels.id');
                //     $exceptMotelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                //         ->whereNotIn('motels.id', $motelIds)
                //         ->distinct()
                //         ->pluck('motels.id');

                //     if ($isHaveSupporter === true && $request->manage_supporter_id != null) {
                //         $query->where('motels.user_id', $request->user->id)->whereIn('motels.id', $motelIds);
                //     } else {
                //         $query->where('motels.user_id', $request->user->id)->orWhere(function ($q) use (&$motelIds, $exceptMotelIds, $isHaveSupporter, $isSupportManage, $isSupporter) {
                //             if ($isHaveSupporter === true) {
                //                 $q->whereIn('motels.id', $motelIds);
                //             } else if ($isHaveSupporter === null) {
                //                 // $q->whereIn('motels.id', $motelIds);
                //                 $q->whereNotIn('motels.id', $exceptMotelIds);
                //             } else if ($isHaveSupporter === false && $isSupportManage === false) {
                //                 $q->whereNotIn('motels.id', $motelIds);
                //             } else if ($isHaveSupporter === false && $isSupportManage === true) {
                //                 $q->whereNotIn('motels.id', $exceptMotelIds);
                //             }
                //         });
                //     }
                // } else if ($isSupporter === true) {
                //     $query->where('motels.user_id', $request->user->id)->orWhere(function ($q) use ($request, $motelIds) {
                //         $supporterManageTowerIds = DB::table('supporter_manage_towers')
                //             ->when($request->is_have_supporter != null, function ($subQ) use ($request) {
                //                 $subQ->where('supporter_id', $request->user->id);
                //             })
                //             ->when($request->manage_supporter_id !== null, function ($subQ) use ($request) {
                //                 $subQ->where('id', $request->manage_supporter_id);
                //             })
                //             ->distinct()
                //             ->pluck('id');

                //         $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                //             ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                //             ->when($request->tower_id != null, function ($subQ) use ($request) {
                //                 $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                //             })
                //             ->distinct()
                //             ->pluck('motels.id');

                //         $q->whereIn('motels.id', $motelIds);
                //     });
                // }
                if ($isHaveSupporter) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_manage_towers.host_id', $request->user->id)
                        ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                            $sq->where('id', $request->manage_supporter_id);
                        })
                        ->pluck('supporter_manage_towers.id');
                    $motelSupporterIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->when($request->tower_id != null, function ($subQ) use ($request) {
                            $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                        })
                        ->when($request->manage_supporter_id != null, function ($subQ) use ($request) {
                            $subQ->where('supporter_manage_tower_id', $request->manage_supporter_id);
                        })
                        ->distinct()
                        ->pluck('motels.id')->toArray();
                    $query->whereIn('motels.id', $motelSupporterIds);
                } else if ($isHaveSupporter === false) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_manage_towers.host_id', $request->user->id)
                        ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                            $sq->where('id', $request->manage_supporter_id);
                        })
                        ->pluck('supporter_manage_towers.id');
                    $motelSupporterIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->when($request->tower_id != null, function ($subQ) use ($request) {
                            $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                        })
                        ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                            $sq->where('supporter_manage_tower_id', $request->manage_supporter_id);
                        })
                        ->distinct()
                        ->pluck('motels.id')->toArray();
                    $query->whereNotIn('motels.id', $motelSupporterIds);
                } else {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_manage_towers.host_id', $request->user->id)
                        ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                            $sq->where('id', '<>', $request->manage_supporter_id);
                        })
                        ->pluck('supporter_manage_towers.id');
                    $motelSupporterIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->when($request->tower_id != null, function ($subQ) use ($request) {
                            $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                        })
                        ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                            $sq->where('supporter_manage_tower_id', '<>', $request->manage_supporter_id);
                        })
                        ->distinct()
                        ->pluck('motels.id')->toArray();
                    $query->where('motels.user_id', $request->user->id)->whereNotIn('motels.id', $motelSupporterIds);
                }
            }
        })
            ->where(function ($query) use ($isDraftMotel) {
                if ($isDraftMotel) {
                    $query->where('motels.status', StatusMotelDefineCode::MOTEL_DRAFT);
                } else {
                    $query->where('motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT);
                }
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->floor_from != null && is_numeric($request->floor_from), function ($query) use ($request) {
                $query->where('number_floor', '>=', $request->floor_from);
            })
            ->when($request->floor_to != null && is_numeric($request->floor_to), function ($query) use ($request) {
                $query->where('number_floor', '<=', $request->floor_to);
            })
            ->where(function ($query) use ($request, $isHaveTower) {
                if ($isHaveTower && $request->tower_id != null) {
                    $query->where('motels.tower_id', $request->tower_id);
                } else if ($isHaveTower && $request->tower_id == null) {
                    $query->whereNotNull('motels.tower_id');
                } else if (!$isHaveTower) {
                    $query->whereNull('motels.tower_id');
                }
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
            ->when(isset($hasContract) || isset($pendingContract), function ($query) use ($hasContract, $pendingContract, $request, $isHaveSupporter, $isSupporter) {
                if ($pendingContract == true) {
                    $query->join('contracts', 'motels.id', '=', 'contracts.motel_id');
                    $query->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                    $query->whereIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);
                    $query->select('motels.*');
                } else if ($hasContract) {
                    $query->join('contracts', 'motels.id', 'contracts.motel_id');
                    $query->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                    ]);
                    $query->select('motels.*');
                } else {
                    $listIdMotelHasContract = DB::table('contracts')
                        ->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED])
                        ->pluck('motel_id');

                    $query->whereNotIn('motels.id', $listIdMotelHasContract);
                }
                $query->distinct('motels.id');

                // $supporterManageTowerIds = DB::table('supporter_manage_towers')
                //     ->when($isHaveSupporter != null || $isSupporter != null, function ($q) use ($request) {
                //         $q->where('supporter_id', $request->user->id);
                //     })
                //     ->pluck('id');

                // $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                //     ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                //     ->when($request->tower_id != null, function ($subQ) use ($request) {
                //         $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                //     })
                //     ->pluck('motels.id');

                // $exceptMotelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                //     ->whereNotIn('motels.id', $motelIds)
                //     ->pluck('motels.id');
                // if ($pendingContract == true) {
                //     $query->join('contracts', 'motels.id', '=', 'contracts.motel_id');
                //     $query->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                //     $query->whereIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);
                //     $query->select('motels.*');
                // } else if ($hasContract) {
                //     $query->join('contracts', 'motels.id', 'contracts.motel_id');
                //     $query->where([
                //         ['contracts.status', StatusContractDefineCode::COMPLETED],
                //         ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                //     ]);
                //     if ($isSupporter === true) {
                //         $query->orWhereIn('motels.id', $motelIds)->where('motels.status', StatusMotelDefineCode::MOTEL_HIRED);
                //     } else if ($isSupporter === null) {
                //         $query->whereIn('motels.id', $motelIds)->where('motels.status', StatusMotelDefineCode::MOTEL_HIRED);
                //     }
                //     $query->select('motels.*');
                // } else {
                //     $listIdMotelHasContract = DB::table('contracts')
                //         ->whereIn(
                //             'contracts.status',
                //             [
                //                 StatusContractDefineCode::COMPLETED,
                //             ]
                //         )
                //         ->pluck('motel_id');
                //     $query->whereNotIn('motels.id', $listIdMotelHasContract);
                //     if ($isSupporter === true) {
                //         $query->whereIn('motels.id', $motelIds)->where('motels.status', StatusMotelDefineCode::MOTEL_EMPTY);
                //     } else if ($isSupporter === null) {
                //         $query->whereIn('motels.id', $motelIds)->where('motels.status', StatusMotelDefineCode::MOTEL_EMPTY);
                //     }
                // }
                // $query->distinct('motels.id');
            })
            ->when(isset($hasUsedCommission), function ($query) {
                $query->join('collaborator_refer_motels', 'motels.id', '=', 'collaborator_refer_motels.motel_id');
                $query->where('collaborator_refer_motels.status', StatusCollaboratorReferMotelDefineCode::COMPLETED);
                $query->select('motels.*');
                $query->distinct('motels.id');
            })
            ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->search($search)
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels,
        ]);
    }


    /**
     * 
     * Thêm 1 phòng trọ
     * 
     * @bodyParam tower_id int mã tòa nhà
     * @bodyParam mo_services array danh sách dịch vụ phòng
     * @bodyParam images array ảnh của phòng
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
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
     * 
     */
    public function create(Request $request)
    {
        $moServices = $request->mo_services;

        // check place
        if ($request->status != StatusMotelDefineCode::MOTEL_DRAFT) {
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
            } else {
                if (count($request->images) < 2) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[0],
                        'msg' => MsgCode::REQUIRE_AT_LEAST_2_IMAGES[1],
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

            // handle furniture
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
        }

        // $motelNameExists = DB::table('motels')
        //     ->where('motel_name', $request->motel_name)
        //     ->when($request->user_id != null && $request->user->is_admin, function ($query) use ($request) {
        //         $query->where('user_id', $request->user->id);
        //     })
        //     ->exists();

        // if ($motelNameExists) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_MOTEL_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_MOTEL_ALREADY_EXISTS[1],
        //     ], 400);
        // }


        // $towerNameExists = DB::table('towers')
        //     ->where('tower_name', $request->tower_name)
        //     ->when($request->user_id != null && $request->user->is_admin, function ($query) use ($request) {
        //         $query->where('user_id', $request->user->id);
        //     })
        //     ->exists();

        // if ($towerNameExists) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_TOWER_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_TOWER_ALREADY_EXISTS[1],
        //     ], 400);
        // }

        $towerNameAddressExists = DB::table('towers')
            ->where([
                ['tower_name', $request->tower_name],
                ['tower_name_filter', str_replace(' ', '_', strtolower($request->tower_name))],
                ['province', $request->province],
                ['district', $request->district],
                ['wards', $request->wards],
                ['address_detail', $request->address_detail],
            ])
            ->when($request->user_id != null && $request->user->is_admin, function ($query) use ($request) {
                $query->where('user_id', $request->user->id);
            })
            ->first();

        if (
            $towerNameAddressExists != null &&
            StringUtils::convert_lowercase_a_underscore($towerNameAddressExists->tower_name) == StringUtils::convert_lowercase_a_underscore($request->tower_name)
        ) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TOWER_ALREADY_EXISTS[0],
                'msg' => MsgCode::TOWER_ALREADY_EXISTS[1],
            ], 400);
        }

        if ($request->tower_id != null && !DB::table('towers')->where([['id', $request->tower_id], ['user_id', $request->user->id]])->exists()) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_TOWER_EXISTS[1],
            ], 400);
        }
        $motel_created = Motel::create([
            "user_id" => $request->user->id,
            "tower_id" => $request->tower_id,
            "type"  => $request->type,
            "status"  => $request->status == StatusMotelDefineCode::MOTEL_DRAFT ? $request->status : StatusMotelDefineCode::MOTEL_EMPTY,
            "phone_number"  => $request->phone_number,
            "title" => $request->title,
            "description"  => $request->description,
            "motel_name"  => $request->motel_name,
            "capacity"  => $request->capacity,
            "sex"  => $request->sex ?? 0,
            "area"  => $request->area,
            "video_link" => $request->video_link,
            "money"  => $request->money ?? 0,
            "deposit" => $request->deposit ?? 0,
            "electric_money" => $request->electric_money ?? 0,
            "water_money"  => $request->water_money ?? 0,
            "has_wifi"  => $request->has_wifi ?? false,
            "wifi_money" => $request->wifi_money ?? 0,
            "has_park" => $request->has_park ?? false,
            "park_money" => $request->park_money,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => StringUtils::convert_lowercase_backspace($request->address_detail),
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
            "used_at" => Helper::getTimeNowDateTime(),
            "quantity_vehicle_parked" => $request->quantity_vehicle_parked ?? 0,
            "furniture" => $request->furniture != null ?  json_encode($request->furniture) : json_encode([]),
            "images" => $request->images != null ?  json_encode($request->images) : json_encode([]),
            "money_commission_admin" => $request->money_commission_admin
        ]);

        //config commission 
        if ($request->has_collaborator == true) {
            ConfigCommission::create([
                'user_host_id' => $request->user->id,
                'motel_id' => $motel_created->id,
                'money_commission_admin' => $request->money_commission_admin,
            ]);
        }

        // handle motel service
        if ($moServices != null && is_array($moServices)) {
            foreach ($moServices as $moServiceItem) {
                if (empty($moServiceItem['service_name'])) {
                    MoService::where('motel_id', $motel_created->id)->delete();
                    Motel::where('id', $motel_created->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (DB::table('mo_services')->where([['motel_id', $motel_created->id], ['service_name', $moServiceItem['service_name']]])->exists()) {
                    MoService::where('motel_id', $motel_created->id)->delete();
                    Motel::where('id', $motel_created->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[0] . ': ' . $moServiceItem['service_name'],
                        'msg' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[1] . ': ' . $moServiceItem['service_name']
                    ]);
                }

                if (!isset($moServiceItem['service_charge']) && $moServiceItem['service_charge'] < 0) {
                    MoService::where('motel_id', $motel_created->id)->delete();
                    Motel::where('id', $motel_created->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (ServiceUnitDefineCode::getServiceUnitCode($moServiceItem['type_unit'], false) === null) {
                    MoService::where('motel_id', $motel_created->id)->delete();
                    Motel::where('id', $motel_created->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                        'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1]
                    ]);
                }

                DB::beginTransaction();
                try {
                    MoService::create([
                        "motel_id" => $motel_created->id,
                        "service_name"  => $moServiceItem['service_name'],
                        "service_icon"  => $moServiceItem['service_icon'] ?? '',
                        "service_unit"  => $moServiceItem['service_unit'] ?? '',
                        "service_charge" => $moServiceItem['service_charge'],
                        "note" => $moServiceItem['note'] ?? null,
                        "type_unit" => $moServiceItem['type_unit'],
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        }

        // handle update price motel
        try {
            if (!empty($request->tower_id)) {
                MotelUtils::handleMinMaxTower($request->tower_id);
                MotelUtils::handleMinMaxMoPost($request->tower_id);
            }
        } catch (\Throwable $th) {
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
     * 
     * Thong tin 1 phòng trọ
     * 
     */
    public function getOne(Request $request)
    {

        $motel_id = request("motel_id");

        $supporterManageTowerIds = DB::table('supporter_manage_towers')
            ->when($request->is_have_supporter != null, function ($subQ) use ($request) {
                $subQ->where('supporter_id', $request->user->id);
            })
            ->when($request->manage_supporter_id !== null, function ($subQ) use ($request) {
                $subQ->where('id', $request->manage_supporter_id);
            })
            ->distinct()
            ->pluck('id');

        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
            ->when($request->tower_id != null, function ($subQ) use ($request) {
                $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
            })
            ->distinct()
            ->pluck('motels.id');

        $motelExists = Motel::where('id', $motel_id)
            ->where(function ($query) use ($request, $motelIds) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds);
                }
            })
            ->first();

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motelExists,
        ]);
    }

    /**
     * Cập nhật 1 phòng trọ
     * 
     * @bodyParam tower_id int mã tòa nhà
     * @bodyParam mo_services array danh sách dịch vụ phòng
     * @bodyParam images array danh sách ảnh
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
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
        $moServices = $request->mo_services;
        $motel_id = request("motel_id");

        $motelExists = Motel::where('id', $motel_id)
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
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $motelPostExists = MoPost::where('motel_id', $motel_id)
            ->where(function ($query) use ($request) {
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
            ->first();

        if (isset($moServices) && is_array($moServices)) {
            MoService::where('motel_id', $motel_id)->delete();
            foreach ($moServices as $moServiceItem) {

                if (empty($moServiceItem['service_name']) && (trim($moServiceItem['service_name']) == '')) {
                    MoService::where('motel_id', $motelExists->id)->delete();
                    // Motel::where('id', $motelExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (DB::table('mo_services')->where([['motel_id', $motelExists->id], ['service_name', 'LIKE', '%' . $moServiceItem['service_name'] . '%']])->exists()) {
                    MoService::where('motel_id', $motelExists->id)->delete();
                    // Motel::where('id', $motelExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[0] . ': ' . $moServiceItem['service_name'],
                        'msg' => MsgCode::NAME_SERVICE_ALREADY_EXISTS[1] . ': ' . $moServiceItem['service_name']
                    ]);
                }

                if (empty($moServiceItem['service_charge']) && is_numeric($moServiceItem['service_charge']) < 0) {
                    MoService::where('motel_id', $motelExists->id)->delete();
                    // Motel::where('id', $motelExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1]
                    ]);
                }

                if (ServiceUnitDefineCode::getServiceUnitCode($moServiceItem['type_unit'], false) == null) {
                    MoService::where('motel_id', $motelExists->id)->delete();
                    // Motel::where('id', $motelExists->id)->delete();
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                        'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1]
                    ]);
                }


                MoService::create([
                    "motel_id" => $motel_id,
                    "service_name"  => $moServiceItem['service_name'] ?? '',
                    "service_icon"  => $moServiceItem['service_icon'] ?? '',
                    "service_unit"  => $moServiceItem['service_unit'] ?? '',
                    "service_charge" => $moServiceItem['service_charge'] ?? 0,
                    "note" => $moServiceItem['note'] ?? null,
                    "type_unit" => $moServiceItem['type_unit'] ?? null,
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                'msg' => MsgCode::INVALID_LIST_SERVICE[1],
            ]);
        }

        if ($request->tower_id != null) {
            $tower = !DB::table('towers')
                ->where('id', $request->tower_id)
                ->where(function ($query) use ($request) {
                    if ($request->user->is_admin == false) {
                        $query->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $towerIds = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
                                ->whereIn('connect_manage_towers.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->pluck('towers.id');
                            $q->whereIn('towers.id', $towerIds);
                        });
                    }
                })
                ->exists();
            if ($tower) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                    'msg' => MsgCode::NO_TOWER_EXISTS[1],
                ], 400);
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

        if (isset($request->status) && StatusMotelDefineCode::getStatusMotelCode($request->status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_MOTEL_STATUS[0],
                'msg' => MsgCode::INVALID_MOTEL_STATUS[1],
            ]);
        }

        DB::beginTransaction();
        try {
            $motelExists->update(
                [
                    "tower_id"  => $request->tower_id,
                    "type"  => $request->type,
                    "status"  => $request->status == StatusMotelDefineCode::MOTEL_DRAFT ? StatusMotelDefineCode::MOTEL_EMPTY : $request->status ?? StatusMotelDefineCode::MOTEL_EMPTY,
                    "phone_number"  => $request->phone_number,
                    "title" => $request->title,
                    "tower_id" => $request->tower_id,
                    "description"  => $request->description,
                    "motel_name"  => $request->motel_name,
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
                    "wards" => $request->wards,
                    "video_link" => $request->video_link,
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
                    "money_commission_user" => $request->user->is_admin == true ? $request->money_commission_user : $motelExists->money_commission_user
                ]
            );

            if ($motelPostExists != null) {
                if ($request->user->is_admin == true) {
                    $adminVerified = true;
                } else if ($request->user->host_rank == HostRankDefineCode::VIP) {
                    $adminVerified = true;
                } else {
                    $adminVerified = $motelPostExists->admin_verified;
                }
                $motelPostExists->update(
                    [
                        "type"  => $request->type != null ? $request->type : $motelPostExists->type,
                        "phone_number"  => $request->phone_number != null ? $request->phone_number : $motelPostExists->phone_number,
                        "title" => $request->title != null ? $request->title : $motelPostExists->title,
                        "description"  => $request->description != null ? $request->description : $motelPostExists->description,
                        "motel_name"  => $request->motel_name != null ? $request->motel_name : $motelPostExists->motel_name,
                        "capacity"  => $request->capacity != null ? $request->capacity : $motelPostExists->capacity,
                        "sex"  => $request->sex != null ? $request->sex : $motelPostExists->sex,
                        "link_video" => $request->link_video,
                        "area"  => $request->area != null ? $request->area : $motelPostExists->area,
                        "money"  => $request->money != null ? $request->money : $motelPostExists->money,
                        "deposit" => $request->deposit != null ? $request->deposit : $motelPostExists->deposit,
                        "electric_money" => $request->electric_money != null ? $request->electric_money : $motelPostExists->electric_money,
                        "water_money"  => $request->water_money != null ? $request->water_money : $motelPostExists->water_money,
                        "has_wifi"  => $request->has_wifi != null ? $request->has_wifi : $motelPostExists->has_wifi,
                        "wifi_money" => $request->wifi_money != null ? $request->wifi_money : $motelPostExists->wifi_money,
                        "has_park" => $request->has_park != null ? $request->has_park : $motelPostExists->has_park,
                        "park_money" => $request->park_money != null ? $request->park_money : $motelPostExists->park_money,
                        "province" => $request->province != null ? $request->province : $motelPostExists->province,
                        "district" => $request->district != null ? $request->district : $motelPostExists->district,
                        "wards" => $request->wards != null ? $request->wards : $motelPostExists->wards,
                        "province_name" => Place::getNameProvince($request->province != null ? $request->province : $motelPostExists->province),
                        "district_name" => Place::getNameDistrict($request->district != null ? $request->district : $motelPostExists->district),
                        "wards_name" => Place::getNameWards($request->wards != null ? $request->wards : $motelPostExists->wards),
                        "address_detail" => $request->address_detail,
                        "has_wc" => $request->has_wc != null ? $request->has_wc : $motelPostExists->has_wc,
                        "has_window" => $request->has_window != null ? $request->has_window : $motelPostExists->has_window,
                        "has_security" => $request->has_security != null ? $request->has_security : $motelPostExists->has_security,
                        "has_free_move" => $request->has_free_move != null ? $request->has_free_move : $motelPostExists->has_free_move,
                        "has_own_owner" => $request->has_own_owner != null ? $request->has_own_owner : $motelPostExists->has_own_owner,
                        "has_air_conditioner" => $request->has_air_conditioner != null ? $request->has_air_conditioner : $motelPostExists->has_air_conditioner,
                        "has_water_heater" => $request->has_water_heater != null ? $request->has_water_heater : $motelPostExists->has_water_heater,
                        "has_kitchen" => $request->has_kitchen != null ? $request->has_kitchen : $motelPostExists->has_kitchen,
                        "has_fridge" => $request->has_fridge != null ? $request->has_fridge : $motelPostExists->has_fridge,
                        "has_washing_machine" => $request->has_washing_machine != null ? $request->has_washing_machine : $motelPostExists->has_washing_machine,
                        "has_mezzanine" => $request->has_mezzanine != null ? $request->has_mezzanine : $motelPostExists->has_mezzanine,
                        "has_bed" => $request->has_bed != null ? $request->has_bed : $motelPostExists->has_bed,
                        "has_wardrobe" => $request->has_wardrobe != null ? $request->has_wardrobe : $motelPostExists->has_wardrobe,
                        "has_tivi" => $request->has_tivi != null ? $request->has_tivi : $motelPostExists->has_tivi,
                        "has_pet" => $request->has_pet != null ? $request->has_pet : $motelPostExists->has_pet,
                        "has_balcony" => $request->has_balcony != null ? $request->has_balcony : $motelPostExists->has_balcony,
                        "has_finger_print" => $request->has_finger_print != null ? $request->has_finger_print : $motelPostExists->has_finger_print,
                        "has_kitchen_stuff" => $request->has_kitchen_stuff != null ? $request->has_kitchen_stuff : $motelPostExists->has_kitchen_stuff,
                        "has_table" => $request->has_table != null ? $request->has_table : $motelPostExists->has_table,
                        "has_picture" => $request->has_picture != null ? $request->has_picture : $motelPostExists->has_picture,
                        "has_decorative_lights" => $request->has_decorative_lights != null ? $request->has_decorative_lights : $motelPostExists->has_decorative_lights,
                        "has_tree" => $request->has_tree != null ? $request->has_tree : $motelPostExists->has_tree,
                        "has_pillow" => $request->has_pillow != null ? $request->has_pillow : $motelPostExists->has_pillow,
                        "has_mattress" => $request->has_mattress != null ? $request->has_mattress : $motelPostExists->has_mattress,
                        "has_shoes_rasks" => $request->has_shoes_rasks != null ? $request->has_shoes_rasks : $motelPostExists->has_shoes_rasks,
                        "has_curtain" => $request->has_curtain != null ? $request->has_curtain : $motelPostExists->has_curtain,
                        "has_mirror" => $request->has_mirror != null ? $request->has_mirror : $motelPostExists->has_mirror,
                        "has_sofa" => $request->has_sofa != null ? $request->has_sofa : $motelPostExists->has_sofa,
                        "has_ceiling_fans" => $request->has_ceiling_fans != null ? $request->has_ceiling_fans : $motelPostExists->has_ceiling_fans,
                        "hour_open" => $request->hour_open != null ? $request->hour_open : $motelPostExists->hour_open,
                        "minute_open" => $request->minute_open != null ? $request->minute_open : $motelPostExists->minute_open,
                        "hour_close" => $request->hour_close != null ? $request->hour_close : $motelPostExists->hour_close,
                        "minute_close" => $request->minute_close != null ? $request->minute_close : $motelPostExists->minute_close,
                        "number_floor" => $request->number_floor != null ? $request->number_floor : $motelPostExists->number_floor,
                        "quantity_vehicle_parked" => $request->quantity_vehicle_parked != null ? $request->quantity_vehicle_parked : $motelPostExists->quantity_vehicle_parked,
                        "furniture" =>  $request->furniture != null ? json_encode($request->furniture) : $motelPostExists->furniture,
                        "images" => $request->images != null ? json_encode($request->images) : $motelPostExists->images,
                        "money_commission_admin" => $request->money_commission_admin != null ? $request->money_commission_admin : $motelPostExists->money_commission_admin,
                        "money_commission_user" => $request->user->is_admin == true ? $request->money_commission_user : $motelExists->money_commission_user,
                        "admin_verified" => $adminVerified,
                        "percent_commission" => $request->percent_commission != null ? $request->percent_commission : $motelPostExists->percent_commission,
                        "percent_commission_collaborator" => $request->percent_commission_collaborator != null ? $request->percent_commission_collaborator : $motelPostExists->percent_commission_collaborator
                    ]
                );
            }

            // handle update price motel, mo_post
            try {
                if (!empty($request->tower_id)) {
                    MotelUtils::handleMinMaxTower($request->tower_id);
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
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Motel::where('id', '=', $motelExists->id)->first(),
        ]);
    }


    /**
     * Xóa 1 phòng trọ
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $motel_id = request("motel_id");
        $motelExists = Motel::where([
            ['id', $motel_id]
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

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $idDeleted = $motelExists->id;
        $motelExists->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
            'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
