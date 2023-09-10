<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Helper\StatusWithdrawalDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function index()
    {
        $total_golden_coin = User::query()
            ->sum('golden_coin');

        $total_silver_coin = User::query()
            ->sum('silver_coin');

        $total_deposit = WalletTransaction::query()
            ->sum('deposit_money');

        $total_withdraw = Withdrawal::query()
            ->where('status', StatusWithdrawalDefineCode::APPROVED)
            ->sum('amount_money');

        $data = [
            'total_golden_coin' => number_format($total_golden_coin, 2),
            'total_silver_coin' => number_format($total_silver_coin, 2),
            'total_deposit' => number_format($total_deposit, 2),
            'total_withdraw' => number_format($total_withdraw, 2),
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ]);
    }
}
