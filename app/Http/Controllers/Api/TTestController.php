<?php

namespace App\Http\Controllers\Api;

use App\AdminJobs\NotificationAdminJob as AdminJobsNotificationAdminJob;
use App\Helper\AccountRankDefineCode;
use App\Helper\DatetimeUtils;
use App\Helper\DefineFolderSaveFile;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StatusOrderServicesSellDefineCode;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\StatusUserDefineCode;
use App\Helper\StatusWithdrawalDefineCode;
use App\Helper\TypeFCM;
use App\Helper\TypeMoneyFromEWalletDefineCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Jobs\NotificationUserJob;
use App\Jobs\NotificationUserJobTest;
use App\Jobs\PushNotificationAdminJob;
use App\Jobs\PushNotificationUserJob;
use App\Mail\SendMailOTP;
use App\Models\CollaboratorReferMotel;
use App\Models\Contract;
use App\Models\EWalletCollaborator;
use App\Models\EWalletCollaboratorHistory;
use App\Models\MoPost;
use App\Models\Motel;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use App\Models\OrderServiceSell;
use App\Models\OtpCodeEmail;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Cron\DayOfMonthField;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Mail;


class TTestController extends Controller
{
    public function testGet(Request $request)
    {

        NotificationUserJobTest::dispatch(
            97,
            "Thông báo hợp đồng sắp hết hạn",
            "Hợp đồng ",
            TypeFCM::CONTRACT_IS_ABOUT_TO_EXPIRE,
            NotiUserDefineCode::USER_NORMAL,
            12,
        );

        return ResponseUtils::json([
            'code' => 400,
            'success' => false,
            'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
            'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
        ]);
    }

    public function testPot(Request $request)
    {
        $userExisted = User::where('id', 45)->first();
        if (isset($request->is_admin) && $request->is_admin == false) {
            $initialAccountType = $userExisted->initial_account_type;
            $checkUserIsHost = DB::table('motels')
                ->where('motels.user_id', $userExisted->id)
                ->exists();
            if ($checkUserIsHost) {
                $initialAccountType = StatusUserDefineCode::USER_IS_HOST;
            }
            dd($initialAccountType, $checkUserIsHost);
            $userExisted->update([
                'initial_account_type' => $initialAccountType
            ]);
        }
    }
}
