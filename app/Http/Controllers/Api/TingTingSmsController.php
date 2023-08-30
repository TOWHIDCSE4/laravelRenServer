<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Helper\Helper;
use App\Helper\PhoneUtils;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\HistoryMotelBillOtp;
use App\Models\LastSentOtp;
use App\Models\MsgCode;
use App\Models\OtpCodePhone;
use Carbon\Carbon;


/**
 * @group  Gui OTP
 */
class TingTingSmsController extends Controller
{
    private $apiKey = '16eZwau4S4fDIREj3vT640155a128601';
    private $API_URL = 'https://v1.tingting.im/api';
    private $WIDGET_URL = 'https://widgetapiv1.tingting.im/api';
    public static $sender = "RENCITY";

    /**
     * Send
     */
    static public function send(Request $request)   // send with api
    {
        $curl = curl_init();
        $is_voice = true;
        $phone = $request->phone_number;

        $keySmsTingTing = "16eZwau4S4fDIREj3vT640155a128601";

        $otp = Helper::generateRandomNum(6);
        $now = Helper::getTimeNowString();
        $phoneHasAreaCode = PhoneUtils::convert($phone, true);
        $phone = PhoneUtils::convert($phone);
        $otpExis = LastSentOtp::where('phone', $phone)->orderBy('id', 'desc')->first();
        $valid = PhoneUtils::convert($phone);

        if (
            $phone == null ||
            $valid  == false
        ) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $hasLast = LastSentOtp::where("phone", $phone)->orderBy('id', 'desc')->first();

        $timeNow = Carbon::now('Asia/Ho_Chi_Minh');
        $dateFrom = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 00:00:00';
        $dateTo = $timeNow->year . '-' . $timeNow->month . '-' . $timeNow->day . ' 23:59:59';

        $totalSMSInDay = LastSentOtp::where("phone", $phone)
            ->where('updated_at', '>=',  $dateFrom)
            ->where('updated_at', '<=', $dateTo)
            ->count();

        if ($totalSMSInDay > 5) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => "TIME_IS_TOO_CLOSE",
                'msg' => "Hôm nay bạn đã gửi vượt quá 5 tin",
            ], Response::HTTP_BAD_REQUEST);
        }

        if (Cache::lock($phone, 10)->get()) {
            //tiếp tục handle
        } else {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' =>  MsgCode::ERROR[0],
                'msg' => "Đã gửi otp",
            ], 400);
        }

        if ($hasLast != null) {
            $time1 = Carbon::parse($hasLast->time_generate);
            $time1 = $time1->addSeconds(29);
            $time2 = Carbon::parse($now);
            $span =  $time2->diffInSeconds($time1, false);

            if ($span <= 29 && $span > 0) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => "TIME_IS_TOO_CLOSE",
                    'msg' => "Vui lòng gửi lại sau " . (29 - (29 - $span)) . "s",
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($valid == true) {
            // $noidung = "Ma xac thuc cua ban la: $otp Cam on ban da su dung dich vu.";
            // $smsAPI = new TingTingSMSAPI($keySmsTingTing);

            $now = Helper::getTimeNowDateTime();
            $timezone = 'GMT+7';

            $content = "[RENCITY] Mã xác thực của bạn tại app Rencity là " . $otp;
            $type = 3;
            $sender = "RENCITY";

            // $response = $smsAPI->sendSMS($phoneHasAreaCode, $sender, $content, $now->format('Y-m-d H:i:s'), $timezone);
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://v1.tingting.im/api/sms?apikey=16eZwau4S4fDIREj3vT640155a128601',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(
                    [
                        "to" => $phoneHasAreaCode,
                        "content" => "[RENCITY] Mã xác thực của bạn tại app Rencity là " . $otp,
                        "sender" => "RENCITY"
                    ]
                ),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: tingting_session=KpbsQG2OzRJW9f4Fufh1XkmotmWqcExMIRv1ZdkF'
                ),
            ));

            $response = json_decode(curl_exec($curl));

            if ($response != null && isset($response->status) && $response->status == "success") {

                LastSentOtp::create([
                    "area_code" => "+84",
                    "otp" =>  $otp,
                    "ip" => $request->ip(),
                    "phone" => $phone,
                    "time_generate" => $now,
                ]);

                $otpExis = OtpCodePhone::where('phone', $phone)->first();

                if ($otpExis == null) {
                    OtpCodePhone::create([
                        "area_code" => "+84",
                        "otp" =>  $otp,
                        "previous_otp" =>  $otp,
                        "phone" => $phone,
                        "time_generate" => $now,
                        "content" =>  $content,
                    ]);
                } else {
                    $otpExis->update([
                        "otp" =>  $otp,
                        "time_generate" => $now,
                    ]);
                }

                return response()->json([
                    'code' => 200,
                    'success' => true,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ], 200);
            } else {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::CANT_SEND_OTP[0],
                    'msg' => MsgCode::CANT_SEND_OTP[1],
                    'info' =>   $response
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return response()->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'success' => false,
            'msg_code' => MsgCode::CANT_SEND_OTP[0],
            'msg' => MsgCode::CANT_SEND_OTP[1],
        ], Response::HTTP_BAD_REQUEST);
    }

    static public function sendSmsContractRenter($phoneNumber, $motelName = 'unknown motel name', $towerName = null, $nameHost = null, $user_id = null, $renter_id = null)
    {
        $now = Helper::getTimeNowDateTime();
        $tingTingSms = new TingTingSmsController();
        $phoneHasAreaCode = PhoneUtils::convert($phoneNumber, true);
        $timezone = 'GMT+7';

        $content = "Bạn được thêm vào 1 hợp đồng thuê nhà, hãy tải App Rencity và đăng ký theo số điện thoại này để dễ dàng quản lý và theo dõi nha. Tải app tại: https://rencity.vn";
        $response = $tingTingSms->sendSMS($phoneHasAreaCode, self::$sender, $content, $now->format('Y-m-d H:i:s'), $timezone);

        if ($response != null && isset($response["status"]) && $response["status"] == "success") {
            HistoryMotelBillOtp::create([
                'phone_number' => $phoneNumber,
                'content' => $content,
                'user_id' => $user_id,
                'renter_id' => $renter_id,
                'is_send' => true
            ]);
        } else {
            HistoryMotelBillOtp::create([
                'phone_number' => $phoneNumber,
                'content' => $content,
                'user_id' => $user_id,
                'renter_id' => $renter_id,
                'is_send' => false
            ]);
        }
    }

    static public function sendMotelBill($phoneNumber, $dateBill, $amountMoneyBill, $contract_id = null, $user_id = null, $renter_id = null)
    {
        $tingTingSms = new TingTingSmsController();
        $amountMoneyBill = Helper::currency_money_format($amountMoneyBill);
        $now = Helper::getTimeNowDateTime();
        $phoneHasAreaCode = PhoneUtils::convert($phoneNumber, true);
        $timezone = 'GMT+7';

        $sender = "RENCITY";

        $content = "Hoa don tien phong thang $dateBill cua quy cu dan la $amountMoneyBill, vui long xem chi tiet tren app RENCITY, xin cam on!";
        $response = $tingTingSms->sendSMS($phoneHasAreaCode, $sender, $content, $now->format('Y-m-d H:i:s'), $timezone);

        if ($response != null && isset($response["status"]) && $response["status"] == "success") {
            HistoryMotelBillOtp::create([
                'phone_number' => $phoneNumber,
                'content' => $content,
                'contract_id' => $contract_id,
                'user_id' => $user_id,
                'renter_id' => $renter_id,
                'is_send' => true
            ]);
        } else {
            HistoryMotelBillOtp::create([
                'phone_number' => $phoneNumber,
                'content' => $content,
                'contract_id' => $contract_id,
                'user_id' => $user_id,
                'renter_id' => $renter_id,
                'is_send' => false
            ]);
        }
    }

    //send message to a phone number through Zalo OA
    public function sendZNS($to, $sender, $tempid, $tempdata, $failoverdata = null, $sendTime = '', $timezone = '')
    {

        $params = [
            'to' => $to,
            'sender' => $sender,
            'tempid' => $tempid,
            'temp_data' => $tempdata
        ];

        if (!empty($failoverdata) && is_array($failoverdata)) {

            if (!isset($failoverdata['sender']) || !isset($failoverdata['content'])) {
                return null;
            }

            $params['failover'] = $failoverdata;
        }

        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/zns';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    //send message to a phone number through SMS
    public function sendSMS($to, $sender, $content, $sendTime = '', $timezone = '')
    {
        $params = [
            'to' => $to,
            'sender' => $sender,
            'content' => $content
        ];

        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/sms';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    //call to a phone number and reading the content using text to speech
    public function call($to, $sender, $content, $sendTime = '', $timezone = '')
    {
        $params = [
            'to' => $to,
            'sender' => $sender,
            'content' => $content
        ];
        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }
        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/call';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }


    public function session($configId, $to = '')
    {
        $params = [
            'config_id' => $configId
        ];
        if (!empty($to)) {
            $params['to'] = $to;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->WIDGET_URL . '/session';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function createPin($configId, $to, $channel)
    {
        $params = [
            'config_id' => $configId,
            'to' => $to,
            'channel' => $channel
        ];

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/pin';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function verifyPin($msgId, $pinCode)
    {
        $params = [
            'msg_id' => $msgId,
            'pin_code' => $pinCode
        ];

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/verify';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }
}
