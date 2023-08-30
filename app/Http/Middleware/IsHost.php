<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use Illuminate\Http\Response;

class IsHost
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
        if ($request->user->is_host == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'msg_code' => MsgCode::YOU_ARE_NOT_THE_HOST[0],
                'msg' => MsgCode::YOU_ARE_NOT_THE_HOST[1],
                'success' => false,
            ]);
        }

        return $next($request);
    }
}
