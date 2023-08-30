<?php

namespace App\Services;

use App\Helper\TypeFCM;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Collaborator;
use App\Models\CollaboratorsConfig;
use App\Models\PayCollaborator;
use App\Models\Store;
use App\Models\UserDeviceToken;

class SettlementCollaboratorService
{
    public static function settlement($store_id)
    {

        $store = Store::where('id', $store_id)->first();

        $collaborators = Collaborator::where('store_id', $store_id)
            ->get();

        $configExists = CollaboratorsConfig::where(
            'store_id',
            $store_id
        )->first();

        $length_settlement = 0;
        foreach ($collaborators as $collaborator) {

            if ($collaborator == null) {
                continue;
            }


            if ($configExists  == null || $configExists->payment_limit == null) {
                continue;
            }

            if ($collaborator->balance < $configExists->payment_limit) {
                continue;
            }

            $payAfter = PayCollaborator::where('store_id', $store_id)
                ->where('collaborator_id',  $collaborator->id)->where('status', 0)->first();

            if ($payAfter  != null) {
                continue;
            }

            PushNotificationCustomerJob::dispatch(
                $store_id,
                $collaborator->customer_id,
                "Shop " . $store->name,
                "Đã quyết toán số dư CTV cho bạn",
                TypeFCM::NEW_PERIODIC_SETTLEMENT,
                null
            );

            $length_settlement++;

            PayCollaborator::create([
                "store_id" => $store_id,
                "collaborator_id"  =>  $collaborator->id,
                "money"  =>  $collaborator->balance,
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
                null,
            );
        }
    }
}
