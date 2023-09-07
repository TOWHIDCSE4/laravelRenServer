<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\StatusPaymentHistory;
use App\Models\User;
use App\Models\VirtualAccount;
use Exception;
use Illuminate\Http\Request;
use App\Jobs\PushNotificationJob;
use App\Models\UserDeviceToken;
// use App\Traits\NinePay;
use App\Http\Controllers\PaymentMethod\lib\HMACSignature;
use  App\Http\Controllers\PaymentMethod\lib\MessageBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @group  Customer/thanh toán onpay
 */
class NinePayController extends Controller
{

    const MERCHANT_KEY = 'Fdakr9';
    const MERCHANT_SECRET_KEY = 'sYGDQGOYLojD5w4uTVZLgJiZ3lkeqahk5aP';
    const END_POINT = 'https://sand-payment.9pay.vn';
    const UNAME = 'Rencity';
    const DEFAULT_BANK_CODE = 'BIDV';

    public function callAPI($method, $url, $data, $headers = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // EXECUTE:
        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }

    public function refundCreate(Request $request)
    {
        $request_id = time() + rand(0, 999999);
        $amount = $request->amount;
        $payment_no = $request->payment_no;
        $description = "Mô tả giao dịch";
        $time = time();
        $refund_param = array(
            'request_id' => $request_id,
            'payment_no' => $payment_no,
            'amount' => $amount,
            'description' => "Test Refund " . $payment_no,
        );
        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/refunds/create', 'POST')
            ->withParams($refund_param)
            ->build();
        $hmacs = new HMACSignature();
        $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

        $headers = array(
            'Date: ' . $time,
            'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
        );

        $response = self::callAPI('POST', self::END_POINT . '/refunds/create', $refund_param, $headers);

        echo 'HEADERs:';
        print_r($headers);
        echo '<hr>RESULT:';
        print_r($response);
    }

    public function invoiceInquire(Request $request, $request_id)
    {

        $amount = $request->amount;
        $payment_no = $request->payment_no;
        $time = time();
        $refund_param = array(
            'request_id' => $request_id,
            'payment_no' => $payment_no,
            'amount' => $amount,
            'description' => "Test Refund " . $payment_no,
        );
        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/refunds/create', 'POST')
            ->withParams($refund_param)
            ->build();
        $hmacs = new HMACSignature();
        $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

        $headers = array(
            'Date: ' . $time,
            'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
        );

        $response = self::callAPI('POST', self::END_POINT . '/refunds/create', $refund_param, $headers);

        echo 'HEADERs:';
        print_r($headers);
        echo '<hr>RESULT:';
        print_r($response);
    }


    public function paymentCreate(Request $request)
    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
            $backUrl = "$http$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $returnUrl = str_replace('payments-create', '', $backUrl);
            $time = time();

            $data = array(
                'merchantKey' => self::MERCHANT_KEY,
                'time' => $time,
                'invoice_no' => $request->invoice_no,
                'amount' => $request->amount,
                'description' => $request->description,

                'back_url' => $backUrl,
                'return_url' => "{$returnUrl}result",
            );

            $message = MessageBuilder::instance()
                ->with($time, self::END_POINT . '/payments/create', 'POST')
                ->withParams($data)
                ->build();


            $hmacs = new HMACSignature();
            $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

            $httpData = [
                'baseEncode' => base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)),
                'signature' => $signature,
            ];
            $redirectUrl = self::END_POINT . '/portal?' . http_build_query($httpData);
            echo '<pre>';
            print_r($data);
            echo '<br/>';
            echo '<hr/>';
            print_r($message);
            echo '<br/>';
            echo '<hr/>';
            var_dump($httpData);
            echo '<br/>';
            echo '<hr/>';
            print_r($redirectUrl);
            exit();
            //return header('Location: ' . $redirectUrl);
        }
    }


    public function inquire(Request $request)
    {
        $time = time();
        $invoice_no = $request->invoice_no;
        $data = [];
        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/v2/payments/' . $invoice_no . '/inquire', 'GET')
            ->withParams($data)
            ->build();
        $hmacs = new HMACSignature();
        $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

        $headers = array(
            'Date: ' . $time,
            'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
        );

        var_dump($headers);

        echo 'RESPONSE<br/>';
        $response = self::callAPI('GET', self::END_POINT . '/v2/payments/' . $invoice_no . '/inquire', false, $headers);
        var_dump($response);
    }


    public function result(Request $request)
    {
        $secretKeyChecksum = 'sYGDQGOYLojD5w4uTVZLgJiZ3lkeqahk5aP';
        $result = 'eyJhbW91bnQiOjExMDAwMCwiYW1vdW50X2ZvcmVpZ24iOm51bGwsImFtb3VudF9vcmlnaW5hbCI6bnVsbCwiYW1vdW50X3JlcXVlc3QiOjExMDAwMCwiYmFuayI6bnVsbCwiY2FyZF9icmFuZCI6IlZJU0EiLCJjYXJkX2luZm8iOnsidG9rZW4iOiIzMDQ2NWEwMDRiZjU0NWQxNjkyMWEyY2ZhMjAwNmVlYyIsImNhcmRfbmFtZSI6Ik5HVVlFTiBWQU4gQSIsImhhc2hfY2FyZCI6ImI1NDdjNjdhZmJkODM0N2Y2ZWY0YmFhMGViOGFkZDkyIiwiY2FyZF9icmFuZCI6IlZJU0EiLCJjYXJkX251bWJlciI6IjQwMDU1NXh4eHh4eDAwMDkifSwiY3JlYXRlZF9hdCI6IjIwMjItMDYtMDNUMDI6MDY6MjguMDAwMDAwWiIsImN1cnJlbmN5IjoiVk5EIiwiZGVzY3JpcHRpb24iOiJUaGFuaCB0b8OhbiDEkcahbiBow6BuZyBRUDE2NTQyNDcxNzE1MjMwMzU4IiwiZXhjX3JhdGUiOm51bGwsImZhaWx1cmVfcmVhc29uIjpudWxsLCJmb3JlaWduX2N1cnJlbmN5IjpudWxsLCJpbnZvaWNlX25vIjoiUVAxNjU0MjQ3MTcxNTIzMDM1OCIsImxhbmciOm51bGwsIm1ldGhvZCI6IkNSRURJVF9DQVJEIiwicGF5bWVudF9ubyI6Mjk5Nzc4NTIyODk1LCJzdGF0dXMiOjUsInRlbm9yIjpudWxsfQ';
        $checksum = 'SLtZLhRIsOnUDdtigF9b9QTPIGR444M8';


        $hashChecksum = strtoupper(hash('sha256', $result . $secretKeyChecksum));
        if ($hashChecksum === $checksum) {
            echo 'Dữ liệu đúng';
        } else {
            echo 'Dữ liệu không hợp lệ';
        }

        Log::info(json_encode($this->urlsafeB64Decode($result)));
        print_r($this->urlsafeB64Decode($result));
    }

    function urlsafeB64Decode($input)
    {
        $remainder = \strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= \str_repeat('=', $padlen);
        }
        return \base64_decode(\strtr($input, '-_', '+/'));
    }


    // Create Virtual Account (VA)
    public function createVirtualAccount(Request $request)
    {
        $request_id = uniqid();
        $uid = $request->user->id;
        $uname = self::UNAME;
        $bank_code = $request->bank_code;
        $request_amount = $request->request_amount;

        $virtual_account = null;

        $time = time();
        $virtual_account_param = [
            "request_id" => $request_id,
            "uid" => $uid,
            "uname" => $uname,
            "bank_code" => $bank_code,
            "request_amount" => $request_amount,
        ];

        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/va/create', 'POST')
            ->withParams($virtual_account_param)
            ->build();

        DB::beginTransaction();
        try {
            $hmacs = new HMACSignature();
            $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

            $headers = array(
                'Date: ' . $time,
                'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
            );

            $response = self::callAPI('POST', self::END_POINT . '/va/create', $virtual_account_param, $headers);

            $response_data = json_decode($response);

            if(isset($response_data->error_code) && $response_data->error_code == '001'){
                return ResponseUtils::json([
                    'code' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'msg_code' => MsgCode::ALREADY_VIRTUAL_ACCOUNT_EXISTS[0],
                    'msg' => MsgCode::ALREADY_VIRTUAL_ACCOUNT_EXISTS[1]
                ]);
            }

            if (isset($response_data->status) && $response_data->status == 5) {
                $virtual_account = VirtualAccount::query()
                    ->create([
                        'user_id' => $uid,
                        'request_id' => $request_id,
                        'bank_code' => $bank_code,
                        'request_amount' => $request_amount,
                        'bank_account_no' => $response_data->data->bank_account_no,
                        'bank_account_name' => $response_data->data->bank_account_name,
                        'qr_code_url' => $response_data->data->qr_code_url,
                    ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $virtual_account,
        ]);
    }

    //  Update Virtual Account (VA)
    public function updateVirtualAccount(Request $request)
    {
        $virtual_account = VirtualAccount::query()->where('user_id', $request->user->id)->first();;

        if (!$virtual_account) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[0],
                'msg' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[1]
            ]);
        }

        $request_id = $virtual_account->request_id;
        $uid = $request->user->id;
        $uname = self::UNAME;
        $bank_code = $request->bank_code;
        $request_amount = $request->request_amount;
        $is_active = $request->is_active;

        $time = time();
        $virtual_account_param = [
            "request_id" => $request_id,
            "uid" => $uid,
            "uname" => $uname,
            "bank_code" => $bank_code,
            "request_amount" => $request_amount,
            "is_active" => $is_active,
        ];

        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/va/update', 'POST')
            ->withParams($virtual_account_param)
            ->build();

        DB::beginTransaction();
        try {
            $hmacs = new HMACSignature();
            $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

            $headers = array(
                'Date: ' . $time,
                'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
            );

            $response = self::callAPI('POST', self::END_POINT . '/va/update', $virtual_account_param, $headers);
            $response_data = json_decode($response);

            if (isset($response_data->status) && $response_data->status == 5) {
                VirtualAccount::query()
                    ->where('user_id', $uid)
                    ->update([
                        'user_id' => $uid,
                        'request_id' => $request_id,
                        'bank_code' => $bank_code,
                        'request_amount' => $request_amount,
                        'is_active' => $is_active,
                        'bank_account_name' => $response_data->data->bank_account_name,
                        'bank_account_no' => $response_data->data->bank_account_no,
                        'qr_code_url' => $response_data->data->qr_code_url,
                    ]);

                DB::commit();

                $virtual_account = VirtualAccount::query()->where('user_id', $uid)->first();
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $virtual_account,
        ]);
    }

    //  Query Virtual Account info
    public function infoVirtualAccount(Request $request)
    {
        $virtual_account = VirtualAccount::query()
            ->where('user_id', $request->user->id)
            ->first();;

        if (!$virtual_account) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[0],
                'msg' => MsgCode::NO_VIRTUAL_ACCOUNT_EXISTS[1]
            ]);
        }

        $uid = $request->user->id;
        $bank_code = $request->bank_code;

        $time = time();
        $virtual_account_param = [
            "uid" => $uid,
            "bank_code" => $bank_code,
        ];

        $message = MessageBuilder::instance()
            ->with($time, self::END_POINT . '/va/info', 'POST')
            ->withParams($virtual_account_param)
            ->build();

        try{
            $hmacs = new HMACSignature();
            $signature = $hmacs->sign($message, self::MERCHANT_SECRET_KEY);

            $headers = array(
                'Date: ' . $time,
                'Authorization: Signature Algorithm=HS256,Credential=' . self::MERCHANT_KEY . ',SignedHeaders=,Signature=' . $signature
            );

            $response = self::callAPI('POST', self::END_POINT . '/va/info', $virtual_account_param, $headers);
            $response_data = json_decode($response);

            if (isset($response_data->status) && $response_data->status == 5) {
                VirtualAccount::query()
                    ->where('user_id', $uid)
                    ->update([
                        'user_id' => $uid,
                        'bank_code' => $bank_code,
                    ]);

                DB::commit();

                $virtual_account = VirtualAccount::query()
                    ->where('user_id', $uid)
                    ->first();
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $virtual_account,
        ]);
    }


}
