<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\SystemPermission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PermissionAdminController extends Controller
{
    public function create(Request $request)
    {
        $roleUser = DB::table('system_permissions')
            ->join('user_permissions', 'system_permissions.id', '=', 'user_permissions.system_permission_id')
            ->where('user_id', $request->user->id)
            ->first();

        // if ($roleUser == null) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'msg_code' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[0],
        //         'msg' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[1],
        //         'success' => false,
        //     ]);
        // }

        // if ($roleUser->able_decentralization == false) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
        //         'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
        //         'success' => false,
        //     ]);
        // }


        $systemPermissionCreate = SystemPermission::create([
            'name' => $request->name,
            'description' => $request->description,
            'view_badge' => $request->view_badge ?? false,
            'manage_motel' => $request->manage_motel ?? false,
            'manage_user' => $request->manage_user ?? false,
            'manage_mo_post' => $request->manage_mo_post ?? false,
            'manage_contract' => $request->manage_contract ?? false,
            'manage_renter' => $request->manage_renter ?? false,
            'manage_bill' => $request->manage_bill ?? false,
            'manage_message' => $request->manage_message ?? false,
            'manage_report_problem' => $request->manage_report_problem ?? false,
            'manage_service' => $request->manage_service_sell ?? false,
            'manage_service_sell' => $request->manage_service_sell ?? false,
            'manage_order_service_sell' => $request->manage_order_service_sell ?? false,
            'manage_notification' => $request->manage_notification ?? false,
            'setting_banner' => $request->setting_banner ?? false,
            'setting_contact' => $request->setting_contact ?? false,
            'setting_help' => $request->setting_help ?? false,
            'all_access' => $request->all_access ?? false,
            'setting_category_help' => $request->setting_category_help ?? false,
            'manage_motel_consult' => $request->manage_motel_consult ?? false,
            'manage_report_statistic' => $request->manage_report_statistic ?? false,
            'able_decentralization' => $request->able_decentralization ?? false,
            'manage_collaborator' => $request->manage_collaborator ?? false
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $systemPermissionCreate
        ]);
    }

    public function update(Request $request)
    {
        // $roleUser = DB::table('system_permissions')
        //     ->join('user_permissions', 'system_permissions.id', '=', 'user_permissions.system_permission_id')
        //     ->where('user_id', $request->user->id)
        //     ->first();

        // if ($roleUser == null) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'msg_code' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[0],
        //         'msg' => MsgCode::NO_SYSTEM_PERMISSION_EXISTS[1],
        //         'success' => false,
        //     ]);
        // }

        // if ($roleUser->able_decentralization == false) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
        //         'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
        //         'success' => false,
        //     ]);
        // }
        $systemPermissionExist = SystemPermission::where([
            ['id', $request->system_permission_id]
        ])->first();

        if ($systemPermissionExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
                'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
                'success' => false,
            ]);
        }

        $systemPermissionExist->update([
            'name' => $request->name ?? $systemPermissionExist->name,
            'description' => $request->description ?? $systemPermissionExist->description,
            'all_access' => $request->all_access ?? $systemPermissionExist->all_access,
            'view_badge' => $request->view_badge ?? $systemPermissionExist->view_badge,
            'manage_motel' => $request->manage_motel ?? $systemPermissionExist->manage_motel,
            'manage_user' => $request->manage_user ?? $systemPermissionExist->manage_user,
            'manage_mo_post' => $request->manage_mo_post ?? $systemPermissionExist->manage_mo_post,
            'manage_contract' => $request->manage_contract ?? $systemPermissionExist->manage_contract,
            'manage_renter' => $request->manage_renter ?? $systemPermissionExist->manage_renter,
            'manage_bill' => $request->manage_bill ?? $systemPermissionExist->manage_bill,
            'manage_message' => $request->manage_message ?? $systemPermissionExist->manage_message,
            'manage_report_problem' => $request->manage_report_problem ?? $systemPermissionExist->manage_report_problem,
            'manage_service' => $request->manage_service ?? $systemPermissionExist->manage_service,
            'manage_service_sell' => $request->manage_service_sell ?? $systemPermissionExist->manage_service_sell,
            'manage_order_service_sell' => $request->manage_order_service_sell ?? $systemPermissionExist->manage_order_service_sell,
            'manage_notification' => $request->manage_notification ?? $systemPermissionExist->manage_notification,
            'setting_banner' => $request->setting_banner ?? $systemPermissionExist->setting_banner,
            'setting_contact' => $request->setting_contact ?? $systemPermissionExist->setting_contact,
            'setting_help' => $request->setting_help ?? $systemPermissionExist->setting_help,
            'setting_category_help' => $request->setting_category_help ?? $systemPermissionExist->setting_category_help,
            'manage_motel_consult' => $request->manage_motel_consult ?? $systemPermissionExist->manage_motel_consult,
            'manage_report_statistic' => $request->manage_report_statistic ?? $systemPermissionExist->manage_report_statistic,
            'manage_collaborator' => $request->manage_collaborator ?? $systemPermissionExist->manage_collaborator,
            'able_decentralization' => $request->able_decentralization ?? $systemPermissionExist->able_decentralization
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $systemPermissionExist
        ]);
    }

    public function getOne(Request $request, $id)
    {
        $systemPermissionExist = SystemPermission::where('id', $id)->first();

        if ($systemPermissionExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
                'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
                'success' => false,
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $systemPermissionExist
        ]);
    }

    public function Delete(Request $request, $id)
    {
        // $roleUser = DB::table('system_permissions')
        //     ->join('user_permissions', 'system_permissions.id', '=', 'user_permissions.system_permission_id')
        //     ->where('user_id', $request->user->id)
        //     ->first();

        // if ($roleUser->able_decentralization == false) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
        //         'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
        //         'success' => false,
        //     ]);
        // }
        $systemPermissionExist = SystemPermission::where([
            ['id', $id],
        ])->first();

        if ($systemPermissionExist->able_remove == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::THIS_ROLE_UNABLE_REMOVE[0],
                'msg' => MsgCode::THIS_ROLE_UNABLE_REMOVE[1],
                'success' => false,
            ]);
        }

        if ($systemPermissionExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_UNAUTHORIZED,
                'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
                'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
                'success' => false,
            ]);
        }

        UserPermission::where('system_permission_id', $id)->delete();
        $systemPermissionExist->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true
        ]);
    }

    public function getALl(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $listSystemPermission = SystemPermission::paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listSystemPermission,
        ]);
    }
}
