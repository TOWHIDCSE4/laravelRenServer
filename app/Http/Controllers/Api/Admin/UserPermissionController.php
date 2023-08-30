<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserPermissionController extends Controller
{
    public function update(Request $request)
    {
        $userExist = User::where([
            ['id', $request->user_id],
        ])->first();

        if ($userExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
                'success' => false,
            ]);
        }

        if ($userExist->is_admin == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'msg_code' => MsgCode::ACCOUNT_NOT_ADMIN[0],
                'msg' => MsgCode::ACCOUNT_NOT_ADMIN[1],
                'success' => false,
            ]);
        }

        $userPermissionExist = UserPermission::where([
            ['user_id', $request->user_id]
        ])->first();

        if ($userPermissionExist == null) {
            $userPermissionExist = UserPermission::create([
                'user_id' => $request->user_id,
                'system_permission_id' => 5
            ]);
        }

        if (!DB::table('system_permissions')->where('id', $request->system_permission_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'msg_code' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[0],
                'msg' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[1],
                'success' => false,
            ]);
        }

        $userPermissionExist->update([
            'system_permission_id' => $request->system_permission_id
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $userPermissionExist
        ]);
    }
}
