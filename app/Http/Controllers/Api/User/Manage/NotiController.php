<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\NotificationManage;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotiController extends Controller
{
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

        $notis = NotificationManage::where('user_id', $request->user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_unread" => 0,
                "list_notification" => $notis
            ],
        ]);
    }
}
