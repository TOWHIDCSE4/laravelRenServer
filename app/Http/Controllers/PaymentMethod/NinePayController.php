<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\StatusPaymentHistory;
use Illuminate\Http\Request;
use App\Jobs\PushNotificationJob;
use App\Models\UserDeviceToken;
// use App\Traits\NinePay;
use App\Http\Controllers\PaymentMethod\lib\HMACSignature;
use  App\Http\Controllers\PaymentMethod\lib\MessageBuilder;

/**
 * @group  Customer/thanh toán onpay
 */
class NinePayController extends Controller
{

    const MERCHANT_KEY = 'Fdakr9';
    const MERCHANT_SECRET_KEY = 'sYGDQGOYLojD5w4uTVZLgJiZ3lkeqahk5aP';
    const END_POINT = 'https://sand-payment.9pay.vn';

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
            $returnUrl = str_replace('index.php', '', $backUrl);
            $time = time();

            $data = array(
                'merchantKey' => self::MERCHANT_KEY,
                'time' => $time,
                'invoice_no' => $request->invoice_no,
                'amount' => $request->amount,
                'description' => $request->description,

                'back_url' => $backUrl,
                'return_url' => "{$returnUrl}result.php",
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
}
