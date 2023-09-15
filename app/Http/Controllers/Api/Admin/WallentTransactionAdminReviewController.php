<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusWithdrawalDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\User;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class WallentTransactionAdminReviewController extends Controller
{
    //confirm Payment Status review by Admin
    public function  confirmPaymentStatusAdmin(Request $request)
    {
        if ($request->wallet_transaction_id == null || empty($request->wallet_transaction_id)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WALLET_TRANSACTION_ID_IS_REQUIRED[0],
                'msg' => MsgCode::WALLET_TRANSACTION_ID_IS_REQUIRED[1],
            ]);
        }

        $wallet_transaction = WalletTransaction::where('id', $request->wallet_transaction_id)->first();

        if ($wallet_transaction == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_TRANSACTION_EXISTS[0],
                'msg' => MsgCode::NO_TRANSACTION_EXISTS[1],
            ]);
        }

        DB::beginTransaction();

        try {
            if($request->status == 1){
                $user = DB::table('users')
                    ->where('id', $request->user->id)
                    ->first();

                $wallet_transaction_data = $wallet_transaction->update([
                    "status" => StatusWithdrawalDefineCode::CANCEL,
                ]);

                $remaining_golden_coin = $user->golden_coin + $wallet_transaction_data->withdraw_money;

                User::query()
                    ->where('id', $request->user->id)
                    ->update([
                        'golden_coin'=> $remaining_golden_coin,
                    ]);
            }else{
                $wallet_transaction_data = $wallet_transaction->update([
                    "status" => StatusWithdrawalDefineCode::APPROVED,
                ]);
            }

            DB::commit();

            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $wallet_transaction,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

    }
     // get Wallet Data For Graph
    public function getWalletDataForGraph(Request $request)
    {
        $year = $request->year ?? date('Y');
        $type = $request->type ?? 'deposit';

        if ($year != null ) {
            $dateFrom  = $year .'-01-01 00:00:00';
            $dateTo    = $year .'-12-31 23:59:59';
        }
        $monthWiseTotal = [];
        $wallet_transaction_data = [];
        if($type == 'deposit'){
            $wallet_transaction_data = WalletTransaction::when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('wallet_transactions.deposit_date_time', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('wallet_transactions.deposit_date_time', '<=', $dateTo);
                })->where('type', WalletTransaction::DEPOSIT)
                ->select('wallet_transactions.deposit_money','wallet_transactions.deposit_date_time','wallet_transactions.type','wallet_transactions.status')
                ->get();

            $monthWiseTotal = $this->monthWiseToTal($wallet_transaction_data,['deposit_date_time','deposit_money']);
        }

        if($type == 'withdraw'){
            $wallet_transaction_data = WalletTransaction::when($dateFrom != null, function ($query) use ($dateFrom) {
                    $query->where('wallet_transactions.withdraw_date_time', '>=', $dateFrom);
                })
                ->when($dateTo != null, function ($query) use ($dateTo) {
                    $query->where('wallet_transactions.withdraw_date_time', '<=', $dateTo);
                })->where('type', WalletTransaction::WITHDRAW)
                ->select('wallet_transactions.withdraw_money','wallet_transactions.withdraw_date_time','wallet_transactions.type','wallet_transactions.status')
                ->get();

            $monthWiseTotal = $this->monthWiseToTal($wallet_transaction_data,['withdraw_date_time','withdraw_money']);

        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $monthWiseTotal
        ]);
    }

    protected function monthWiseToTal($wallet_transaction_data, $property = []){
        $monthWiseTotal = [];
        foreach ($wallet_transaction_data as $entry) {
            $month = date('m', strtotime($entry[$property[0]]));
            $monthNumber = intval($month);
            $withdrawMoney = $entry[$property[1]];
            if (array_key_exists($monthNumber, $monthWiseTotal)) {
                $monthWiseTotal[$monthNumber] += $withdrawMoney;
            } else {
                $monthWiseTotal[$monthNumber] = $withdrawMoney;
            }
        }

        return $monthWiseTotal;

    }

}
