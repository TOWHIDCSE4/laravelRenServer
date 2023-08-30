<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;

class RankHelper
{
    // kiểm tra các thông số và set ranking
    static public function checkRanked($user_id)
    {
        $ranked = 0;
        $totalMotel = 0;
        $totalSum = DB::table('bills')
            ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.user_id', $user_id],
                ['bills.status', StatusBillDefineCode::COMPLETED]
            ])
            ->sum('bills.total_final');

        $motel = DB::table('motels')->where('user_id', $user_id);
        if ($motel->exists()) {
            $totalMotel = $motel->count();
        }

        if ($totalSum >= 40000000 || $totalMotel >= 30) {
            $ranked = RankUserDefineCode::DIAMOND;
        } else if ($totalSum >= 20000000 || $totalMotel >= 20) {
            $ranked = RankUserDefineCode::GOLD;
        } else if ($totalSum >= 10000000 || $totalMotel >= 10) {
            $ranked = RankUserDefineCode::SILVER;
        } else if ($totalSum >= 5000000 || $totalMotel >= 5) {
            $ranked = RankUserDefineCode::BRONZE;
        } else {
            $ranked = RankUserDefineCode::NORMAL;
        }

        DB::table('users')->where('id', $user_id)->update([
            'ranked' => $ranked
        ]);
    }
}
