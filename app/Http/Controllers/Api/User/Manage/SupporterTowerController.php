<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\PhoneUtils;
use App\Helper\ResponseUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\ConnectManageMotel;
use App\Models\ConnectManageTower;
use App\Models\MsgCode;
use App\Models\SupporterManageTower;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SupporterTowerController extends Controller
{
    /**
     *
     * Thêm 1 người hỗ trợ quản lý tòa nhà
     *
     * @bodyParam name string tên người đại diện
     * @bodyParam phone_number string tên người đại diện
     * @bodyParam email string tên người đại diện
     *
     */
    public function create(Request $request)
    {
        if ($request->phone_number == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
            ]);
        }

        if (!PhoneUtils::isNumberPhoneValid($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        $user = User::where([['phone_number', $request->phone_number], ['is_host', true]])->first();

        if (!$user) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_HOST_EXISTS[0],
                'msg' => MsgCode::NO_HOST_EXISTS[1],
            ]);
        }
        $supporterManageTower = DB::table('supporter_manage_towers')
            ->where([
                ['phone_number', $request->phone_number],
                ['host_id', $request->user->id]
            ])
            ->first();

        if ($supporterManageTower) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::SUPPORTER_MANAGE_TOWER_ALREADY_EXITS[0],
                'msg' => MsgCode::SUPPORTER_MANAGE_TOWER_ALREADY_EXITS[1],
            ]);
        }

        if ($request->towers != null && is_array($request->towers)) {
            foreach ($request->towers as $tower) {
                $towerId = $tower['id'];
                $motelIds = [];

                if ($towerId == null) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::TOWER_ID_IS_REQUIRED[0],
                        'msg' => MsgCode::TOWER_ID_IS_REQUIRED[1],
                    ]);
                }

                if (isset($tower['motel']) && is_array($tower['motel'])) {
                    foreach ($tower['motel'] as $motel) {
                        if ($motel['id'] == null) {
                            return ResponseUtils::json([
                                'code' => Response::HTTP_BAD_REQUEST,
                                'success' => false,
                                'msg_code' => MsgCode::MOTEL_ID_IS_REQUIRED[0],
                                'msg' => MsgCode::MOTEL_ID_IS_REQUIRED[1],
                            ]);
                        }
                        array_push($motelIds, $motel['id']);
                    }
                }

                $towerExist = DB::table('towers')->where([
                    ['id', $towerId],
                    ['user_id', $request->user->id],
                ])->first();

                if (!$towerExist) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                        'msg' => MsgCode::NO_TOWER_EXISTS[1],
                    ]);
                }

                $countCheckMotel = DB::table('motels')->whereIn('id', $motelIds)
                    ->where([
                        ['user_id', $request->user->id],
                        ['tower_id', $towerId],
                    ])
                    ->count();

                if (count($motelIds) != $countCheckMotel) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_LIST_MOTEL_ID[0],
                        'msg' => MsgCode::INVALID_LIST_MOTEL_ID[1],
                    ]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $supporterManageTower = SupporterManageTower::create([
                "supporter_id" => $user->id,
                "host_id" => $request->user->id,
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
            ]);

            if ($request->towers != null && is_array($request->towers)) {
                foreach ($request->towers as $tower) {
                    ConnectManageTower::create([
                        "supporter_manage_tower_id" => $supporterManageTower->id,
                        "tower_id" => $tower['id'],
                    ]);

                    if (isset($tower['motel']) && is_array($tower['motel'])) {
                        foreach ($tower['motel'] as $motel) {
                            ConnectManageMotel::create([
                                "supporter_manage_tower_id" => $supporterManageTower->id,
                                "tower_id" => $tower['id'],
                                "motel_id" => $motel['id'],
                            ]);
                        }
                    }
                }
            }

            if ($user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    'Quản lý toàn nhà',
                    'Bạn đã được thêm quyền quản lý toàn nhà của chủ nhà ' . $request->user->name,
                    TypeFCM::ADD_ROLE_MANAGE_TOWER,
                    NotiUserDefineCode::USER_IS_HOST,
                    $supporterManageTower->id
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
            'data' => $supporterManageTower,
        ]);
    }

    /**
     *
     * Cập nhật 1 người hỗ trợ quản lý tòa nhà
     *
     * @bodyParam name string tên người đại diện
     * @bodyParam phone_number string tên người đại diện
     * @bodyParam email string tên người đại diện
     *
     */
    public function update(Request $request)
    {

        $supporterManageTower = SupporterManageTower::where([
            ['id', $request->supporter_manage_tower_id],
            ['host_id', $request->user->id],
        ])->first();

        if (!$supporterManageTower) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPORTER_MANAGE_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPORTER_MANAGE_TOWER_EXISTS[1],
            ]);
        }

        if ($request->phone_number != null && !PhoneUtils::isNumberPhoneValid($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        if ($request->phone_number != null) {
            $supporter = User::where([['phone_number', $request->phone_number], ['is_host', true]])->first();

            if (!$supporter) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_USER_EXISTS[0],
                    'msg' => MsgCode::NO_USER_EXISTS[1],
                ]);
            }
        }
        if ($request->towers != null && is_array($request->towers)) {
            $groupedTowers = [];
            foreach ($request->towers as $tower) {
                $id = $tower['id'];
                if (!isset($groupedTowers[$id])) {
                    $groupedTowers[$id] = $tower;
                } else {
                    $groupedTowers[$id]['motel'] = array_merge($groupedTowers[$id]['motel'], $tower['motel']);

                    $groupedTowers[$id]['motel'] = array_map("unserialize", array_unique(array_map("serialize", $groupedTowers[$id]['motel'])));
                }
            }
        }

        $handleDoubleTowers = ['towers' => array_values($groupedTowers)];



        DB::beginTransaction();
        try {
            $supporterManageTower->update([
                "supporter_id" =>  $request->phone_number != null ? $supporter->id : $supporterManageTower->supporter_id,
                "name" => $request->name != null ? $request->name : $supporterManageTower->name,
                "phone_number" => $request->phone_number != null ? $request->phone_number : $supporterManageTower->phone_number,
                "email" => $request->email != null ? $request->email : $supporterManageTower->email,
            ]);

            if ($handleDoubleTowers != null && is_array($handleDoubleTowers)) {
                ConnectManageTower::where('supporter_manage_tower_id', $supporterManageTower->id)->delete();
                ConnectManageMotel::where('supporter_manage_tower_id', $supporterManageTower->id)->delete();

                foreach ($handleDoubleTowers['towers'] as $tower) {
                    ConnectManageTower::create([
                        "supporter_manage_tower_id" => $supporterManageTower->id,
                        "tower_id" => $tower['id'],
                    ]);

                    if (isset($tower['motel']) && is_array($tower['motel'])) {
                        foreach ($tower['motel'] as $motel) {
                            ConnectManageMotel::create([
                                "supporter_manage_tower_id" => $supporterManageTower->id,
                                "tower_id" => $tower['id'],
                                "motel_id" => $motel['id'],
                            ]);
                        }
                    }
                }
            }

            if ($supporter) {
                NotificationUserJob::dispatch(
                    $supporter->id,
                    'Quản lý toàn nhà',
                    'Bạn đã được thêm quyền quản lý toàn nhà của chủ nhà ' . $request->user->name,
                    TypeFCM::ADD_ROLE_MANAGE_TOWER,
                    NotiUserDefineCode::USER_IS_HOST,
                    $supporterManageTower->id
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
            'data' => $supporterManageTower,
        ]);
    }

    /**
     *
     * Lấy 1 người hỗ trợ quản lý tòa nhà
     *
     */
    public function getOne(Request $request)
    {
        $supporterManageTower = SupporterManageTower::where([
            ['id', $request->supporter_manage_tower_id],
            ['host_id', $request->user->id],
        ])->first();

        if (!$supporterManageTower) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SUPPORTER_MANAGE_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_SUPPORTER_MANAGE_TOWER_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $supporterManageTower,
        ]);
    }

    /**
     *
     * Xóa tòa nhà hỗ trợ quản lý
     *
     */
    public function deleteTowerManageSupport(Request $request)
    {
        if ($request->tower_ids != null && is_array($request->tower_ids) && $request->supporter_manage_tower_id != null) {
            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                ->where('host_id', $request->user->id)
                ->where('id', $request->supporter_manage_tower_id)
                ->pluck('id');
            ConnectManageTower::join('supporter_manage_towers', 'connect_manage_towers.supporter_manage_tower_id', '=', 'supporter_manage_towers.id')
                ->where('supporter_manage_towers.host_id', $request->user->id)
                ->where('supporter_manage_towers.id', $request->supporter_manage_tower_id)
                ->whereIn('supporter_manage_towers.id', $supporterManageTowerIds)
                ->whereIn('connect_manage_towers.tower_id', $request->tower_ids)
                ->delete();
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
     * List quản lý tòa nhà
     *
     *
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $supporterManageTower = SupporterManageTower::where('host_id', $request->user->id)
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $supporterManageTower,
        ]);
    }

    /**
     * Xóa hỗ trợ tòa nhà
     *
     */
    public function delete(Request $request)
    {
        if (is_array($request->support_manage_tower_ids)) {

            $supporterManageTower = SupporterManageTower::where('host_id', $request->user->id)->whereIn('id', $request->support_manage_tower_ids)->delete();
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
