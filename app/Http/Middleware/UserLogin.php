<?php

namespace App\Http\Middleware;

use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Models\SessionStaff;
use App\Models\SessionUser;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class UserLogin
{
    public function handle($request, Closure $next)
    {

        $token = request()->header('token');
        if (empty($token)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ],);
        }

        $checkTokenIsValidForUser = SessionUser::where('token', $token)->first();

        if (empty($checkTokenIsValidForUser)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                'success' => false,
            ]);
        }

        if (!empty($checkTokenIsValidForUser)) {
            $user = User::where('id', $checkTokenIsValidForUser->user_id)->first();
            if ($user == null) {
                $checkTokenIsValidForUser->delete();
                return ResponseUtils::json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                    'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                    'success' => false,
                ]);
            }

            $request->merge([
                'user' => User::where('id', $checkTokenIsValidForUser->user_id)->first(),
            ]);
        }

        return $next($request);
    }
}
