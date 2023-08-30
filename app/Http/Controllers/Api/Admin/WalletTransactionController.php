<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ParamUtils;
use App\Helper\RenterType;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    
    //get All Wallet Deposit
    public function  getAllWalletDeposit()
    {
        $deposits = WalletTransaction::select(
            'user_id',
            'deposit_money', 
            'account_number', 
            'bank_account_holder_name', 
            'bank_name',
            'deposit_trading_code', 
            'deposit_date_time', 
            'deposit_content')->get();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $deposits,
        ], 200);
    }
    //edit Wallet Deposit
    public function  editWalletDeposit($wallet_transaction_id, Request $request)
    {

        if ($request->deposit_money == null || empty($request->deposit_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[1],
            ]);
        }


        $wallet_transaction = WalletTransaction::where(['id' =>  $wallet_transaction_id, 'type' => WalletTransaction::DEPOSIT])->first();

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
            $response = $wallet_transaction->update([
                "account_number" => $request->account_number ?? $wallet_transaction->account_number,
                "bank_account_holder_name" => $request->bank_account_holder_name ?? $wallet_transaction->bank_account_holder_name,
                "bank_name" => $request->bank_name ?? $wallet_transaction->bank_name,
                "deposit_money" => $request->deposit_money ?? $wallet_transaction->deposit_money,
                "deposit_trading_code" => $request->deposit_trading_code ?? $wallet_transaction->deposit_trading_code,
                "deposit_content" => $request->deposit_content ?? $wallet_transaction->deposit_content,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $wallet_transaction,
        ]);
    }
    //edit Wallet Withdrow
    public function  editWalletWithdrow($wallet_transaction_id, Request $request)
    {
        if ($request->withdraw_money == null || empty($request->withdraw_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[1],
            ]);
        }

        $wallet_transaction = WalletTransaction::where(['id' =>  $wallet_transaction_id, 'type' => WalletTransaction::WITHDRAW])->first();

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
            $response = $wallet_transaction->update([
                "account_number" => $request->account_number ?? $wallet_transaction->account_number,
                "bank_account_holder_name" => $request->bank_account_holder_name ?? $wallet_transaction->bank_account_holder_name,
                "bank_name" => $request->bank_name ?? $wallet_transaction->bank_name,
                "withdraw_money" => $request->withdraw_money ?? $wallet_transaction->withdraw_money,
                "withdraw_trading_code" => $request->withdraw_trading_code ?? $wallet_transaction->withdraw_trading_code,
                "withdraw_content" => $request->withdraw_content ?? $wallet_transaction->withdraw_content,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $wallet_transaction,
        ]);
    }



    //get All Wallet Withdraws
    public function  getAllWalletWithdraws()
    {
        $deposits = WalletTransaction::select(
            'user_id',
            'withdraw_money', 
            'account_number', 
            'bank_account_holder_name', 
            'bank_name',
            
            'withdraw_trading_code', 
            'withdraw_date_time', 
            'withdraw_content')->get();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $deposits,
        ], 200);
    }
   //create Wallet Deposit
    public function createWalletDeposit(Request $request)
    {
        if ($request->deposit_money == null || empty($request->deposit_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[1],
            ]);
        }

        DB::beginTransaction();
        try {
            $wallet_transaction_created = WalletTransaction::create([
                "user_id" => $request->user->id,
                "account_number" => $request->account_number,
                "bank_account_holder_name" => $request->bank_account_holder_name,
                "bank_name" => $request->bank_name,
                "deposit_money" => $request->deposit_money,
                "deposit_trading_code" => Helper::generateTransactionID(),
                "deposit_date_time" => Helper::getTimeNowString(),
                "deposit_content" => $request->deposit_content ?? null,
                "type" => WalletTransaction::DEPOSIT,
            ]);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $wallet_transaction_created,
        ]);
    }


    //create Wallet Withdraws
    public function createWalletWithdraws(Request $request)
    {
        if ($request->withdraw_money == null || empty($request->withdraw_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[1],
            ]);
        }

        DB::beginTransaction();
        try {
            $wallet_transaction_created = WalletTransaction::create([
                "user_id" => $request->user->id,
                "account_number" => $request->account_number,
                "bank_account_holder_name" => $request->bank_account_holder_name,
                "bank_name" => $request->bank_name,

                "withdraw_money" => $request->withdraw_money,
                "withdraw_trading_code" => Helper::generateTransactionID(),
                "withdraw_date_time" => Helper::getTimeNowString(),
                "withdraw_content" => $request->withdraw_content ?? null,
                "type" => WalletTransaction::WITHDRAW,
            ]);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $wallet_transaction_created,
        ]);
    }

}