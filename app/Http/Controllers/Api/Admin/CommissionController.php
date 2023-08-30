<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\AccountRankDefineCode;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\TypeFCM;
use App\Helper\TypeMoneyFromEWalletDefineCode;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\CollaboratorReferMotel;
use App\Models\Contract;
use App\Models\EWalletCollaborator;
use App\Models\EWalletCollaboratorHistory;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommissionController extends Controller
{

    public function listCommissionCollaborator(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if ($dateFrom != null || $dateTo != null) {
            $dateFrom = Helper::createAndValidateFormatDate($dateFrom, 'Y-m-d');
            $dateTo = Helper::createAndValidateFormatDate($dateTo, 'Y-m-d');

            if ($dateFrom != false && $request->date_from != null) {
                $dateFrom = $dateFrom->format('Y-m-d') . ' 00:00:01';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }

            if ($dateTo != false && $request->date_to != null) {
                $dateTo = $dateTo->format('Y-m-d') . ' 23:59:59';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        }

        $all = CollaboratorReferMotel::when($request->status != null, function ($query) use ($request) {
            $query->where('collaborator_refer_motels.status', $request->status);
        })
            ->when($request->status_commission_collaborator != null, function ($query) use ($request) {
                $query->where('collaborator_refer_motels.status_commission_collaborator', $request->status_commission_collaborator);
            })
            ->when($sortBy != null && Schema::hasColumn('collaborator_refer_motels', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('collaborator_refer_motels.date_refer_success', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('collaborator_refer_motels.date_refer_success', '<=', $dateTo);
            })
            ->select('collaborator_refer_motels.*')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all,
        ]);
    }

    public function updateCommissionCollaborator(Request $request)
    {
        $collaboratorCommission = CollaboratorReferMotel::where('id', $request->commission_collaborator_id)->first();

        if ($collaboratorCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        $contract = Contract::where('id', $collaboratorCommission->contract_id)->first();

        if ($contract == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1]
            ]);
        }

        if ($collaboratorCommission->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[1]
            ]);
        }

        if ($collaboratorCommission->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::CANCEL) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[1]
            ]);
        }

        if (StatusCollaboratorReferMotelDefineCode::getStatusMotelCode($request->status_commission_collaborator) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        $collaboratorCommission->update([
            'status_commission_collaborator' => $request->status_commission_collaborator
        ]);

        if ($request->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::CANCEL) {
            NotificationUserJob::dispatch(
                $collaboratorCommission->user_id,
                'Thông báo hoa hồng',
                'Duyệt tiền hoa hồng đã được chấp nhận',
                TypeFCM::CONFIRMED_COMMISSION_COLLABORATOR_FOR_HOST,
                NotiUserDefineCode::USER_IS_HOST,
                $collaboratorCommission->id
            );
        }

        if ($request->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            if ($contract != null) {
                $eWalletHistoryCollaborator = null;
                $userRenterRepresent = DB::table('users') // đây là thằng đại diện hợp đồng, cũng là thằng sử dụng mã giới thiệu
                    ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.id', $contract->id],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['user_contracts.is_represent', true]
                    ])
                    ->select('users.*')
                    ->first();


                if ($userRenterRepresent == null) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                        'data' => $collaboratorCommission
                    ]);
                }

                $checkExistPreviousCollaborator = CollaboratorReferMotel::where([
                    ['id', '<>', $request->commission_collaborator_id],
                    ['user_referral_id', $userRenterRepresent->id],
                    ['motel_id', $contract->motel_id]
                ])->count();

                if ($checkExistPreviousCollaborator > 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                        'data' => $collaboratorCommission
                    ]);
                }

                $userReferralCodeExist = DB::table('users') // đây là thằng chia sẻ mã giới thiệu
                    ->whereNotNull('self_referral_code')
                    ->where('self_referral_code', $userRenterRepresent->referral_code)
                    ->first();

                if ($userReferralCodeExist == null || $userReferralCodeExist->account_rank != AccountRankDefineCode::LOYAL) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                        'data' => $collaboratorCommission
                    ]);
                }

                $checkContractPreviousMonth = DB::table('collaborator_refer_motels')
                    ->where([
                        ['id', '<>', $request->commission_collaborator_id],
                        ['user_referral_id', $userRenterRepresent->id]
                    ])
                    ->when($userReferralCodeExist != null, function ($query) use ($userReferralCodeExist) {
                        $query->where('user_id', $userReferralCodeExist->id);
                    })
                    ->first();

                $moPostExist = DB::table('mo_posts')
                    ->where('motel_id', $contract->motel_id)
                    ->first();

                if ($moPostExist == null || $moPostExist->status != StatusMoPostDefineCode::COMPLETED && $moPostExist->status != StatusMoPostDefineCode::CANCEL) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                        'data' => $collaboratorCommission
                    ]);
                }

                if ($moPostExist != null) {
                    if ($checkContractPreviousMonth == null && $userReferralCodeExist != null && $userRenterRepresent->referral_code != null  && $moPostExist->money_commission_user != 0 && $moPostExist->money_commission_user != null) {
                        $eWalletCollaborator = EWalletCollaborator::where('user_id', $userRenterRepresent->id)->first();
                        $eWalletReferralCode = EWalletCollaborator::where('user_id', $userReferralCodeExist->id)->first();

                        if ($eWalletReferralCode == null) {
                            $eWalletReferralCode = EWalletCollaborator::create([
                                'user_id' => $userReferralCodeExist->id,
                                'account_balance' => 0,
                            ]);
                        }
                        if ($eWalletCollaborator == null) {
                            $eWalletCollaborator = EWalletCollaborator::create([
                                'user_id' => $userRenterRepresent->id,
                                'account_balance' => 0,
                            ]);
                        }

                        EWalletCollaboratorHistory::create([
                            'e_wallet_collaborator_id' => $eWalletReferralCode->id,
                            'money_change' => $moPostExist->money_commission_user,
                            'account_balance_changed' => $eWalletReferralCode->account_balance + $moPostExist->money_commission_user,
                            'balance_origin' => $eWalletReferralCode->account_balance,
                            'type_money_from' => TypeMoneyFromEWalletDefineCode::USER_REFERRED,
                            'value_reference' => $eWalletCollaborator->id,
                            'title' => 'Tiền hoa hồng',
                            'description' => 'Tiền hoa hồng +' . Helper::currency_money_format($moPostExist->money_commission_user) .
                                ' giới thiệu được người thuê phòng',
                            'take_out_money' => false
                        ]);
                        $eWalletReferralCode->update([
                            'account_balance' => $eWalletReferralCode->account_balance + $moPostExist->money_commission_user
                        ]);

                        EWalletCollaboratorHistory::create([
                            'e_wallet_collaborator_id' => $eWalletCollaborator->id,
                            'money_change' => $moPostExist->money_commission_user,
                            'account_balance_changed' => $eWalletCollaborator->account_balance + $moPostExist->money_commission_user,
                            'balance_origin' => $eWalletCollaborator->account_balance,
                            'type_money_from' => TypeMoneyFromEWalletDefineCode::USER_REFERRAL,
                            'value_reference' => $eWalletReferralCode->id,
                            'title' => 'Tiền hoa hồng',
                            'description' => 'Tiền hoa hồng +' . Helper::currency_money_format($moPostExist->money_commission_user) .
                                ' được giới thiệu thuê phòng',
                            'take_out_money' => false
                        ]);
                        $eWalletCollaborator->update([
                            'account_balance' => $eWalletCollaborator->account_balance + $moPostExist->money_commission_user
                        ]);

                        NotificationUserJob::dispatch(
                            $userReferralCodeExist->id,
                            'Biến động số dư ví CTV',
                            'Tiền hoa hồng + ' . Helper::currency_money_format($moPostExist->money_commission_user),
                            TypeFCM::BALANCE_CHANGE,
                            NotiUserDefineCode::USER_NORMAL,
                            $eWalletHistoryCollaborator != null ? $eWalletHistoryCollaborator->id : null
                        );
                        NotificationUserJob::dispatch(
                            $userRenterRepresent->id,
                            'Biến động số dư ví CTV',
                            'Tiền hoa hồng + ' . Helper::currency_money_format($moPostExist->money_commission_user),
                            TypeFCM::BALANCE_CHANGE,
                            NotiUserDefineCode::USER_NORMAL,
                            $eWalletHistoryCollaborator != null ? $eWalletHistoryCollaborator->id : null
                        );
                    }
                }

                // NotificationUserJob::dispatch(
                //     $contract->user_id,
                //     'Thông báo hoa hồng',
                //     'Thanh toán hoa hồng đã được xác nhận',
                //     TypeFCM::CONFIRMED_COMMISSION_COLLABORATOR_FOR_HOST,
                //     NotiUserDefineCode::USER_IS_HOST,
                //     $collaboratorCommission->id
                // );
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $collaboratorCommission
        ]);
    }

    public function updatePaidCommissionAdmin(Request $request)
    {
        $collaboratorCommission = CollaboratorReferMotel::where('id', $request->commission_collaborator_id)->first();
        if ($collaboratorCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        $contract = Contract::where('id', $collaboratorCommission->contract_id)->first();

        if ($contract == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1]
            ]);
        }

        if ($collaboratorCommission->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[1]
            ]);
        }

        if ($collaboratorCommission->status == StatusCollaboratorReferMotelDefineCode::CANCEL) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[1]
            ]);
        }

        if (StatusCollaboratorReferMotelDefineCode::getStatusMotelCode($request->status)  == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        $collaboratorCommission->update([
            'status' => $request->status
        ]);

        if ($request->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            NotificationUserJob::dispatch(
                $contract->user_id,
                'Thông báo hoa hồng',
                'Thanh toán hoa hồng đã được xác nhận',
                TypeFCM::CONFIRMED_COMMISSION_COLLABORATOR_FOR_HOST,
                NotiUserDefineCode::USER_IS_HOST,
                $collaboratorCommission->id
            );
        }
        if ($request->status == StatusCollaboratorReferMotelDefineCode::CANCEL) {
            NotificationUserJob::dispatch(
                $contract->user_id,
                'Thông báo hoa hồng',
                'Hoa hồng đã bị hủy',
                TypeFCM::CONFIRMED_COMMISSION_COLLABORATOR_FOR_HOST,
                NotiUserDefineCode::USER_IS_HOST,
                $collaboratorCommission->id
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $collaboratorCommission
        ]);
    }

    public function getOneCommissionCollaborator(Request $request)
    {
        $collaboratorCommission = CollaboratorReferMotel::where('id', $request->commission_collaborator_id)->first();

        if ($collaboratorCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $collaboratorCommission
        ]);
    }
}
