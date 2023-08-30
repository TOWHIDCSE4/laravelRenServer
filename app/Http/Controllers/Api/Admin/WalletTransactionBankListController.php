<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\WalletTransactionBankList;

class WalletTransactionBankListController extends Controller
{

    public function getAllBankList()
    {
        $bankList = WalletTransactionBankList::get();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $bankList,
        ], 200);
    }
}

