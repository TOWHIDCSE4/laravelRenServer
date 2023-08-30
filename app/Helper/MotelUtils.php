<?php

namespace App\Helper;

use App\Models\Contract;
use App\Models\MoPost;
use App\Models\Motel;
use App\Models\Tower;
use Illuminate\Support\Facades\DB;

class MotelUtils
{
    static public function getBadgesMotels($user_id)
    {
        $total_motel_renter = Contract::join('users', 'contracts.user_id', '=', 'users.id')
            ->where([
                ['users.id', $user_id],
                ['users.is_host', 1],
                ['contracts.status', 2]
            ])
            ->select('contracts.*')
            ->count('contracts.id');



        $totalMotelAvailable = Motel::join('users', 'motels.user_id', '=', 'users.id')
            ->where([
                ['motels.user_id', $user_id],
                ['users.is_host', true],
                ['motels.status', 0]
            ])
            ->select('motels.*')
            ->count('motels.id');

        return ['summary' => ['total_motel_renter' => $total_motel_renter, 'total_motel_available' => $totalMotelAvailable]];
    }

    static function handleMinMaxTower($towerId)
    {
        $tower = Tower::where('id', $towerId)->first();

        if ($tower != null) {
            $motels = DB::table('motels')->where('tower_id', $tower->id);
            $minMoney = (clone $motels)->whereNotNull('money')->orderBy('money', 'asc')->value('money');
            $maxMoney = (clone $motels)->whereNotNull('money')->orderBy('money', 'desc')->value('money');
            $tower->update([
                'min_money' => $minMoney,
                'max_money' => $maxMoney,
            ]);
        }
    }

    static function handleMinMaxMoPost($towerId)
    {
        $moPost = MoPost::where('tower_id', $towerId)->whereNotNull('tower_id')->first();
        if ($moPost != null) {
            $motels = DB::table('motels')->where('tower_id', $moPost->tower_id);
            $minMoney = (clone $motels)->whereNotNull('money')->orderBy('money', 'asc')->value('money');
            $maxMoney = (clone $motels)->whereNotNull('money')->orderBy('money', 'desc')->value('money');
            $moPost->update([
                'money' => $minMoney,
                'min_money' => $minMoney,
                'max_money' => $maxMoney,
            ]);
        }
    }
}
