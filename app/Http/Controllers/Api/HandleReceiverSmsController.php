<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
// use App\Models\OtpCodePhone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\TextUI\Help;

class HandleReceiverSmsController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Nhan du lieu request tu iNET gui qua
        $code               = $_REQUEST['code'] ?? "";            // Ma chinh
        $subCode            = $_REQUEST['subCode'] ?? "";         // Ma phu
        $mobile             = $_REQUEST['mobile'] ?? "";          // So dien thoai +84
        $serviceNumber      = $_REQUEST['serviceNumber'] ?? "";   // Dau so 8x85
        $info               = $_REQUEST['info'] ?? "";            // Noi dung tin nhan
        $ipremote           = $_SERVER['REMOTE_ADDR'] ?? "";      // IP server goi qua truyen du lieu

        // 2. Ghi log va kiem tra du lieu
        // Tim file log.txt tai thu muc chua file php xu ly sms nay
        // kiem tra de biet ban da nhan du thong tin ve tin nhan hay chua
        $text = $code . " - " . $subCode . " - " . $mobile . " - " . $serviceNumber . " - " . $ipremote . " - " . $info;


        // 2. Kiem tra bao mat du lieu tu iNET gui qua
        // Lien he voi iNET de lay IP nay
        // 210.211.127.168
        // 210.211.127.172
        if ($_SERVER['REMOTE_ADDR'] != '210.211.127.168' && $_SERVER['REMOTE_ADDR'] != '210.211.127.172') {
            echo $_SERVER['REMOTE_ADDR'];
            echo "Authen Error";
            exit;
        }

        // 3. Xu ly du lieu cua ban tai day
        // ket noi csdl
        $otp = Helper::generateRandomNum(6);
        $phone = PhoneUtils::convert($mobile);
        $valid = PhoneUtils::isNumberPhoneValid($phone);
        $now = Helper::getTimeNowString();


        if ($valid == true) {

            $otpExis = OtpCodePhone::where('phone', $phone)->first();
            if ($otpExis == null) {
                OtpCodePhone::create([
                    "area_code" => "+84",
                    "otp" =>  $otp,
                    "phone" => $phone,
                    "time_generate" => $now,
                    "content" => $text,
                ]);
            } else {

                $time1 = Carbon::parse($otpExis->time_generate);
                $time1 = $time1->addMinutes(7);

                $time2 = Carbon::parse($now);


                $otpExis->update([
                    "otp" =>  $otp,
                    "time_generate" => $now,
                ]);


                if (!HandleReceiverSmsController::has_expired_otp($phone)) {
                    $otp = $otpExis->otp;

                    $otpExis->update([
                        "otp" =>   $otp,
                        "time_generate" => $now,
                    ]);
                } else {
                    $otpExis->update([
                        "otp" =>  $otp,
                        "time_generate" => $now,
                    ]);
                }
            }
        }

        // xu ly du lieu


        // 5. Tra ve tin nha gom kieu tin nhan (0) va noi dung tin nhan
        // Xuong dong trong tin nhan su dung \n
        $noidung = "Ma pin cua ban la: $otp Cam on ban da su dung dich vu.";
        echo "0|" . $noidung;
    }

    static function has_expired_otp($from, $type = null) //da het han
    {
        $now = Helper::getTimeNowString();

        $otpExis = null;
        if ($type  == "email") {
            $otpExis = OtpCodeEmail::where('email', $from)->first();
        } else {
            $otpExis = OtpCodePhone::where('phone', $from)->first();
        }

        if ($otpExis == null) return false;
        $time1 = Carbon::parse($otpExis->time_generate);
        $time1 = $time1->addMinutes(7);

        $time2 = Carbon::parse($now);

        if ($time1 > $time2) {
            return false;
        } else {
            return true;
        }
    }


    static function reset_otp($phone)
    {
        $otp = Helper::generateRandomNum(6);
        $now = Helper::getTimeNowString();
        $phone = PhoneUtils::convert($phone);
        $otpExis = OtpCodePhone::where('phone', $phone)->first();
        if ($otpExis == null) {
            OtpCodePhone::create([
                "area_code" => "+84",
                "otp" =>  $otp,
                "phone" => $phone,
                "time_generate" => $now,
            ]);
        } else {
            $otpExis->update([
                "otp" =>  $otp,
                "time_generate" => $now,
            ]);
        }
    }
}
