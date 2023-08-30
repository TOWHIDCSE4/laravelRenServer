<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\HelpFindMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpFindMotelController extends Controller
{
    /**
     * check username
     * 
     * @bodyParam name string tên
     * @bodyParam facebook string
     * @bodyParam zalo string
     * @bodyParam phone_number string
     * @bodyParam content string
     */
    public function create(Request $request)
    {
        if ($request->name == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        if ($request->phone_number == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
            ]);
        }

        if ($request->content == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
            ]);
        }

        HelpFindMotel::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'content' => $request->content,
            'facebook' => $request->facebook,
            'zalo' => $request->zalo
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
