<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Tower;
use App\Models\TowerMotel;
use App\Models\Motel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TowerMotelController extends Controller
{
    /**
     * Cập nhật 1 tòa nhà
     * 
     * @bodyParam tower_id int mã tòa nhà
     * @bodyParam list_motel_id array mã tòa nhà
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
     * 
     */
    public function addMotelTower(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $tower_id = request("tower_id");
        $towerMotelTemp = [];
        $towerExists = Tower::where('id', $tower_id)
            // ->where(function ($query) use ($request) {
            //     if ($request->user != null && $request->user->is_admin != true) {
            //         $query->where('user_id', $request->user->id);
            //     }
            // })
            ->where('user_id', $request->user->id)
            ->first();

        if ($towerExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
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
        } else {
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
                'is_room_hidden' => 0,
                'status' => 0,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        if (count($towerMotelTemp) > 0) {
            TowerMotel::where([
                ['tower_id' => $towerExists->id],
                ['motel_id' => $motelId]
            ])->delete();
            TowerMotel::insert($towerMotelTemp);
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
     * @bodyParam tower_motel_id int mã phòng tòa nhà
     * 
     */
    public function deleteMotelTower(Request $request)
    {
        $towerExists = TowerMotel::where([
            ['id', $request->tower_motel_id],
            ['user_id', $request->user->id]
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
                'msg_code' => MsgCode::NO_TOWER_MOTEL_ID_EXISTS[0],
                'msg' => MsgCode::NO_TOWER_MOTEL_ID_EXISTS[1]
            ]);
        }

        $towerExists->delete();

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
     * @bodyParam tower_motel_id int mã phòng tòa nhà
     * 
     */
    public function getAllMotelTower(Request $request)
    {

        if ($request->tower_id == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_TOWER_EXISTS[1]
            ]);
        }
        $towerExists = TowerMotel::where([
            ['tower_id', $request->tower_id],
            ['user_id', $request->user->id]
        ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->paginate(20);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $towerExists,
        ]);
    }
    // Tower Romm -> Hidden or Unhidden 
    public function updateTowerByRoom(Request $request)
    {
        $TowerMotel_is_room = Motel::where('id', $request->motel_id)->first();
        if ($TowerMotel_is_room == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[0],
                'msg' => MsgCode::NO_CATEGORY_SERVICE_SELL_EXISTS[1],
            ]);
        }

        $TowerMotel_is_room->update([
            'is_room_hidden' => $request->is_room_hidden != null ? $request->is_room_hidden : $TowerMotel_is_room->is_room_hidden,
        ]);
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $TowerMotel_is_room,
        ]);
    }
}
