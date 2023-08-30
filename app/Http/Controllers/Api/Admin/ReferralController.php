<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
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

        $listUserReferralCode = DB::table('users')
            ->whereNotNull('referral_code')
            ->distinct()
            ->pluck('referral_code');

        $users = User::whereIn('self_referral_code', $listUserReferralCode)
            ->when(isset($request->referral_code), function ($query) use ($request) {
                $query->where('referral_code',  $request->referral_code);
            })
            ->when(isset($request->is_used_referral_code), function ($query) use ($request) {
                $isUseReferralCode = filter_var($request->is_used_referral_code ?: false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $query->where('has_referral_code',  $isUseReferralCode);
            })
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->select(
                'users.*',
                DB::raw("(SELECT SUM(money_commission_user) FROM collaborator_refer_motels
                WHERE collaborator_refer_motels.user_id = users.id
                GROUP BY collaborator_refer_motels.user_id ) as total_commission_received")
            )
            ->distinct()
            ->when($request->search != null, function ($query) {
                $query->search(request('search'));
            })
            ->when(User::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

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
    public function getListUserUseReferralCode(Request $request)
    {
        $referralCode = request("referral_code");
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

        $listUserUseReferralCode = User::where('referral_code', $referralCode)
            ->select('users.*')
            ->distinct()
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->select(
                'users.*',
                DB::raw("(SELECT SUM(money_commission_user) FROM collaborator_refer_motels
                WHERE collaborator_refer_motels.user_referral_id = users.id
                GROUP BY collaborator_refer_motels.user_referral_id ) as total_commission_received")
            )
            ->when($request->search != null, function ($query) {
                $query->search(request('search'));
            })
            ->when(User::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listUserUseReferralCode
        ]);
    }
}
