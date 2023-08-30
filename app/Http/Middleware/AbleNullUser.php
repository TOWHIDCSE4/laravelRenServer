<?php

namespace App\Http\Middleware;

use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Models\SessionUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AbleNullUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = request()->header('token');

        $checkTokenIsValidForUser = SessionUser::where('token', $token)->first();

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

            // if ($user->phone_verified_at == null && $user->email_verified_at == null) {
            //     return ResponseUtils::json([
            //         'code' => Response::HTTP_UNAUTHORIZED,
            //         'msg_code' => MsgCode::UNVERIFIED_ACCOUNT[0],
            //         'msg' => MsgCode::UNVERIFIED_ACCOUNT[1],
            //         'success' => false,
            //     ]);
            // }


            $request->merge([
                'user' => User::where('id', $checkTokenIsValidForUser->user_id)->first(),
            ]);
        }

        return $next($request);
    }
}
