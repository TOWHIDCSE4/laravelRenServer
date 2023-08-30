<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\StatusPaymentHistory;
use Exception;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán onpay
 */
class OnePayController extends Controller
{


    public function index(Request $request)
    {
        return $this->pay($request);
    }


    public function create(Request $request)
    {
        $host = $request->getSchemeAndHttpHost();

        $order_code = $request->order->order_code;
        $store_code = $request->store->store_code;
        $SECURE_SECRET = "A3EFDFABA8653DF2342E8DAC29B51AF0"; //Chuỗi bí mật

        session(['cost_id' => $request->id]);
        session(['url_prev' => url()->previous()]);

        $vpc_Url = "https://mtf.onepay.vn/onecomm-pay/vpc.op";

        $vpc_AccessCode = "D67342C2";
        $vpc_Merchant = "ONEPAY";

        $vpc_Amount = $request->order->total_final * 100;
        $vpc_Locale = 'vn';
        $vpc_IpAddr = request()->ip();
        $vnp_Returnurl = $host . "/api/customer/$store_code/purchase/return/one_pay";

        $inputData = array(
            "vpc_Version" => "2",
            "vpc_Currency" => "VND",
            "vpc_Command" => "pay",
            "vpc_AccessCode" => $vpc_AccessCode,
            "vpc_Merchant" => $vpc_Merchant,
            "vpc_Locale" =>  $vpc_Locale,

            "vpc_ReturnURL" =>  $vnp_Returnurl,
            "vpc_MerchTxnRef" => Helper::generateRandomNum(20),
            "vpc_OrderInfo" =>  $order_code,
            "vpc_Amount" => $vpc_Amount,
            "vpc_TicketNo" => $vpc_IpAddr,
            "Title" => "Thanh toán đơn hàng " . $order_code,
            "AgainLink" => $vnp_Returnurl
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vpc_Url = $vpc_Url . "?" . $query;


        $stringHashData = "";

        // sort all the incoming vpc response fields and leave out any with no value
        foreach ($inputData as $key => $value) {
            //        if ($key != "vpc_SecureHash" or strlen($value) > 0) {
            //            $stringHashData .= $value;
            //        }
            //      *****************************chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về*****************************
            if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0, 4) == "vpc_") || (substr($key, 0, 5) == "user_"))) {
                $stringHashData .= $key . "=" . $value . "&";
            }
        }
        //  *****************************Xóa dấu & thừa cuối chuỗi dữ liệu*****************************
        $stringHashData = rtrim($stringHashData, "&");



        $vnpSecureHash =   strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $SECURE_SECRET)));
        $vpc_Url .= 'vpc_SecureHash=' . $vnpSecureHash;




        return redirect($vpc_Url);
    }

    public function return(Request $request)
    {
        $SECURE_SECRET = "A3EFDFABA8653DF2342E8DAC29B51AF0";

        $vpc_Txn_Secure_Hash = $_GET["vpc_SecureHash"];
        unset($_GET["vpc_SecureHash"]);
        $errorExists = false;
        ksort($_GET);

        $inputData = $_GET;

        if (strlen($SECURE_SECRET) > 0 && $_GET["vpc_TxnResponseCode"] != "7" && $_GET["vpc_TxnResponseCode"] != "No Value Returned") {

            //$stringHashData = $SECURE_SECRET;
            //*****************************khởi tạo chuỗi mã hóa rỗng*****************************
            $stringHashData = "";

            // sort all the incoming vpc response fields and leave out any with no value
            foreach ($_GET as $key => $value) {
                //        if ($key != "vpc_SecureHash" or strlen($value) > 0) {
                //            $stringHashData .= $value;
                //        }
                //      *****************************chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về*****************************
                if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0, 4) == "vpc_") || (substr($key, 0, 5) == "user_"))) {
                    $stringHashData .= $key . "=" . $value . "&";
                }
            }
            //  *****************************Xóa dấu & thừa cuối chuỗi dữ liệu*****************************
            $stringHashData = rtrim($stringHashData, "&");


            //    if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper ( md5 ( $stringHashData ) )) {
            //    *****************************Thay hàm tạo chuỗi mã hóa*****************************
            if (strtoupper($vpc_Txn_Secure_Hash) == strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*', $SECURE_SECRET)))) {
                // Secure Hash validation succeeded, add a data field to be displayed
                // later.
                $hashValidated = "CORRECT";
            } else {
                // Secure Hash validation failed, add a data field to be displayed
                // later.
                $hashValidated = "INVALID HASH";
            }
        } else {
            // Secure Hash was not validated, add a data field to be displayed later.
            $hashValidated = "INVALID HASH";
        }

        if ($hashValidated  == "CORRECT" && $_GET["vpc_TxnResponseCode"] == "0") {

            $historyExists = StatusPaymentHistory::where(
                'order_code',
                $inputData['vpc_OrderInfo']
            )->orderBy('id', 'desc')->first();

            $orderExists = Order::where(
                'order_code',
                $inputData['vpc_OrderInfo']
            )->first();

            if (empty($historyExists)) {
                StatusPaymentHistory::create(
                    [
                        "order_code" => $inputData['vpc_OrderInfo'],
                        "transaction_no" => $inputData['vpc_TransactionNo'],
                        "amount" => ($inputData['vpc_Amount'] != null && $inputData['vpc_Amount'] > 0) ? $inputData['vpc_Amount'] / 100 : 0,
                        "bank_code" => $inputData['vpc_AdditionData'],
                        "card_type" => "",
                        "order_info" => $inputData['vpc_OrderInfo'],
                        "pay_date" => Helper::getTimeNowString(),
                        "response_code" => $inputData['vcp_Message'],
                        "key_code_customer" => "",
                    ]
                );

                if (!empty($orderExists)) {

                    PushNotificationUserJob::dispatch(
                        $request->store->user_id,
                        'Shop ' . $orderExists->store->name,
                        'Đơn hàng ' . $orderExists->order_code . ' đã được thanh toán',
                        TypeFCM::CUSTOMER_PAID,
                        $orderExists->order_code,
                        null
                    );

                    if ($orderExists->customer_id != null) {
                        OrderRecord::create(
                            [
                                'store_id' =>  $orderExists->store->id,
                                'customer_id' => $orderExists->customer_id,
                                'order_id' => $orderExists->id,
                                'note' => "Đã thanh toán đơn hàng",
                                'author' => 1,
                                'customer_cant_see' => true
                            ]
                        );
                    }
                }
            }


            if ($_GET['vnp_ResponseCode'] == '00') {


                if (!empty($orderExists)) {
                    $orderExists->update(
                        [
                            "payment_status" => 2,
                        ]
                    );
                }

                return response()->view('success_paid');
            } else {
                echo "Thanh toán không thành công xin thử lại";
            }
        } else {
            echo "Giao dịch không thành công";
        }
    }
}
