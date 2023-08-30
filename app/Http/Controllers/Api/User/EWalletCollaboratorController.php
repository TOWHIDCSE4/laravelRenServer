<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AccountRankDefineCode;
use App\Helper\DatetimeUtils;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusWithdrawalDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\EWalletCollaboratorHistory;
use App\Models\MsgCode;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EWalletCollaboratorController extends Controller
{
    public function getHistoryEWalletCollaborator(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $listHistoryEWalletUser = EWalletCollaboratorHistory::join('e_wallet_collaborators', 'e_wallet_collaborator_histories.e_wallet_collaborator_id', '=', 'e_wallet_collaborators.id')
            ->where('user_id', $request->user->id)
            ->select('e_wallet_collaborator_histories.*')
            ->when($sortBy != null && Schema::hasColumn('e_wallet_collaborator_histories', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listHistoryEWalletUser
        ]);
    }

    /**
     * yêu cầu rút tiền
     */
    public function requestWithdrawal(Request $request)
    {
        if ($request->user->account_rank != AccountRankDefineCode::LOYAL) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::FUNC_REQUIRE_ACCOUNT_RANK_LOYAL[0],
                'msg' => MsgCode::FUNC_REQUIRE_ACCOUNT_RANK_LOYAL[1]
            ]);
        }

        $accountBalance = DB::table('e_wallet_collaborators')
            ->where('user_id', $request->user->id)
            ->first();

        if ($accountBalance == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NOT_E_WALLET_EXISTS[0],
                'msg' => MsgCode::NOT_E_WALLET_EXISTS[1]
            ]);
        }

        if (DB::table('withdrawals')->where([['user_id', $request->user->id], ['status', StatusWithdrawalDefineCode::PROGRESSING]])->first() != null) {
            // return ResponseUtils::json([
            //     'code' => Response::HTTP_BAD_REQUEST,
            //     'success' => false,
            //     'msg_code' => MsgCode::REQUEST_WITHDRAWAL_PREVIOUS_NO_HANDLE[0],
            //     'msg' => MsgCode::REQUEST_WITHDRAWAL_PREVIOUS_NO_HANDLE[1]
            // ]);
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REQUEST_WITHDRAWAL_PREVIOUS_NO_HANDLE[0],
                'msg' => MsgCode::REQUEST_WITHDRAWAL_PREVIOUS_NO_HANDLE[1]
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->amount_money < 0) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_MONEY[0],
                'msg' => MsgCode::INVALID_MONEY[1]
            ]);
        }

        if ($request->amount_money > $accountBalance->account_balance) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WITHDRAWAL_MONEY_CANNOT_GREATER_THAN_BALANCE[0],
                'msg' => MsgCode::WITHDRAWAL_MONEY_CANNOT_GREATER_THAN_BALANCE[1]
            ]);
        }

        $withdrawalCreate = Withdrawal::create([
            'user_id' => $request->user->id,
            'amount_money' => $request->amount_money,
            'status' => StatusWithdrawalDefineCode::PROGRESSING
        ]);

        NotificationUserJob::dispatch(
            null,
            "Yêu cầu rút tiền mới",
            'Yêu cầu rút tiền mới từ người dùng ' . $request->user->name,
            TypeFCM::NEW_REQUEST_WITHDRAWAL,
            NotiUserDefineCode::USER_IS_ADMIN,
            $withdrawalCreate->id,
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $withdrawalCreate,
        ]);
    }

    public function getAll(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        //Config datetime
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $date2 = null;
        $limit = $request->limit ?: 20;
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => RESPONSE::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $listRequestWithdrawal = Withdrawal::where('user_id', $request->user->id)
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('withdrawals.status', $request->status);
            })
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('withdrawals.user_id', $request->user_id);
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney) {
                $query->where('amount_money', '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney) {
                $query->where('amount_money', '<=', $toMoney);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('withdrawals.created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('withdrawals.created_at', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('withdrawals', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->select('withdrawals.*')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listRequestWithdrawal
        ]);
    }

    public function getOne(Request $request)
    {
        $withdrawalRequestExist = Withdrawal::where([
            ['id', $request->withdrawal_id],
            ['user_id', $request->user->id]
        ])->first();

        if ($withdrawalRequestExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[0],
                'msg' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $withdrawalRequestExist
        ]);
    }

    public function update(Request $request)
    {
        $withdrawalRequestExist = Withdrawal::where('id', $request->withdrawal_id)->first();

        if ($withdrawalRequestExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[0],
                'msg' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[1]
            ]);
        }

        $accountBalance = DB::table('e_wallet_collaborators')
            ->where('user_id', $request->user->id)
            ->select('account_balance')
            ->first();

        if ($accountBalance == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NOT_E_WALLET_EXISTS[0],
                'msg' => MsgCode::NOT_E_WALLET_EXISTS[1]
            ]);
        }

        if ($request->status == StatusWithdrawalDefineCode::APPROVED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_REQUEST_WITHDRAWAL_STATUS[0],
                'msg' => MsgCode::INVALID_REQUEST_WITHDRAWAL_STATUS[1]
            ]);
        }
        if ($withdrawalRequestExist->status == StatusWithdrawalDefineCode::APPROVED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[0],
                'msg' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[1]
            ]);
        }

        if ($request->amount_money > $accountBalance->account_balance) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WITHDRAWAL_MONEY_CANNOT_GREATER_THAN_BALANCE[0],
                'msg' => MsgCode::WITHDRAWAL_MONEY_CANNOT_GREATER_THAN_BALANCE[1]
            ]);
        }

        if ($request->amount_money < 0) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_MONEY[0],
                'msg' => MsgCode::INVALID_MONEY[1]
            ]);
        }

        $withdrawalRequestExist->update([
            'amount_money' => $request->amount_money,
            'status' => $request->status
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $withdrawalRequestExist
        ]);
    }

    public function delete(Request $request)
    {
        $withdrawalRequestExist = Withdrawal::where([
            ['id', $request->withdrawal_id],
            ['user_id', $request->user->id]
        ])->first();

        if ($withdrawalRequestExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[0],
                'msg' => MsgCode::NO_WITHDRAWAL_ID_EXISTS[1]
            ]);
        }

        if ($withdrawalRequestExist->status == StatusWithdrawalDefineCode::APPROVED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[0],
                'msg' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[1]
            ]);
        }

        $withdrawalRequestExist->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
