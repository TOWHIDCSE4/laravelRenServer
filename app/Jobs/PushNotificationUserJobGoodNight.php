<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Helper\InventoryUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Models\Branch;
use App\Models\ElementDistribute;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\SubElementDistribute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PushNotificationUserJobGoodNight implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $now = Helper::getTimeNowDateTime();
        $arr_store = DB::table('orders')
            ->where('created_at', '>=',  $now->format('Y-m-d 00:00:00'))
            ->where('created_at', '<', $now->format('Y-m-d 23:59:59'))
            ->groupBy('store_id')
            ->having(DB::raw('count(id)'), '>', 0)
            ->pluck('store_id')->toArray();


        foreach ($arr_store as  $store_id) {
            $store = Store::where('id', $store_id)->first();

            PushNotificationUserJob::dispatch(
                $store->id,
                $store->user_id,
                'Shop ' . $store->name,
                "Một ngày nữa lại kết thúc. Chúc bạn có giấc ngủ ngon và mơ những giấc mơ đẹp để ngày mai cho một ngày làm việc thật hiệu quả.!",
                TypeFCM::GOOD_NIGHT_USER,
                null,
                null
            );
        }
    }
}
