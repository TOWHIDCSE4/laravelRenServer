<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\AdminContact;
use App\Models\HelpFindMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminContactController extends Controller
{
    /**
     * Xóa contact admin
     * 
     */
    public function delete(Request $request)
    {

        // AdminContact::delete();

        // return ResponseUtils::json([
        //     'code' => Response::HTTP_OK,
        //     'success' => true,
        //     'msg_code' => MsgCode::SUCCESS[0],
        //     'msg' => MsgCode::SUCCESS[1],
        //     'data' => ['list_id_help_find_motel_deleted' => $IdDeleted]
        // ]);
    }

    /**
     * Lấy contact
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
     *  Cập nhật thông tin liên lạc admin
     * 
     * @bodyParam email string 
     * @bodyParam facebook string
     * @bodyParam zalo string
     * @bodyParam phone_number string
     * @bodyParam content string
     * @bodyParam address id
     * 
     */
    public function update(Request $request)
    {
        DB::table('admin_contacts')->delete();

        AdminContact::create([
            'facebook' => $request->facebook,
            'zalo' => $request->zalo,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'hotline' => $request->hotline,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'bank_name' => $request->bank_name,
            'content' => $request->content,
            "address" => $request->address
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminContact::first()
        ]);
    }
}
