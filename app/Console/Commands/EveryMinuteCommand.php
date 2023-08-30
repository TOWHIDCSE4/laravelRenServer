<?php

namespace App\Console\Commands;

use App\Helper\DatetimeUtils;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\PushNotificationJob;
use App\Jobs\NotificationUserJob;
use App\Models\CollaboratorsConfig;
use App\Models\Contract;
use App\Models\User;
use App\Models\TaskNoti;
use App\Models\UserDeviceToken;
use App\Services\SettlementCollaboratorService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EveryMinuteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:every_minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo tới User';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // $this->handleSettlement();
        // $this->handleContract();
        // $this->handleNotiToUser();
        // $this->handleEndDay();
        $this->info('Success');
    }


    public function handleNotiToUser()
    {
        $date = Helper::getTimeNowDateTime();
        $dateC =  Carbon::parse(Helper::getTimeNowString());
        $time1 = $date->format('H:i:00');
        $time2 = $date->format('H:i:59');

        $dayNow = (int)$date->format('d');
        $monthNow =  (int)$date->format('m');
        $dayOfWeek =    (int)$dateC->dayOfWeek;

        //Xử lý 1 lần
        $timeOnce1 = $date->format('Y-m-d H:i:00');
        $timeOnce2 = $date->format('Y-m-d H:i:59');

        $listCanOnce = TaskNoti::where('status', 0)
            ->where('type_schedule', 0)
            ->whereBetween('time_run', [$timeOnce1,  $timeOnce2])
            ->get();


        foreach ($listCanOnce  as $itemTask) {
            if ($itemTask->type_schedule === 0) {

                NotificationUserJob::dispatch(
                    null,
                    $itemTask->title,
                    $itemTask->description,
                    TypeFCM::SEND_ALL,
                    $itemTask->role,
                    $itemTask->reference_value,
                );


                $task = TaskNoti::where(
                    'id',
                    $itemTask->id
                )->first();

                $task->update([
                    'status' => 2,
                    'time_run_near' => $dateC
                ]);
            }
        }


        //Xử lý noti lịch trình lặp lại
        $listCanHandle = TaskNoti::where('status', 0)
            ->where('type_schedule', '<>', 0)
            ->whereTime('time_of_day', '>=', $time1)
            ->whereTime('time_of_day', '<', $time2)
            ->get();

        foreach ($listCanHandle as $itemTask) {

            $allowSend = false;
            if ($itemTask->type_schedule === 1) {
                $allowSend = true;
            }

            if ($itemTask->type_schedule === 2) {
                if ($itemTask->day_of_week ==  $dayOfWeek) {
                    $allowSend = true;
                }
            }

            if ($itemTask->type_schedule === 3) {
                if ($itemTask->day_of_month ==   $dayNow) {
                    $allowSend = true;
                }
            }

            if ($allowSend === true) {



                // if ($itemTask->group_User == 1) {
                //     $listUser = User::where(
                //         'store_id',
                //         $itemTask->store_id
                //     );

                //     $dayBirth1 = $date->format('Y-m-d 00:00:00');
                //     $dayBirth2 = $date->format('Y-m-d 23:59:59');

                //     $listUser =  $listUser
                //         ->where('day_of_birth', '>=',  $dayBirth1)
                //         ->where('day_of_birth', '<',   $dayBirth2);

                //     $listUser =  $listUser->get();

                //     foreach ($listUser as $User) {
                //         NotificationUserJob::dispatch(
                //             $itemTask->store_id,
                //             $User->id,
                //             $itemTask->content,
                //             $itemTask->title,
                //             TypeFCM::SEND_ALL,
                //             null,
                //             $itemTask->type_action,
                //             $itemTask->value_action,
                //         );
                //         NotificationUserJob::dispatch(
                //             null,
                //             $request->title,
                //             $request->content,
                //             $request->type_notification,
                //             $request->role,
                //             $request->reference_value
                //         );
                //     }
                // } else {
                NotificationUserJob::dispatch(
                    null,
                    $itemTask->title,
                    $itemTask->description,
                    TypeFCM::SEND_ALL,
                    $itemTask->role,
                    $itemTask->reference_value,
                );
                // }



                $task = TaskNoti::where(
                    'id',
                    $itemTask->id
                )->first();

                $task->update([
                    'time_run_near' => $dateC
                ]);
            }
        }
    }

    public function handleSettlement()
    {
        // $now = Helper::getTimeNowDateTime();
        // $timeNow = $now->format('H:i');

        // $isDay1 = (int)$now->format('d') == 1;
        // $isDay16 = (int)$now->format('d') == 16;

        // if ($isDay1 || $isDay16) {
        //     if ($timeNow == "00:00") {
        //         $callaboratorExists = CollaboratorsConfig::get();
        //         foreach ($callaboratorExists as $callaboratorConfig) {
        //             if (($callaboratorConfig->payment_1_of_month === true &&  $isDay1) || ($callaboratorConfig->payment_16_of_month === true &&  $isDay16)) {
        //                 SettlementCollaboratorService::settlement($callaboratorConfig->store_id);
        //             }
        //         }
        //     }
        // }
    }


    public function handleEndDay()
    {

        $dateC =  Carbon::parse(Helper::getTimeNowString());

        if ($dateC->hour == 19 && $dateC->minute == 45) {
            // NotificationUserJobEndDay::dispatch();
        }

        if ($dateC->hour == 22 && $dateC->minute == 0) {
            // NotificationUserJobGoodNight::dispatch();
        }
    }


    public function handleContract()
    {
        $listContractExpire10 = DB::table('contracts')->where('rent_to', Carbon::now()->addDays(10))->get();
        $listContractExpire5 = DB::table('contracts')->where('rent_to', Carbon::now()->addDays(5))->get();
        $listContractExpire1 = DB::table('contracts')->where('rent_to', Carbon::now()->addDays(1))->get();
        $listContractExpired = DB::table('contracts')->where('rent_to', '<', Carbon::now())->get();

        foreach ($listContractExpire10 as $itemContractExpire) {
            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                `Hợp đồng sắp hết hạn vào ngày: {$itemContractExpire->rent_to}`,
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                NotiUserDefineCode::USER_NORMAL,
                $itemContractExpire->id,
            );
        }
        foreach ($listContractExpire5 as $itemContractExpire) {
            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                `Hợp đồng sắp hết hạn vào ngày: {$itemContractExpire->rent_to}`,
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                NotiUserDefineCode::USER_NORMAL,
                $itemContractExpire->id,
            );
        }
        foreach ($listContractExpire1 as $itemContractExpire) {
            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                `Hợp đồng sắp hết hạn vào ngày: {$itemContractExpire->rent_to}`,
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                NotiUserDefineCode::USER_NORMAL,
                $itemContractExpire->id,
            );
        }
        foreach ($listContractExpired as $itemContractExpired) {
            NotificationUserJob::dispatch(
                $itemContractExpired->user_id,
                "Thông báo hợp đồng đã hết hạn",
                `Hợp đồng đã hết hạn vào ngày: {$itemContractExpired->rent_to}`,
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                NotiUserDefineCode::USER_NORMAL,
                $itemContractExpired->id,
            );
        }
    }
}
