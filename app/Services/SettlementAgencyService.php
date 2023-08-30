<?php

namespace App\Services;

use App\Helper\TypeFCM;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Agency;
use App\Models\AgencyConfig;
use App\Models\PayAgency;
use App\Models\Store;
use App\Models\UserDeviceToken;

class SettlementAgencyService
{
    public static function settlement($store_id)
    {

        $store = Store::where('id', $store_id)->first();

        $agencys = Agency::where('store_id', $store_id)
            ->get();

        $configExists = AgencyConfig::where(
            'store_id',
            $store_id
        )->first();

        $length_settlement = 0;
        foreach ($agencys as $agency) {

            if ($agency == null) {
                continue;
            }


            if ($configExists  == null || $configExists->payment_limit == null) {
                continue;
            }

            if ($agency->balance < $configExists->payment_limit) {
                continue;
            }

            $payAfter = PayAgency::where('store_id', $store_id)
                ->where('agency_id',  $agency->id)->where('status', 0)->first();

            if ($payAfter  != null) {
                continue;
            }

            PushNotificationCustomerJob::dispatch(
                $store_id,
                $agency->customer_id,
                "Shop " . $store->name,
                "Đã quyết toán số dư CTV cho bạn",
                TypeFCM::NEW_PERIODIC_SETTLEMENT,
                null
            );

            $length_settlement++;

            PayAgency::create([
                "store_id" => $store_id,
                "agency_id"  =>  $agency->id,
                "money"  =>  $agency->balance,
                "status"  => 0,
                "from"  => 1,
            ]);
        }

        $deviceTokens = UserDeviceToken::where('user_id', $store->user_id)
            ->pluck('device_token')
            ->toArray();

        if ($length_settlement > 0) {
            PushNotificationUserJob::dispatch(
                $store->id,
                $store->user_id,
                'Shop ' . $store->name,
                'Đã lên danh sách quyết toán cho CTV ',
                TypeFCM::NEW_PERIODIC_SETTLEMENT,
                null,
                null
            );
        }
    }
}
