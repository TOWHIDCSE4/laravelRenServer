<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\ConfigAdmin;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ConfigAdminController extends Controller
{

    public function getAdminConfig(Request $request)
    {
        $configAdmin = ConfigAdmin::first();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configAdmin
        ]);
    }


    public function setCurrentVersionAdmin(Request $request)
    {
        $configAdmin = ConfigAdmin::first();
        if ($configAdmin == null) {
            $configAdmin = ConfigAdmin::create([
                'current_version' => $request->current_version,
                'intro_app' => $request->intro_app,
            ]);
        } else {
            $configAdmin->update([
                'intro_app' => $request->intro_app,
                'current_version' => $request->current_version
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configAdmin
        ]);
    }
}
