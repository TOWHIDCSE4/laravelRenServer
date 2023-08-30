<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Jobs\PushNotificationUserJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EveryDayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:every_day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to user';

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
        // $this->handleNotiToCustomer();
        $this->handleContract();
        $this->handleBillToRenter();
        $this->info('Success');
    }

    public function handleNoticeToUser()
    {
        $date = Helper::getTimeNowDateTime();
        $dateC =  Carbon::parse(Helper::getTimeNowString());
        $time1 = $date->format('H:i:00');
        $time2 = $date->format('H:i:59');

        $dayNow = (int)$date->format('d');
        $monthNow =  (int)$date->format('m');
        $dayOfWeek =    (int)$dateC->dayOfWeek;
    }

    public function handleContract()
    {
        $listContractExpire10 = DB::table('contracts')->where([
            ['rent_to', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 10, date('Y')))],
            ['rent_to', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') + 10, date('Y')))]
        ])
            ->get();
        $listContractExpire5 = DB::table('contracts')->where([
            ['rent_to', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 5, date('Y')))],
            ['rent_to', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') + 5, date('Y')))]
        ])
            ->get();
        $listContractExpire1 = DB::table('contracts')->where([
            ['rent_to', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 1, date('Y')))],
            ['rent_to', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') + 1, date('Y')))]
        ])
            ->get();
        $listContractExpire = DB::table('contracts')->where([
            ['rent_to', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')))],
            ['rent_to', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d'), date('Y')))]
        ])
            ->get();
        $listContractExpired = DB::table('contracts')->where([
            ['rent_to', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - 1, date('Y')))],
            ['rent_to', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') - 1, date('Y')))]
        ])
            ->get();

        foreach ($listContractExpire10 as $itemContractExpire) {
            $listUserRenterExpire = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where([
                    ['contract_id', $itemContractExpire->id],
                    ['motel_id', $itemContractExpire->motel_id],
                    ['user_id', $itemContractExpire->user_id]
                ])
                ->distinct('phone_number')
                ->pluck('users.id');
            $motelExist = DB::table('motels')
                ->where('id', $itemContractExpire->motel_id)
                ->first();

            foreach ($listUserRenterExpire as $userId) {
                NotificationUserJob::dispatch(
                    $userId,
                    "Thông báo hợp đồng sắp hết hạn",
                    "Hợp đồng " . ($motelExist->motel_name ?? $itemContractExpire->motel_id) . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                    TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                    NotiUserDefineCode::USER_NORMAL,
                    $itemContractExpire->id,
                );
            }

            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                "Hợp đồng " . ($motelExist->motel_name ?? $itemContractExpire->motel_id) . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE_MANAGE,
                NotiUserDefineCode::USER_IS_HOST,
                $itemContractExpire->id,
            );
        }

        foreach ($listContractExpire5 as $itemContractExpire) {
            $listUserRenterExpire = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where([
                    ['contract_id', $itemContractExpire->id],
                    ['motel_id', $itemContractExpire->motel_id],
                    ['user_id', $itemContractExpire->user_id]
                ])
                ->distinct('phone_number')
                ->pluck('users.id');

            $motelExist = DB::table('motels')
                ->where('id', $itemContractExpire->motel_id)
                ->first();

            foreach ($listUserRenterExpire as $userId) {
                NotificationUserJob::dispatch(
                    $userId,
                    "Thông báo hợp đồng sắp hết hạn",
                    "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                    TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                    NotiUserDefineCode::USER_NORMAL,
                    $itemContractExpire->id,
                );
            }

            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE_MANAGE,
                NotiUserDefineCode::USER_IS_HOST,
                $itemContractExpire->id,
            );
        }

        foreach ($listContractExpire1 as $itemContractExpire) {
            $listUserRenterExpire = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where([
                    ['contract_id', $itemContractExpire->id],
                    ['motel_id', $itemContractExpire->motel_id],
                    ['user_id', $itemContractExpire->user_id]
                ])
                ->distinct('phone_number')
                ->pluck('users.id');

            $motelExist = DB::table('motels')
                ->where('id', $itemContractExpire->motel_id)
                ->first();

            foreach ($listUserRenterExpire as $userId) {
                NotificationUserJob::dispatch(
                    $userId,
                    "Thông báo hợp đồng sắp hết hạn",
                    "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                    TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                    NotiUserDefineCode::USER_NORMAL,
                    $itemContractExpire->id,
                );
            }

            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE_MANAGE,
                NotiUserDefineCode::USER_IS_HOST,
                $itemContractExpire->id,
            );
        }

        foreach ($listContractExpire as $itemContractExpire) {
            $listUserRenterExpire = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where([
                    ['contract_id', $itemContractExpire->id],
                    ['motel_id', $itemContractExpire->motel_id],
                    ['user_id', $itemContractExpire->user_id]
                ])
                ->distinct('phone_number')
                ->pluck('users.id');

            $motelExist = DB::table('motels')
                ->where('id', $itemContractExpire->motel_id)
                ->first();

            foreach ($listUserRenterExpire as $userId) {
                NotificationUserJob::dispatch(
                    $userId,
                    "Thông báo hợp đồng sắp hết hạn",
                    "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                    TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
                    NotiUserDefineCode::USER_NORMAL,
                    $itemContractExpire->id,
                );
            }

            NotificationUserJob::dispatch(
                $itemContractExpire->user_id,
                "Thông báo hợp đồng sắp hết hạn",
                "Hợp đồng " . $motelExist->motel_name . " sắp hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpire->rent_to)),
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE_MANAGE,
                NotiUserDefineCode::USER_IS_HOST,
                $itemContractExpire->id,
            );
        }

        foreach ($listContractExpired as $itemContractExpired) {
            $listUserRenterExpire = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where([
                    ['contract_id', $itemContractExpired->id],
                    ['motel_id', $itemContractExpired->motel_id],
                    ['user_id', $itemContractExpired->user_id]
                ])
                ->distinct('phone_number')
                ->pluck('users.id');

            $motelExist = DB::table('motels')
                ->where('id', $itemContractExpired->motel_id)
                ->first();

            foreach ($listUserRenterExpire as $userId) {
                NotificationUserJob::dispatch(
                    $userId,
                    "Thông báo hợp đồng đã hết hạn",
                    "Hợp đồng " . ($motelExist->motel_name ?? $itemContractExpired->motel_id) . " đã hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpired->rent_to)),
                    TypeFCM::CONTRACT_EXPIRED,
                    NotiUserDefineCode::USER_NORMAL,
                    $itemContractExpired->id,
                );
            }

            NotificationUserJob::dispatch(
                $itemContractExpired->user_id,
                "Thông báo hợp đồng đã hết hạn",
                "Hợp đồng " . ($motelExist->motel_name ?? $itemContractExpired->motel_id) . " đã hết hạn vào ngày: " . date("Y-m-d", strtotime($itemContractExpired->rent_to)),
                TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE_MANAGE,
                NotiUserDefineCode::USER_IS_HOST,
                $itemContractExpired->id,
            );
        }
    }

    public function handleBillToRenter()
    {
        $allRenterNearDuePayment10days = DB::table('users')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where('contracts.status', StatusContractDefineCode::COMPLETED)
            ->where([
                ['contracts.pay_start', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 10, date('Y')))],
                ['contracts.pay_start', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') + 10, date('Y')))]
            ])
            ->select('users.*')
            ->distinct()
            ->get();

        $allRenterNearDuePayment = DB::table('users')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where('contracts.status', StatusContractDefineCode::COMPLETED)
            ->where([
                ['contracts.pay_start', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')))],
                ['contracts.pay_start', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d'), date('Y')))]
            ])
            ->select('users.*')
            ->distinct()
            ->get();


        foreach ($allRenterNearDuePayment10days as $user) {
            $listMotelRented = DB::table('motels')->join('contracts', 'motels.id', '=', 'contracts.motel_id')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['user_contracts.renter_phone_number', $user->phone_number],
                ])
                ->where([
                    ['contracts.pay_start', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 10, date('Y')))],
                    ['contracts.pay_start', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d') + 10, date('Y')))]
                ])
                ->select('motels.*', 'contracts.money as money_motel_contract', 'contracts.pay_start as date_payment_contract', 'contracts.id as contract_id')
                ->first();

            foreach ($listMotelRented as $motelRented) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Thông báo sắp đến hạn đóng tiền phòng",
                    "Thông báo sắp đến hạn đóng tiền phòng: " . $motelRented->motel_name . " vào ngày: " . date("Y-m-d", strtotime($motelRented->date_payment_contract)) . " với số tiền: " . Helper::currency_money_format($motelRented->money_motel_contract),
                    TypeFCM::PAYMENT_DATE_MOTEL_IS_COMING,
                    NotiUserDefineCode::USER_NORMAL,
                    $listMotelRented->contract_id,
                );
            }
        }

        foreach ($allRenterNearDuePayment as $user) {
            $listMotelRented = DB::table('motels')->join('contracts', 'motels.id', '=', 'contracts.motel_id')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['user_contracts.renter_phone_number', $user->phone_number],
                ])
                ->where([
                    ['contracts.pay_start', '<=', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')))],
                    ['contracts.pay_start', '>=', date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d'), date('Y')))]
                ])
                ->select('motels.*', 'contracts.money as money_motel_contract', 'contracts.pay_start as date_payment_contract', 'contracts.id as contract_id')
                ->first();

            foreach ($listMotelRented as $motelRented) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Thông báo đến hạn đóng tiền phòng",
                    "Thông báo đến hạn đóng tiền phòng: " . $motelRented->motel_name . " vào ngày: " . date("Y-m-d", strtotime($motelRented->date_payment_contract))  . " với số tiền: " . Helper::currency_money_format($motelRented->money_motel_contract),
                    TypeFCM::MATURITY_MOTEL,
                    NotiUserDefineCode::USER_NORMAL,
                    $listMotelRented->contract_id,
                );
            }
        }
    }
}
