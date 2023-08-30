<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PermissionAdmin
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
        // $currentUri = $request->getRequestUri();
        // $permissionAdmin = DB::table('user_permissions')
        //     ->join('system_permissions', 'user_permissions.system_permission_id', '=', 'system_permissions.id')
        //     ->where('user_id', $request->user->id)->first();

        // if ($permissionAdmin == null) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_UNAUTHORIZED,
        //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
        //         'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
        //         'success' => false,
        //     ]);
        // }

        // if ($permissionAdmin->all_access != true) {
        //     if ($permissionAdmin->manage_motel == false && str_contains($currentUri, 'admin/motels')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_MOTEL[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_MOTEL[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_user == false && str_contains($currentUri, 'admin/user')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_mo_post == false && str_contains($currentUri, 'admin/mo_posts')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_MO_POST[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_MO_POST[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_contract == false && str_contains($currentUri, 'admin/contracts')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_CONTRACT[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_CONTRACT[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_bill == false && str_contains($currentUri, 'admin/bills')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_message == false && str_contains($currentUri, 'admin/person_chat')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_report_problem == false && str_contains($currentUri, 'admin/report_problem')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_REPORT_PROBLEM[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_REPORT_PROBLEM[1],
        //             'success' => false,
        //         ]);
        //     }
        //     //  else if ($permissionAdmin->manage_service == false && str_contains($currentUri, 'admin/user')) {
        //     //     return ResponseUtils::json([
        //     //         'code' => Response::HTTP_UNAUTHORIZED,
        //     //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[0],
        //     //         'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[1],
        //     //         'success' => false,
        //     //     ]);
        //     // }
        //     else if ($permissionAdmin->manage_service_sell == false && str_contains($currentUri, 'admin/service_sells')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_SERVICE_SELL[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_SERVICE_SELL[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_order_service_sell == false && str_contains($currentUri, 'admin/order_service_sell')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_ORDER_SERVICE_SELL[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_ORDER_SERVICE_SELL[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->manage_notification == false && str_contains($currentUri, 'admin/notifications')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_NOTIFICATION[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_NOTIFICATION[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->setting_banner == false && str_contains($currentUri, 'admin/banners')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_SETTING_BANNER[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_SETTING_BANNER[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->setting_contact == false && str_contains($currentUri, 'admin/admin_contact')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_SETTING_CONTACT[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_SETTING_CONTACT[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->setting_help == false && str_contains($currentUri, 'admin/help_post')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_HELP_POST[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_HELP_POST[1],
        //             'success' => false,
        //         ]);
        //     } else if ($permissionAdmin->setting_category_help == false && str_contains($currentUri, 'admin/category_help_post')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_CATEGORY_HELP_POST[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_CATEGORY_HELP_POST[1],
        //             'success' => false,
        //         ]);
        //     }
        //     // else if ($permissionAdmin->manage_motel_consult == false && str_contains($currentUri, 'admin/user')) {
        //     //     return ResponseUtils::json([
        //     //         'code' => Response::HTTP_UNAUTHORIZED,
        //     //         'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[0],
        //     //         'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_USER[1],
        //     //         'success' => false,
        //     //     ]);
        //     // }
        //     else if ($permissionAdmin->manage_report_statistic == false && str_contains($currentUri, 'admin/report_statistic')) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_UNAUTHORIZED,
        //             'msg_code' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_REPORT_STATISTIC[0],
        //             'msg' => MsgCode::NO_PERMISSION_ACCESS_MANAGE_REPORT_STATISTIC[1],
        //             'success' => false,
        //         ]);
        //     }
        // }

        return $next($request);
    }
}
