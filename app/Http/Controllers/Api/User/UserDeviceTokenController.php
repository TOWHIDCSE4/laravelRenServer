<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group  User/Device token
 */
class UserDeviceTokenController extends Controller
{
    /**
     * Đăng ký device token
     * @bodyParam device_id string required device_id
     * @bodyParam device_type string required 0 android | 1 ios
     * @bodyParam device_token string required device_token
     */
    public function updateDeviceTokenUser(Request $request)
    {
        if ($request->user == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
            ]);
        }

        if ($request->device_token == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DEVICE_TOKEN[0],
                'msg' => MsgCode::NO_DEVICE_TOKEN[1],
            ]);
        }

        $checkDeviceTokenExists = null;

        // UserDeviceToken::where(
        //     'user_id',
        //     $request->user->id
        // )->where('device_token', '!=', $request->device_token)->delete();

        $checkDeviceTokenExists = UserDeviceToken::where(
            'device_token',
            $request->device_token
        )->where(
            'user_id',
            $request->user->id
        )->first();

        if ($checkDeviceTokenExists == null) {
            $checkDeviceTokenExists =  UserDeviceToken::create(
                [
                    'user_id' => $request->user->id,
                    'device_id' =>  $request->device_id,
                    'device_type' => $request->device_type,
                    'device_token' => $request->device_token,
                    'active' => true
                ]
            );
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $checkDeviceTokenExists
        ]);
    }
    /**
     * Đăng ký device token
     * @bodyParam device_id string required device_id
     * @bodyParam device_type string required 0 android | 1 ios
     * @bodyParam device_token string required device_token
     */
    public function removeDeviceTokenUser(Request $request)
    {
        if (
            $request->user_id != null &&
            $request->device_token != null
        ) {
            UserDeviceToken::where(
                [
                    ['user_id', $request->user_id],
                    ['device_token', $request->device_token],
                ]
            )->delete();
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
