<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusFindFastMotelDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StatusOrderServicesSellDefineCode;
use App\Helper\StatusReportPostViolationDefineCode;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\StatusReservationMotelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\ConfigAdmin;
use App\Models\Motel;
use App\Models\MsgCode;
use App\Models\PersonChats;
use App\Models\Renter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BadgesController extends Controller
{
    public function getBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $totalUser = 0;
        (int)$total_cart = 0;
        $total_renter = 0;
        $totalPostFindMotel = 0;
        $totalPostRoommate = 0;
        $total_user_chat = 0;
        $chat_unread = 0;
        $totalQuantityProblem = 0;
        $totalQuantityProblemDone = 0;
        $totalQuantityProblemNotDone = 0;
        $totalQuantityContractRented = 0;
        $totalQuantityContractPending = 0;
        $totalQuantityBillsNeedPaid = 0;
        $totalMoneyBillsNeedPaid = 0;
        $total_money_need_payment = 0;
        $total_motel_manage = 0;
        $total_motel_rented_manage = 0;
        $total_motel_favorite_manage = 0;
        $total_motel_available_manage = 0;
        $total_renter_rented_manage = 0;
        $total_problem_done_manage = 0;
        $total_problem_not_done_manage = 0;
        $totalContractActiveManage = 0;
        $totalContractPendingManage = 0;
        $totalContractExpiredManage = 0;
        $totalMoneyBillsManage = 0;
        $totalQuantityBillsManage = 0;
        $totalQuantityPendingPaymentBillsManage = 0;
        $totalQuantityWaitConfirmBillsManage = 0;
        $totalMoneyCommissionMustPaidForAdminManage = 0;
        $totalQuantityReservationNotConsult = 0;
        $totalQuantityReservationConsulted = 0;
        $totalContractAdmin = 0;
        $totalContractActiveAdmin = 0;
        $totalContractPendingAdmin = 0;
        $totalContractExpiredAdmin = 0;
        $totalRenterHasMotelAdmin = 0;
        $totalRenterHasNotMotelAdmin = 0;
        $totalRenterUnconfirmedMotelAdmin = 0;
        $totalHostAccountAdmin = 0;
        $totalQuantityOrderInTimeAdmin = 0;
        $totalQuantityOrderProgressingAdmin = 0;
        $totalQuantityOrderCancelAdmin = 0;
        $totalQuantityOrderCompletedAdmin = 0;
        $totalQuantityBillsAdmin = 0;
        $totalQuantityPendingPaymentBillsAdmin = 0;
        $totalQuantityWaitConfirmBillsAdmin = 0;
        $totalMoPostAdmin = 0;
        $totalMoPostFindMotelAdmin = 0;
        $totalMoPostRoommateAdmin = 0;
        $totalMoPostAdmin = 0;
        $totalMotelAdmin = 0;
        $totalProblemDoneAdmin = 0;
        $totalProblemNotDoneAdmin = 0;
        $totalQuantityFindFastMotelConsultedAdmin = 0;
        $totalQuantityFindFastMotelNotConsultAdmin = 0;
        $totalQuantityReservationNotConsultAdmin = 0;
        $totalQuantityReservationConsultedAdmin = 0;
        $totalQuantityReportViolationPostProgressingAdmin = 0;
        $totalQuantityReportViolationPostCompletedAdmin = 0;
        $currentUser = null;
        $totalBillMotelToCollect = 0;
        $currentVersion = null;

        $notificationUnread = 0;

        if ($dateFrom != null || $dateTo != null) {
            if (($dateFrom != null && $dateTo != null) && (Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))) {
                $dateFrom = $dateFrom . ' 00:00:01';
                $dateTo = $dateTo . ' 23:59:59';
            } else if ($dateFrom != null && Helper::validateDate($dateFrom, 'Y-m-d')) {
                $dateFrom = $dateFrom . ' 00:00:01';
            } else if ($dateTo != null && Helper::validateDate($dateTo, 'Y-m-d')) {
                $dateTo = $dateTo . ' 23:59:59';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        }

        if ($request->user != null) {
            $ReadNotiAdmins =  DB::table('read_noti_admins')
                ->where('user_id', $request->user->id)
                ->pluck('noti_user_id');

            $notificationUnread = DB::table('notification_users')
                ->where([
                    ['unread', true]
                ])
                ->where(function ($query) {
                    if (request('user')->is_admin == true) {
                        if (request('user')->is_host == true) {
                            $query->where('user_id', request('user')->id)
                                ->orWhereNull('user_id');
                            $query->whereIn('role', [
                                NotiUserDefineCode::USER_IS_HOST,
                                NotiUserDefineCode::USER_IS_ADMIN,
                                NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                            ]);
                        } else {
                            $query->where('user_id', request('user')->id)
                                ->orWhereNull('user_id');
                            $query->whereIn('role', [
                                NotiUserDefineCode::USER_IS_ADMIN,
                                NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                            ]);
                        }
                    } else if (request('user')->is_host == true) {
                        $query->where('user_id', request('user')->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_IS_HOST,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM
                        ]);
                    } else {
                        $query->where('user_id', request('user')->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_NORMAL,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM
                        ]);
                    }
                })
                ->whereNotIN('id', $ReadNotiAdmins)
                ->count();

            $totalQuantityContractRented = DB::table('contracts')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED]
                ])
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('created_at', '<=', $dateTo);
                })
                ->count();

            $totalQuantityContractPending = DB::table('contracts')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::PROGRESSING]
                ])
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('created_at', '<=', $dateTo);
                })
                ->count();

            $totalQuantityProblem = DB::table('report_problems')
                ->where('user_id', $request->user->id)
                ->count();

            $totalQuantityProblemDone = DB::table('report_problems')
                ->where([
                    ['user_id', $request->user->id],
                    ['report_problems.status', StatusReportProblemDefineCode::COMPLETED]
                ])
                ->count();

            $totalQuantityProblemNotDone = DB::table('report_problems')
                ->where([
                    ['user_id', $request->user->id],
                    ['report_problems.status', StatusReportProblemDefineCode::PROGRESSING]
                ])
                ->count();

            $totalQuantityBillsNeedPaid = DB::table('bills')
                ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['bills.status', StatusBillDefineCode::PROGRESSING],
                    ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ])
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('created_at', '<=', $dateTo);
                })
                ->distinct()
                ->count();

            $totalMoneyBillsNeedPaid = DB::table('bills')
                ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['bills.status', StatusBillDefineCode::PROGRESSING],
                    ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ])
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('created_at', '<=', $dateTo);
                })
                ->distinct()
                ->sum('total_final');


            // management
            $totalPostFindMotel = DB::table('mo_post_find_motels')
                ->where('user_id', $request->user->id)
                ->count();
            $totalPostRoommate = DB::table('mo_post_roommates')
                ->where('user_id', $request->user->id)
                ->count();
            if ($request->user->is_host == true) {

                $totalContractActiveManage = DB::table('contracts')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        ['status', StatusContractDefineCode::COMPLETED]
                    ])
                    ->count();

                $totalContractPendingManage = DB::table('contracts')
                    ->where([
                        ['contracts.user_id', $request->user->id]
                    ])
                    ->whereIn('contracts.status', [StatusContractDefineCode::WAITING_CONFIRM, StatusContractDefineCode::PROGRESSING])
                    ->count();

                $totalContractExpiredManage = DB::table('contracts')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        // ['status', StatusContractDefineCode::COMPLETED],
                        ['contracts.rent_to', '<', Helper::getTimeNowDateTime()]
                    ])
                    ->count();

                $totalQuantityBillsManage = DB::table('bills')
                    ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                    ])
                    ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING, StatusBillDefineCode::WAIT_FOR_CONFIRM])
                    ->select('bills.*')
                    ->count();

                $totalQuantityPendingPaymentBillsManage =  DB::table('bills')
                    ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                    ])
                    ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING])
                    ->select('bills.*')
                    ->count();

                $totalQuantityWaitConfirmBillsManage =  DB::table('bills')
                    ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                    ])
                    ->whereIn('bills.status', [StatusBillDefineCode::WAIT_FOR_CONFIRM])
                    ->select('bills.*')
                    ->count();

                $totalMoneyBillsManage = DB::table('bills')
                    ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.user_id', $request->user->id],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                    ])
                    ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING, StatusBillDefineCode::WAIT_FOR_CONFIRM])
                    ->select('bills.*')
                    ->sum('total_final');

                $total_renter_rented_manage = DB::table('renters')
                    ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['contracts.user_id', $request->user->id]
                    ])
                    ->where(function ($query) use ($request) {
                        if ($request->user->is_host)
                            $query->where('renter_phone_number', '<>', $request->user->phone_number);
                    })
                    ->groupBy('phone_number')
                    ->distinct('renters.phone_number')
                    ->get()
                    ->count();

                $total_renter = DB::table('renters')
                    ->where([
                        ['is_hidden', false],
                    ])
                    ->where(function ($query) use ($request) {
                        $query->where('renters.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                                ->where('supporter_id', $request->user->id)
                                ->pluck('id');

                            $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                                ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                                ->distinct()
                                ->pluck('motels.id');

                            $renterIds = Renter::whereIn('renters.motel_id', $motelIds)
                                ->pluck('renters.id')->toArray();
                            $q->whereIn('renters.id', $renterIds);
                        });
                    })
                    ->where(function ($query) use ($request) {
                        if ($request->user->is_host)
                            $query->where('phone_number', '<>', $request->user->phone_number);
                    })
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('created_at', '<=', $dateTo);
                    })
                    ->distinct('phone_number')
                    ->count();

                $total_motel_favorite_manage = DB::table('motel_favorites')
                    ->where('user_id', $request->user->id)
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('created_at', '<=', $dateTo);
                    })
                    ->count();

                $total_motel_manage = DB::table('motels')
                    ->where(function ($query) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->distinct()
                            ->pluck('id');

                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->when($request->tower_id != null, function ($subQ) use ($request) {
                                $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                            })
                            ->distinct()
                            ->pluck('motels.id');
                        $query->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds);
                    })
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('created_at', '<=', $dateTo);
                    })
                    ->count();

                $total_motel_rented_manage = DB::table('motels')
                    ->join('contracts', 'motels.id', '=', 'contracts.motel_id')
                    ->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
                    ])
                    ->where(function ($query) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->distinct()
                            ->pluck('id');

                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->when($request->tower_id != null, function ($subQ) use ($request) {
                                $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                            })
                            ->orderByDesc('id')
                            ->distinct()
                            ->pluck('motels.id');
                        $query->where('motels.user_id', $request->user->id)->orWhereIn('motels.id', $motelIds);
                    })
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('motels.created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('motels.created_at', '<=', $dateTo);
                    })
                    ->distinct('motels.id')
                    ->count();

                $total_motel_available_manage = Motel::where([
                    ['motels.status', StatusMotelDefineCode::MOTEL_EMPTY],
                ])
                    ->where(function ($query) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->distinct()
                            ->pluck('id');

                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->when($request->tower_id != null, function ($subQ) use ($request) {
                                $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                            })
                            ->distinct()
                            ->pluck('motels.id');
                        $query->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds);
                    })
                    ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                        if (isset($dateFrom) && isset($dateTo)) {
                            $query->where('motels.created_at', '>=', $dateFrom);
                            $query->where('motels.created_at', '<=', $dateTo);
                        } else if (isset($dateFrom)) {
                            $query->where('motels.created_at', '>=', $dateFrom);
                        } else if (isset($dateTo)) {
                            $query->where('motels.created_at', '<=', $dateTo);
                        }
                    })
                    ->count();


                $total_problem_done_manage = DB::table('report_problems')
                    ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                    ->where([
                        ['motels.user_id', $request->user->id],
                        ['report_problems.status', StatusReportProblemDefineCode::COMPLETED]
                    ])
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('report_problems.created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('report_problems.created_at', '<=', $dateTo);
                    })
                    ->count();

                $total_problem_not_done_manage = DB::table('report_problems')
                    ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                    ->where([
                        ['motels.user_id', $request->user->id],
                        ['report_problems.status', StatusReportProblemDefineCode::PROGRESSING]
                    ])
                    ->when($dateFrom != null, function ($query) use ($dateFrom) {
                        $query->where('report_problems.created_at', '>=', $dateFrom);
                    })
                    ->when($dateTo != null, function ($query) use ($dateTo) {
                        $query->where('report_problems.created_at', '<=', $dateTo);
                    })
                    ->count();

                $totalQuantityReservationNotConsult = DB::table('reservation_motels')
                    ->whereNotNull('host_id')
                    ->when($request->user != null, function ($query) {
                        $query->where('host_id', request('user')->id);
                    })
                    ->where('status', StatusReservationMotelDefineCode::NOT_CONSULT)
                    ->count();

                $totalQuantityReservationConsulted = DB::table('reservation_motels')
                    ->whereNotNull('host_id')
                    ->when($request->user != null, function ($query) {
                        $query->where('host_id', request('user')->id);
                    })
                    ->where('status', StatusReservationMotelDefineCode::CONSULTED)
                    ->count();

                $totalMoneyCommissionMustPaidForAdminManage = DB::table('collaborator_refer_motels')
                    ->join('contracts', 'collaborator_refer_motels.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['collaborator_refer_motels.status', StatusCollaboratorReferMotelDefineCode::COMPLETED]
                    ])
                    ->select('collaborator_refer_motels.*')
                    ->distinct()
                    ->sum('money_commission_admin');
            }


            $total_user_chat = PersonChats::where([
                ['user_id', $request->user->id],
            ])
                ->count();

            $chat_unread = PersonChats::where([
                ['user_id', $request->user->id],
                ['seen', false],
                ['is_helper', false],
            ])
                ->where('last_list_mo_post_id', '=', '[]')
                ->whereNotNull('user_id')
                ->count();

            $total_money_need_payment = DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['bills.status', StatusBillDefineCode::PROGRESSING],
                    ['bills.is_init', StatusBillDefineCode::BILL_BY_MONTH],
                    ['contracts.status', StatusContractDefineCode::COMPLETED]
                ])
                ->sum('bills.total_final');

            $total_cart = DB::table('item_cart_service_sells')
                ->where('user_id', $request->user->id)
                ->count();

            $currentUser = User::where('id', $request->user->id)->first();

            $list_motel_rented = DB::table('motels')
                ->join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                ])
                ->distinct()
                ->select('motels.province', 'motels.district', 'motels.wards', 'motels.motel_name', 'motels.province_name', 'motels.district_name', 'motels.wards_name', 'motels.address_detail')
                ->get();

            // $currentUser->system_permission = SystemPermission::join('user_permissions', 'system_permissions.id', '=', 'user_permissions.system_permission_id')
            //     ->where('user_permissions.user_id', $currentUser->id)
            //     ->select('system_permissions.*')
            //     ->first();

            $currentUser->list_motel_rented = $list_motel_rented;

            $currentUser->e_wallet_collaborator = DB::table('e_wallet_collaborators')
                ->where('user_id', $request->user->id)
                ->select('account_balance')
                ->first();

            $currentUser->list_address_user = DB::table('motels')
                ->join('user_contracts', 'motels.id', '=', 'user_contracts.user_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                ])
                ->distinct('province', 'district', 'wards', 'address_detail')
                ->select('motels.province', 'motels.district', 'motels.wards', 'motels.motel_name', 'motels.province_name', 'motels.district_name', 'motels.wards_name', 'motels.address_detail')
                ->get();

            $totalBillMotelToCollect = DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING, StatusBillDefineCode::WAIT_FOR_CONFIRM])
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['contracts.user_id', $request->user->id]
                ])
                ->sum('total_final');

            // ADMIN badges
            $totalContractAdmin = DB::table('contracts')->count();

            $totalContractActiveAdmin = DB::table('contracts')
                ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->count();

            $totalContractPendingAdmin = DB::table('contracts')
                // ->whereIn('status', [StatusContractDefineCode::WAITING_CONFIRM, StatusContractDefineCode::PROGRESSING])
                ->where('status', StatusContractDefineCode::WAITING_CONFIRM)
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->count() + DB::table('contracts')
                ->where('status',  StatusContractDefineCode::PROGRESSING)
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->count();

            $totalContractExpiredAdmin = DB::table('contracts')
                ->where([
                    ['contracts.status', StatusContractDefineCode::WAITING_CONFIRM],
                    ['contracts.rent_to', '<', Helper::getTimeNowDateTime()]
                ])
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->count();


            $totalRenterUnconfirmedMotelAdmin = DB::table('renters')
                ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where('contracts.status', StatusContractDefineCode::PROGRESSING)
                ->where(function ($query) use ($request) {
                    if ($request->user->is_host)
                        $query->where('phone_number', '<>', $request->user->phone_number);
                })
                ->select('renters.*')
                ->distinct('renters.phone_number')
                ->count();

            $totalRenterHasNotMotelAdmin = DB::table('renters')
                ->whereNotIn('phone_number', function ($q) {
                    $q->select('renter_phone_number')->from('user_contracts');
                })
                ->where(function ($query) use ($request) {
                    if ($request->user->is_host)
                        $query->where('phone_number', '<>', $request->user->phone_number);
                })
                ->count();

            $totalRenterHasMotelAdmin = DB::table('renters')
                ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                ->where(function ($query) use ($request) {
                    if ($request->user->is_host)
                        $query->where('phone_number', '<>', $request->user->phone_number);
                })
                ->select('renters.*')
                ->distinct('renters.phone_number')
                ->count();

            $totalQuantityReservationNotConsultAdmin = DB::table('reservation_motels')
                ->where('status', StatusReservationMotelDefineCode::NOT_CONSULT)
                ->count();

            $totalQuantityReservationConsultedAdmin = DB::table('reservation_motels')
                ->where('status', StatusReservationMotelDefineCode::CONSULTED)
                ->count();

            // $total_motel_rented = Contract::join('motels', 'contracts.motel_id', '=', 'motels.id')
            //     ->where([
            //         ['contracts.user_id', $user_id],
            //         ['contracts.status', StatusContractDefineCode::COMPLETED],
            //         ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
            //     ])
            //     ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //         $query->where('contracts.created_at', '>=', $dateFrom);
            //     })
            //     ->when($dateTo != null, function ($query) use ($dateTo) {
            //         $query->where('contracts.created_at', '<=', $dateTo);
            //     })
            //     ->count();

            $totalUser = DB::table('users')->count();

            $totalContractActiveAdmin = DB::table('contracts')
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->where('status', StatusContractDefineCode::COMPLETED)->count();

            $totalContractPendingAdmin = DB::table('contracts')
                ->when($request->user != null, function ($query) {
                    $query->where('user_id', '<>', request('user')->id);
                })
                ->where('status', StatusContractDefineCode::PROGRESSING)->count();

            $totalProblemDoneAdmin = DB::table('report_problems')
                ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                ->when($request->user != null, function ($query) {
                    $query->where('motels.user_id', '<>', request('user')->id);
                })
                ->where('report_problems.status', StatusReportProblemDefineCode::COMPLETED)
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('report_problems.created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('report_problems.created_at', '<=', $dateTo);
                })
                ->count();

            $totalProblemNotDoneAdmin = DB::table('report_problems')
                ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                ->when($request->user != null, function ($query) {
                    $query->where('motels.user_id', '<>', request('user')->id);
                })
                ->where('report_problems.status', StatusReportProblemDefineCode::PROGRESSING)
                ->when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('report_problems.created_at', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('report_problems.created_at', '<=', $dateTo);
                })
                ->count();

            $totalQuantityFindFastMotelNotConsultAdmin = DB::table('find_fast_motels')
                ->where('status', StatusFindFastMotelDefineCode::NOT_CONSULT)
                ->count();
            $totalQuantityFindFastMotelConsultedAdmin = DB::table('find_fast_motels')
                ->where('status', StatusFindFastMotelDefineCode::CONSULTED)
                ->count();

            $totalHostAccountAdmin = DB::table('users')->where('is_host', true)->count();

            $totalQuantityOrderInTimeAdmin = DB::table('order_service_sells')
                ->where(function ($query) {
                    $query->where('created_at', '<=', date("Y-m-t", strtotime(Carbon::now())));
                    $query->where('created_at', '>=', Carbon::now()->format('Y-m-01'));
                })
                ->count();

            $totalQuantityOrderProgressingAdmin = DB::table('order_service_sells')
                ->where('order_status', StatusOrderServicesSellDefineCode::PROGRESSING)
                ->count();
            $totalQuantityOrderCancelAdmin = DB::table('order_service_sells')
                ->where('order_status', StatusOrderServicesSellDefineCode::CANCEL_ORDER)
                ->count();
            $totalQuantityOrderCompletedAdmin = DB::table('order_service_sells')
                ->where('order_status', StatusOrderServicesSellDefineCode::COMPLETED)
                ->count();

            $totalMoPostAdmin = DB::table('mo_posts')->count();
            $totalMoPostFindMotelAdmin = DB::table('mo_post_find_motels')->count();
            $totalMoPostRoommateAdmin = DB::table('mo_post_roommates')->count();

            $totalMotelAdmin = DB::table('motels')->count();

            $totalQuantityBillsAdmin = DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ])
                ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING, StatusBillDefineCode::WAIT_FOR_CONFIRM])
                ->select('bills.*')
                ->count();

            $totalQuantityPendingPaymentBillsAdmin =  DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ])
                ->whereIn('bills.status', [StatusBillDefineCode::PROGRESSING])
                ->select('bills.*')
                ->count();


            $totalQuantityWaitConfirmBillsAdmin =  DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ])
                ->whereIn('bills.status', [StatusBillDefineCode::WAIT_FOR_CONFIRM])
                ->select('bills.*')
                ->count();

            $totalQuantityReportViolationPostCompletedAdmin = DB::table('report_post_violations')
                ->where('status', StatusReportPostViolationDefineCode::COMPLETED)
                ->count();

            $totalQuantityReportViolationPostProgressingAdmin = DB::table('report_post_violations')
                ->where('status', StatusReportPostViolationDefineCode::PROGRESSING)
                ->count();
        }

        $configAdmin = ConfigAdmin::first();
        $currentVersion = $configAdmin->current_version ?? '1.0.0';



        $badges = [
            'total_user' => $totalUser,
            'total_cart' => (int)$total_cart,
            'total_post_find_motel' => $totalPostFindMotel,
            'total_post_roommate' => $totalPostRoommate,
            'total_renter' => $total_renter,
            'total_quantity_problem' => $totalQuantityProblem,
            'total_quantity_problem_done' => $totalQuantityProblemDone,
            'total_quantity_problem_not_done' => $totalQuantityProblemNotDone,
            'total_quantity_contract_rented' => $totalQuantityContractRented,
            'total_quantity_contract_pending' => $totalQuantityContractPending,
            'total_quantity_bills_need_paid' => $totalQuantityBillsNeedPaid,
            'total_money_bills_need_paid' => $totalMoneyBillsNeedPaid,
            'total_user_chat' => $total_user_chat,
            'chat_unread' => $chat_unread,
            'total_money_need_payment' => $total_money_need_payment,
            'total_reservation_motel_not_consult' => $totalQuantityReservationNotConsult,
            'total_reservation_motel_consulted' => $totalQuantityReservationConsulted,
            'total_motel_manage' => $total_motel_manage,
            'total_motel_rented_manage' => $total_motel_rented_manage,
            'total_motel_favorite_manage' => $total_motel_favorite_manage,
            'total_motel_available_manage' => $total_motel_available_manage,
            'total_renter_rented_manage' => $total_renter_rented_manage,
            'total_contract_active_manage' => $totalContractActiveManage,
            'total_contract_pending_manage' => $totalContractPendingManage,
            'total_contract_expired_manage' => $totalContractExpiredManage,
            'total_money_bills_manage' => $totalMoneyBillsManage,
            'total_quantity_bills_manage' => $totalQuantityBillsManage,
            'total_quantity_wait_confirm_bills_manage' => $totalQuantityWaitConfirmBillsManage,
            'total_quantity_pending_payment_bills_manage' => $totalQuantityPendingPaymentBillsManage,
            'total_problem_done_manage' => $total_problem_done_manage,
            'total_problem_not_done_manage' => $total_problem_not_done_manage,
            'total_money_commission_must_paid_for_admin_manage' => $totalMoneyCommissionMustPaidForAdminManage,
            'total_contract_admin' => $totalContractAdmin,
            'total_contract_active_admin' => $totalContractActiveAdmin,
            'total_contract_pending_admin' => $totalContractPendingAdmin,
            'total_contract_expired_admin' => $totalContractExpiredAdmin,
            'total_renter_has_motel_admin' => $totalRenterHasMotelAdmin,
            'total_renter_has_not_motel_admin' => $totalRenterHasNotMotelAdmin,
            'total_renter_unconfirmed_motel_admin' => $totalRenterUnconfirmedMotelAdmin,
            'total_problem_done_admin' => $totalProblemDoneAdmin,
            'total_problem_not_done_admin' => $totalProblemNotDoneAdmin,
            'total_host_account_admin' => $totalHostAccountAdmin,
            'total_order_admin' => $totalQuantityOrderInTimeAdmin,
            'total_quantity_order_progressing_admin' => $totalQuantityOrderProgressingAdmin,
            'total_quantity_order_cancel_admin' => $totalQuantityOrderCancelAdmin,
            'total_quantity_order_completed_admin' => $totalQuantityOrderCompletedAdmin,
            'total_mo_post_admin' => $totalMoPostAdmin,
            'total_mo_post_find_motel_admin' => $totalMoPostFindMotelAdmin,
            'total_mo_post_roommate_admin' => $totalMoPostRoommateAdmin,
            'total_motel_admin' => $totalMotelAdmin,
            'total_quantity_bills_admin' => $totalQuantityBillsAdmin,
            'total_quantity_wait_confirm_bills_admin' => $totalQuantityWaitConfirmBillsAdmin,
            'total_quantity_pending_payment_bills_admin' => $totalQuantityPendingPaymentBillsAdmin,
            'total_reservation_motel_consulted_admin' => $totalQuantityReservationConsultedAdmin,
            'total_reservation_motel_not_consult_admin' => $totalQuantityReservationNotConsultAdmin,
            'total_find_fast_motel_not_consult_admin' => $totalQuantityFindFastMotelNotConsultAdmin,
            'total_find_fast_motel_consulted_admin' => $totalQuantityFindFastMotelConsultedAdmin,
            'total_quantity_report_violation_post_completed_admin' => $totalQuantityReportViolationPostCompletedAdmin,
            'total_quantity_report_violation_post_progressing_admin' => $totalQuantityReportViolationPostProgressingAdmin,
            'total_motel_bill' => $totalBillMotelToCollect,
            'notification_unread' => $notificationUnread,
            'version' => $currentVersion,
            'current_user' => $currentUser,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $badges,
        ]);
    }
}
