<?php

namespace App\Http\Middleware;

use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CheckPhoneNumber
{
    public function handle($request, Closure $next)
    {
        if ($request->user->phone_number == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PLEASE_UPDATE_YOUR_NUMBER_PHONE[0],
                'msg' => MsgCode::PLEASE_UPDATE_YOUR_NUMBER_PHONE[1],
            ]);
        }

        return $next($request);
    }
}
