<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\ViewerPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ViewerPostController extends Controller
{
    public function update(Request $request)
    {
        $viewUserExist = ViewerPost::where([
            ['user_id', $request->user->id],
            ['mo_post_id', request('mo_post_id')]
        ])
            ->first();

        if ($viewUserExist == null) {
            $postExist = DB::table('mo_posts')->where('id', request('mo_post_id'))->first();
            if ($postExist == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_POST_EXISTS[0],
                    'msg' => MsgCode::NO_POST_EXISTS[1],
                ]);
            }

            ViewerPost::create([
                'mo_post_id' => request('mo_post_id'),
                'user_id' => $request->user->id
            ]);
        }
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
