<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\HelpFindMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminHelpFindMotelController extends Controller
{
    /**
     * Xóa HelpFindMotel
     * 
     * @bodyParam list_id_help_fine_motel
     * 
     */
    public function delete(Request $request)
    {
        $IdDeleted = [];

        if (is_array($request->list_id_help_fine_motel)) {
            foreach ($request->list_id_help_fine_motel as $IdHelpFindMotel) {
                $existHelpFindMotel = HelpFindMotel::where('id', $IdHelpFindMotel)->first();
                if ($existHelpFindMotel != null) {
                    array_push($IdDeleted, $existHelpFindMotel->id);
                    $existHelpFindMotel->delete();
                }
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_ID_HELP_FIND_MOTEL[0],
                'msg' => MsgCode::INVALID_LIST_ID_HELP_FIND_MOTEL[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['list_id_help_find_motel_deleted' => $IdDeleted]
        ]);
    }

    /**
     * Lấy danh sách phòng
     */
    public function getAll(Request $request)
    {

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => HelpFindMotel::get()
        ]);
    }
}
