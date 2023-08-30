<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\DatetimeUtils;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusWithdrawalDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\CollaboratorReferMotel;
use App\Models\EWalletCollaborator;
use App\Models\EWalletCollaboratorHistory;
use App\Models\MsgCode;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class EWalletCollaboratorController extends Controller
{
    //

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
            ->whereNotNull('e_wallet_collaborators.account_balance')
            ->where('e_wallet_collaborators.account_balance', '<>', 0)
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('e_wallet_collaborators.user_id', $request->user_id);
            })
            ->when($request->user->is_admin == true, function ($query) use ($request) {
                $query->where('e_wallet_collaborators.user_id', '<>', $request->user->id);
            })
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

    public function getHistoryReceiveMoneyAdmin(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        //Config datetime
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $date2 = null;
        $limit = $request->limit ?: 20;
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

        $historyAdminReceiveMoney = CollaboratorReferMotel::when($request->status != null, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
            ->whereNotNull('money_commission_admin')
            ->whereNotNull('money_commission_user')
            ->where('money_commission_admin', '<>', 0)
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && CollaboratorReferMotel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => RESPONSE::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $historyAdminReceiveMoney
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

        $listRequestWithdrawal = Withdrawal::when($request->status != null, function ($query) use ($request) {
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
        $withdrawalRequestExist = Withdrawal::where('id', $request->withdrawal_id)->first();

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

        if ($request->status != null) {

            if (StatusWithdrawalDefineCode::getStatusWithdrawalCode($request->status) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_WITHDRAWAL_STATUS_EXISTS[0],
                    'msg' => MsgCode::NO_WITHDRAWAL_STATUS_EXISTS[1]
                ]);
            }
        }

        if ($withdrawalRequestExist->status == StatusWithdrawalDefineCode::APPROVED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[0],
                'msg' => MsgCode::REQUEST_WITHDRAWAL_HAS_APPROVED[1]
            ]);
        }

        $withdrawalRequestExist->update([
            'status' => $request->status,
            'note' => $request->note
        ]);

        if ($request->status == StatusWithdrawalDefineCode::APPROVED) {
            $withdrawalRequestExist->update([
                'date_withdrawal_approved' => Helper::getTimeNowDateTime()->format('Y-m-d H:i:s'),
                'admin_id' => $request->user->id
            ]);

            // PushNotificationUserJob::dispatch(
            //     $withdrawalRequestExist->user_id,
            //     "Yêu cầu rút tiền mới được chấp nhận",
            //     'Yêu cầu rút tiền mới được chấp nhận',
            //     TypeFCM::APPROVED_REQUEST_WITHDRAWAL,
            //     NotiUserDefineCode::USER_NORMAL,
            //     $withdrawalRequestExist->id,
            // );

            $eWalletCollaborator = EWalletCollaborator::where('user_id', $withdrawalRequestExist->user_id)->first();

            if ($eWalletCollaborator != null) {
                EWalletCollaboratorHistory::create([
                    'e_wallet_collaborator_id' => $eWalletCollaborator->id,
                    'balance_origin' => $eWalletCollaborator->account_balance,
                    'money_change' => $withdrawalRequestExist->amount_money,
                    'account_balance_changed' => $eWalletCollaborator->account_balance - $withdrawalRequestExist->amount_money,
                    'title' => 'Rút tiền khỏi ví',
                    'description' => 'Ví CTV -' .
                        Helper::currency_money_format($withdrawalRequestExist->amount_money) . ', số dư còn lại ' .
                        Helper::currency_money_format($eWalletCollaborator->account_balance - $withdrawalRequestExist->amount_money),
                    'take_out_money' => true
                ]);

                $eWalletCollaborator->update([
                    'account_balance' => $eWalletCollaborator->account_balance - $withdrawalRequestExist->amount_money
                ]);

                NotificationUserJob::dispatch(
                    $withdrawalRequestExist->user_id,
                    "Yêu cầu rút tiền được chấp nhận",
                    'Biến động số dư ví CTV -' . $withdrawalRequestExist->amount_money,
                    TypeFCM::APPROVED_REQUEST_WITHDRAWAL,
                    NotiUserDefineCode::USER_NORMAL,
                    $withdrawalRequestExist->id,
                );
            }
        } else if ($request->status == StatusWithdrawalDefineCode::CANCEL) {
            $withdrawalRequestExist->update([
                'admin_id' => $request->user->id
            ]);

            NotificationUserJob::dispatch(
                $withdrawalRequestExist->user_id,
                "Yêu cầu rút tiền mới bị hủy",
                'Vui lòng thử lại sau',
                TypeFCM::UNAPPROVED_REQUEST_WITHDRAWAL,
                NotiUserDefineCode::USER_NORMAL,
                $withdrawalRequestExist->id,
            );
        }

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
        $withdrawalRequestExist = Withdrawal::where('id', $request->withdrawal_id)->first();

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

        NotificationUserJob::dispatch(
            $withdrawalRequestExist->user_id,
            "Yêu cầu rút tiền đã bị hủy",
            'Vui lòng thử lại sau',
            TypeFCM::UNAPPROVED_REQUEST_WITHDRAWAL,
            NotiUserDefineCode::USER_NORMAL,
            $withdrawalRequestExist->id,
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
