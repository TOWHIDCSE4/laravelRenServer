<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Models\MsgCode;
use App\Jobs\NotificationUserJob;
use App\Jobs\NotificationUserJobTest;
use App\Models\NotificationUser;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminNotiController extends Controller
{

    /**
     * Tạo thông báo đẩy
     * 
     * @queryBody title string 
     * @queryBody content string
     * @queryBody type 
     * @queryBody role 
     * 
     */
    public function Create(Request $request)
    {
        if (NotiUserDefineCode::getStatusMotelCode($request->role) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_ROLE_USER[0],
                'msg' => MsgCode::INVALID_ROLE_USER[1],
            ]);
        }

        if ($request->role == NotiUserDefineCode::USER_IS_ADMIN) {
            NotificationAdminJob::dispatch(
                null,
                $request->title,
                $request->content,
                $request->type ?? "ADMIN_NOTIFICATION",
                $request->role,
                null
            );
        } else {
            NotificationUserJob::dispatch(
                $request->user_id != null ? $request->user_id : null,
                $request->title,
                $request->content,
                $request->type ?? "ADMIN_NOTIFICATION",
                $request->role,
                $request->referral_value != null ? $request->referral_value : null
            );
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_CREATED,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }


    /**
     * Tạo thông báo đẩy
     * 
     * @queryBody title string 
     * @queryBody content string
     * @queryBody type 
     * @queryBody role 
     * 
     */
    public function CreateTest(Request $request)
    {
        if (NotiUserDefineCode::getStatusMotelCode($request->role) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_ROLE_USER[0],
                'msg' => MsgCode::INVALID_ROLE_USER[1],
            ]);
        }


        NotificationUserJobTest::dispatch(
            $request->user_id != null ? $request->user_id : null,
            $request->title,
            $request->content,
            $request->type ?? "ADMIN_NOTIFICATION",
            $request->role,
            $request->referral_value != null ? $request->referral_value : null,
            $request->deviceTokens,
        );



        return ResponseUtils::json([
            'code' => Response::HTTP_CREATED,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    public function getAll(Request $request)
    {
        if ($request->user == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => [
                    "total_unread" => 0,
                    "list_notification" => [
                        "current_page" => 1,
                        "data" => []
                    ]
                ],
            ]);
        }

        $notices = NotificationUser::where(function ($query) {
            if (request('user')->is_admin == true) {
                if (request('user')->is_host == true) {
                    $query->whereIn('role', [NotiUserDefineCode::USER_IS_ADMIN, NotiUserDefineCode::ALL_USER_IN_SYSTEM]);
                } else {
                    $query->whereIn('role', [NotiUserDefineCode::USER_IS_HOST, NotiUserDefineCode::USER_IS_ADMIN, NotiUserDefineCode::ALL_USER_IN_SYSTEM]);
                }
            } else if (request('user')->is_host == true) {
                $query->whereIn('role', [NotiUserDefineCode::USER_IS_HOST, NotiUserDefineCode::ALL_USER_IN_SYSTEM]);
            } else {
                $query->whereIn('role', [NotiUserDefineCode::USER_NORMAL, NotiUserDefineCode::ALL_USER_IN_SYSTEM]);
            }
        })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_unread" => 0,
                "list_notification" => $notices
            ],
        ]);
    }

    /**
     * Đã đọc tất cả
     * 
     */
    public function readAll(Request $request)
    {
        if ($request->user != null) {
            NotificationUser::where('user_id', $request->user->id)
                ->update(['unread' => false]);
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
