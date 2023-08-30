<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\AdminContact;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThemeController extends Controller
{
    /**
     * Lấy contact admin
     */
    public function getContact(Request $request)
    {
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminContact::first()
        ]);
    }

    /**
     * Lấy discover admin
     */
    public function getTheme(Request $request)
    {
        # code...
    }
}
