<?php

namespace App\Http\Middleware;

use App\Helper\ResponseUtils;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\SessionAdmin;
use App\Models\SessionEmployee;
use App\Models\SessionUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminLogin
{

    public function handle($request, Closure $next)
    {

        $token = request()->header('token');

        $checkTokenIsValidForUser = SessionUser::where('token', $token)->first();

        if (empty($token)) {
            return ResponseUtils::json([
                'code' => 401,
                'msg_code' => MsgCode::NO_TOKEN[0],
                'msg' => MsgCode::NO_TOKEN[1],
                'success' => false,
            ],);
        }


        if (empty($checkTokenIsValidForUser)) {
            return ResponseUtils::json([
                'code' => 401,
                'msg_code' => MsgCode::NOT_HAVE_ACCESS[0],
                'msg' => MsgCode::NOT_HAVE_ACCESS[1],
                'success' => false,
            ]);
        }

        if ($checkTokenIsValidForUser) {
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

            if ($user->is_admin != true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'msg_code' => MsgCode::ERROR[0],
                    'msg' => "Bạn không phải admin",
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
