<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ParamUtils;
use App\Helper\RenterType;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentMethod\lib\HMACSignature;
use App\Http\Controllers\PaymentMethod\lib\MessageBuilder;
use App\Http\Controllers\PaymentMethod\NinePayController;
use App\Models\MsgCode;
use App\Models\VirtualAccount;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{

    //get All Wallet Deposit
    public function getAllWalletDeposit()
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
    public function editWalletDeposit($wallet_transaction_id, Request $request)
    {

        if ($request->deposit_money == null || empty($request->deposit_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[1],
            ]);
        }


        $wallet_transaction = WalletTransaction::where(['id' => $wallet_transaction_id, 'type' => WalletTransaction::DEPOSIT])->first();

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
    public function editWalletWithdrow($wallet_transaction_id, Request $request)
    {
        if ($request->withdraw_money == null || empty($request->withdraw_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::WITHDRAW_MONEY_IS_REQUIRED[1],
            ]);
        }

        $wallet_transaction = WalletTransaction::where(['id' => $wallet_transaction_id, 'type' => WalletTransaction::WITHDRAW])->first();

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
    public function getAllWalletWithdraws()
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
        $deposit_money = $request->deposit_money;

        if ($deposit_money == null || empty($deposit_money)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[0],
                'msg' => MsgCode::DEPOSIT_MONEY_IS_REQUIRED[1],
            ]);
        }

        DB::beginTransaction();
        try {
            $user_id = $request->user->id;
            $virtual_account = VirtualAccount::query()
                ->where('user_id', $user_id)
                ->first();

            $ninePayController = new NinePayController();

            if (!$virtual_account) {
                $ninePayController = new NinePayController();

                $time = time();
                $virtual_account_param = [
                    "request_id" => uniqid(),
                    "uid" => $user_id,
                    "uname" => $ninePayController::UNAME,
                    "bank_code" => $request->bank_code,
                    "request_amount" => $deposit_money,
                ];

                $message = MessageBuilder::instance()
                    ->with($time, $ninePayController::END_POINT . '/va/create', 'POST')
                    ->withParams($virtual_account_param)
                    ->build();

                $hmacs = new HMACSignature();
                $signature = $hmacs->sign($message, $ninePayController::MERCHANT_SECRET_KEY);

                $headers = array(
                    'Date: ' . $time,
                    'Authorization: Signature Algorithm=HS256,Credential=' . $ninePayController::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
                );

                $response = $ninePayController->callAPI('POST', $ninePayController::END_POINT . '/va/create', $virtual_account_param, $headers);

                $response_data = json_decode($response);

                if (isset($response_data->status) && ($response_data->status == 5)) {
                    $virtual_account = VirtualAccount::query()
                        ->where('user_id', $user_id)
                        ->create(
                            array_merge(Arr::only($virtual_account_param, [
                                'request_id',
                                'bank_code',
                                'request_amount',
                            ]),
                                [
                                    'user_id' => $user_id,
                                    'bank_account_name' => $response_data->data->bank_account_name,
                                    'bank_account_no' => $response_data->data->bank_account_no,
                                    'qr_code_url' => $response_data->data->qr_code_url,
                                ]
                            ));
                }

                if(isset($response_data->error_code) && $response_data->error_code == '001'){
                    return ResponseUtils::json([
                        'code' => Response::HTTP_CONFLICT,
                        'success' => false,
                        'msg_code' => MsgCode::ALREADY_VIRTUAL_ACCOUNT_EXISTS[0],
                        'msg' => MsgCode::ALREADY_VIRTUAL_ACCOUNT_EXISTS[1]
                    ]);
                }
            } else {

                $time = time();
                $total_amount =  $deposit_money + $virtual_account->request_amount;
                $virtual_account_param = [
                    "request_id" => $virtual_account->request_id,
                    "uid" => $user_id,
                    "uname" => $ninePayController::UNAME,
                    "bank_code" => $request->bank_code,
                    "request_amount" => $total_amount,
                ];

                $message = MessageBuilder::instance()
                    ->with($time, $ninePayController::END_POINT . '/va/update', 'POST')
                    ->withParams($virtual_account_param)
                    ->build();

                $hmacs = new HMACSignature();
                $signature = $hmacs->sign($message, NinePayController::MERCHANT_SECRET_KEY);

                $headers = array(
                    'Date: ' . $time,
                    'Authorization: Signature Algorithm=HS256,Credential=' . NinePayController::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
                );

                $response = $ninePayController->callAPI('POST', NinePayController::END_POINT . '/va/update', $virtual_account_param, $headers);
                $response_data = json_decode($response);


                if(isset($response_data->status) && $response_data->status == 5){
                    $virtual_account->bank_account_no = $response_data->data->bank_account_no;
                    $virtual_account->request_amount = $total_amount;
                    $virtual_account->save();
                }
            }


            if(!$virtual_account){
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[0],
                    'msg' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[1]
                ]);
            }

            $wallet_transaction_created = WalletTransaction::create([
                "user_id" => $user_id,
                "account_number" => $virtual_account->bank_account_no,
                "bank_account_holder_name" => $virtual_account->bank_account_name,
                "bank_name" => $virtual_account->bank_code,
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
