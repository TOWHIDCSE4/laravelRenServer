<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\AccountRankDefineCode;
use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\NotiUserDefineCode;
use App\Helper\PaginateArr;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\StatusUserDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\Contract;
use App\Models\EWalletCollaborator;
use App\Models\HistoryPotentialUser;
use App\Models\ItemCartServiceSell;
use App\Models\LastSeenMoPost;
use App\Models\LineItemServiceSell;
use App\Models\Message;
use App\Models\MoPost;
use App\Models\MoPostFavorite;
use App\Models\MoPostFindMotel;
use App\Models\MoPostRoommate;
use App\Models\Motel;
use App\Models\MotelFavorite;
use App\Models\User;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use App\Models\OrderServiceSell;
use App\Models\PersonChats;
use App\Models\PotentialUser;
use App\Models\Renter;
use App\Models\ReportPostViolation;
use App\Models\ReportProblem;
use App\Models\ReservationMotel;
use App\Models\RoomChat;
use App\Models\SessionUser;
use App\Models\UserContract;
use App\Models\UserDeviceToken;
use App\Models\UserPermission;
use App\Models\UToUMessages;
use App\Models\ViewerPost;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group Admin/Quản lý/User
 */

class UserController extends Controller
{
    /**
     * 
     * Danh sách user
     * 
     * @bodyParam name string tên người dùng
     * @bodyParam number_phone số điện thoại người dùng
     * @bodyParam email string email
     * @bodyParam date_from datetime ngày bắt đầu
     * @bodyParam date_to datetime ngày kết thúc 
     * @bodyParam descending boolean sắp xếp theo (default true)
     * @bodyParam sort_by string sắp xếp theo tên cột (account_rank, name)
     * @bodyParam limit int Số lượng bản ghi sẽ lấy
     * 
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;
        $descending = filter_var(($request->descending ?: true), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($dateFrom != null || $dateTo != null) {
            if ($dateFrom != null && $dateTo != null) {
                if (
                    !Helper::validateDate($dateFrom, 'Y-m-d')
                    || !Helper::validateDate($dateTo, 'Y-m-d')
                ) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateFrom != null) {
                if (!Helper::validateDate($dateFrom, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateTo != null) {
                if (!Helper::validateDate($dateTo, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
        }

        $users = User::when(isset($request->is_rented), function ($query) use ($request) {

            $hasContract = isset($request->is_rented) ? filter_var($request->is_rented, FILTER_VALIDATE_BOOLEAN) : null;
            if ($hasContract) {
                $query->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number');
                $query->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id');
                $query->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['users.is_host', '<>', true]
                ]);
            } else {
                $query->where('users.is_host', '<>', true);
                $query->whereNotIn('phone_number', DB::table('user_contracts')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                    ->distinct()
                    ->pluck('renter_phone_number')
                    ->toArray());
            }
        })
            ->when(isset($request->referral_code), function ($query) use ($request) {
                $query->where('referral_code',  $request->referral_code);
            })
            ->when(isset($request->is_used_referral_code), function ($query) use ($request) {
                $isUseReferralCode = filter_var($request->is_used_referral_code ?: false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $query->where('has_referral_code',  $isUseReferralCode);
            })
            ->when(isset($request->is_host), function ($query) use ($request) {
                $isHost = filter_var($request->is_host ?: false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $query->where('is_host',  $isHost);
            })
            ->when(isset($request->is_admin), function ($query) use ($request) {
                $isHost = filter_var($request->is_admin ?: false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $query->where('is_admin',  $isHost);
            })
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->select('users.*')
            ->distinct()
            ->when($request->search != null, function ($query) {
                $query->search(request('search'));
            })
            ->when(User::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->get();

        if (isset($request->is_host) && $request->avg_resolved_problem == true) {
            $ReadNotiAdmins =  DB::table('read_noti_admins')
                ->where('user_id', $request->user->id)
                ->pluck('noti_user_id');

            // $users = $users->get();
            foreach ($users as $user) {
                $years = 0;
                $months = 0;
                $days = 0;
                $hours = 0;
                $minutes = 0;
                $seconds = 0;
                $timediff = DB::table('report_problems')
                    ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                    ->select(DB::raw('DATE(report_problems.created_at) AS start_date, AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at))) AS timediff, MINUTE(AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at)))) AS minute_avg'))
                    ->where('report_problems.status', StatusReportProblemDefineCode::COMPLETED)
                    ->where('report_problems.user_id', $user->id)
                    ->groupBy('start_date')
                    ->first();

                $dt = Carbon::now();
                if ($timediff != null) {
                    $years = $dt->diffInYears($dt->copy()->addSeconds($timediff->timediff));
                    $months = $dt->diffInMonths($dt->copy()->addSeconds($timediff->timediff));
                    $days = $dt->diffInDays($dt->copy()->addSeconds($timediff->timediff));
                    $hours = $dt->diffInHours($dt->copy()->addSeconds($timediff->timediff)->subDays($days));
                    $minutes = $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours));
                    $seconds = $dt->diffInSeconds($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours)->subMinutes($minutes));
                }
                $user->avg_minutes_resolved_problem = $timediff ? $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)) : 0;

                // get total noti unread
                $user->noti_unread = NotificationUser::where('role', NotiUserDefineCode::ALL_USER_IN_SYSTEM)
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
                    ->whereNotIn('id', $ReadNotiAdmins)
                    ->count();
            }
            $users = $users->sortByDesc('avg_minutes_resolved_problem');

            $newUser = [];
            foreach ($users as $user) {
                array_push($newUser, $user);
            }

            $users = PaginateArr::paginate($newUser, $limit);
        } else {
            $users = $users->paginate($limit);
        }



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $users
        ]);
    }


    /**
     * Thong tin 1 user
     * 
     */
    public function getOne(Request $request)
    {

        $user_id = request("user_id");

        $userExists = User::where(
            'id',
            $user_id
        )
            ->first();

        if ($userExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ], 404);
        }

        $years = 0;
        $months = 0;
        $days = 0;
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        $timediff = DB::table('report_problems')
            ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
            ->select(DB::raw('DATE(report_problems.created_at) AS start_date, AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at))) AS timediff, MINUTE(AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at)))) AS minute_avg'))
            ->where('report_problems.status', StatusReportProblemDefineCode::COMPLETED)
            ->where('report_problems.user_id', $userExists->id)
            ->groupBy('start_date')
            ->first();

        $dt = Carbon::now();
        if ($timediff != null) {
            $years = $dt->diffInYears($dt->copy()->addSeconds($timediff->timediff));
            $months = $dt->diffInMonths($dt->copy()->addSeconds($timediff->timediff));
            $days = $dt->diffInDays($dt->copy()->addSeconds($timediff->timediff));
            $hours = $dt->diffInHours($dt->copy()->addSeconds($timediff->timediff)->subDays($days));
            $minutes = $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours));
            $seconds = $dt->diffInSeconds($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours)->subMinutes($minutes));
        }

        $userExists->avg_minutes_resolved_problem = $timediff ? $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)) : 0;


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $userExists,
        ], 200);
    }


    /**
     * 
     * Cập nhật user (chủ nhà, xếp hạng chủ nhà, xếp hạng tài khoản)
     * 
     * @bodyParam host_rank
     * @bodyParam account_rank
     * @bodyParam is_host
     * 
     */
    public function updateUser(Request $request, $id)
    {
        $userExisted = User::where('id', $id)->first();
        $isHost = filter_var($request->is_host ?: false, FILTER_VALIDATE_BOOLEAN) ? true : false;
        $isAdmin = filter_var($request->is_admin ?: false, FILTER_VALIDATE_BOOLEAN) ? true : false;
        // $checkUserIsHost = false;

        if ($userExisted == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ]);
        }

        if (isset($request->host_rank)) {
            if (HostRankDefineCode::getHostRankCode($request->host_rank, true) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_VALUE_PASSED[0],
                    'msg' => MsgCode::INVALID_VALUE_PASSED[1],
                ]);
            }
        }

        if (isset($request->status)) {
            if (StatusUserDefineCode::getStatusAccountCode($request->status) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_USER_STATUS[0],
                    'msg' => MsgCode::INVALID_USER_STATUS[1],
                ]);
            }
        }

        if (isset($request->account_rank)) {
            if (AccountRankDefineCode::getAccountRankCode($request->account_rank, true) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_VALUE_PASSED[0],
                    'msg' => MsgCode::INVALID_VALUE_PASSED[1],
                ]);
            }
        }


        if (isset($request->is_admin) && $request->is_admin == false) {
            $initialAccountType = $userExisted->initial_account_type;
            $checkUserIsHost = DB::table('motels')
                ->where('motels.user_id', $userExisted->id)
                ->exists();
            if ($checkUserIsHost) {
                $initialAccountType = StatusUserDefineCode::USER_IS_HOST;
            }
            $userExisted->update([
                'initial_account_type' => $initialAccountType
            ]);
        }
        if ($request->is_admin != null && $isAdmin == true) {
            $setAdmin = $isAdmin;
            $setHost = true;
        } else {
            $setHost = $request->is_host != null ? $isHost : $userExisted->is_host;
            if ($userExisted->is_host) {
                $setHost = true;
            } else if ($userExisted->initial_account_type == StatusUserDefineCode::NORMAL_ACCOUNT) {
                $setHost = false;
            }
            $setAdmin = $isAdmin;
        }
        if ($request->host_rank == HostRankDefineCode::VIP && $userExisted->host_rank != HostRankDefineCode::VIP) {
            NotificationUserJob::dispatch(
                $userExisted->id,
                "Thông báo tài khoản",
                'Tài khoản của bạn đã nâng cấp lên tài khoản ' . HostRankDefineCode::getHostRankCode(2, false),
                TypeFCM::UP_RANK_TO_HOST,
                NotiUserDefineCode::USER_IS_HOST,
                $userExisted->id
            );
        }

        $userExisted->update(
            [
                'account_rank' => isset($request->account_rank) ? $request->account_rank : $userExisted->account_rank,
                'host_rank' => isset($request->host_rank) ? $request->host_rank : $userExisted->host_rank,
                'is_host'  => $setHost,
                'is_admin'  => $setAdmin,
                'is_choosed_decent ' => $request->is_host == true ? 1 : 0,
                'status' => isset($request->status) ? $request->status : $userExisted->status,
            ]
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => User::where('id', $userExisted->id)->first()
        ]);
    }

    /**
     * 
     * Cấp chủ nhà
     * 
     * 
     */
    public function delete(Request $request)
    {

        $user_id = request("user_id");

        $userExists = User::where('id', $user_id)->first();


        if ($userExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ]);
        }

        Contract::where('user_id', $user_id)->delete();
        Motel::where('user_id', $user_id)->delete();
        MoPost::where('user_id', $user_id)->delete();
        UserContract::where('user_id', $user_id)->delete();
        UserContract::where('renter_phone_number', $userExists->phone_number)->delete();
        EWalletCollaborator::where('user_id', $user_id)->delete();
        LastSeenMoPost::where('user_id', $user_id)->delete();
        LineItemServiceSell::where('user_id', $user_id)->delete();
        Message::where('user_id', $user_id)->delete();
        MotelFavorite::where('user_id', $user_id)->delete();
        MoPostFavorite::where('user_id', $user_id)->delete();
        NotificationUser::where('user_id', $user_id)->delete();
        OrderServiceSell::where('user_id', $user_id)->delete();
        OrderServiceSell::where('phone_number',  $userExists->phone_number)->delete();
        PersonChats::where('user_id', $user_id)->delete();
        Renter::where('user_id', $user_id)->delete();
        ReportPostViolation::where('user_id', $user_id)->delete();
        ReportProblem::where('user_id', $user_id)->delete();
        ReservationMotel::where('user_id', $user_id)->delete();
        RoomChat::where('user_id', $user_id)->delete();
        ItemCartServiceSell::where('user_id', $user_id)->delete();
        LineItemServiceSell::where('user_id', $user_id)->delete();
        SessionUser::where('user_id', $user_id)->delete();
        UserDeviceToken::where('user_id', $user_id)->delete();
        UserPermission::where('user_id', $user_id)->delete();
        UToUMessages::where('user_id', $user_id)->delete();
        ViewerPost::where('user_id', $user_id)->delete();
        Withdrawal::where('user_id', $user_id)->delete();
        MoPostRoommate::where('user_id', $user_id)->delete();
        MoPostFindMotel::where('user_id', $user_id)->delete();
        PotentialUser::where('user_guest_id', $user_id)->delete();
        HistoryPotentialUser::where('user_guest_id', $user_id)->delete();
        DB::table('read_noti_admins')->where('user_id', $user_id)->delete();

        $userExists->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }
}
