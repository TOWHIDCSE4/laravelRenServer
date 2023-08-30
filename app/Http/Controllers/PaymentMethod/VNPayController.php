<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\StatusPaymentHistory;
use App\Models\UserDeviceToken;
use App\Traits\VNPay;
use Exception;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán
 */
class VNPayController extends Controller
{
    use VNPay;

    public function index(Request $request)
    {
        return $this->pay($request);
    }

    public function check_status()
    {
        $inputData = array();
        $returnData = array();
        $data = $_REQUEST;
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_TmnCode = "ZE0FCHUA"; //Mã website tại VNPAY 
        $vnp_HashSecret = "GTSRPSEAUHEJTJMFZWYVCNHYTHFFIGAS"; //Chuỗi bí mật

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        $secureHash = hash('sha256', $vnp_HashSecret . $hashData);
        $Status = 0;
        $orderId = $inputData['vnp_TxnRef'];

        try {
            //Check Orderid    
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId            
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);   
                $order = NULL;
                if ($order != NULL) {
                    if ($order["Status"] != NULL && $order["Status"] == 0) {
                        if ($inputData['vnp_ResponseCode'] == '00') {
                            $Status = 1;
                        } else {
                            $Status = 2;
                        }
                        //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                        //
                        //
                        //
                        //Trả kết quả về cho VNPAY: Website TMĐT ghi nhận yêu cầu thành công                
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    } else {
                        $returnData['RspCode'] = '02';
                        $returnData['Message'] = 'Order already confirmed';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chu ky khong hop le';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
        }
        //Trả lại VNPAY theo định dạng JSON
        echo json_encode($returnData);
    }


    public function create(Request $request)
    {
        $host = $request->getSchemeAndHttpHost();

        $order_code = $request->order->order_code;

        session(['cost_id' => $request->id]);
        session(['url_prev' => url()->previous()]);
        $vnp_TmnCode = "ZE0FCHUA"; //Mã website tại VNPAY 
        $vnp_HashSecret = "GTSRPSEAUHEJTJMFZWYVCNHYTHFFIGAS"; //Chuỗi bí mật
        $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = $host . "/api/customer/sy/purchase/return/vn_pay";
        $vnp_TxnRef =  $order_code; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = "Thanh toán hóa đơn .$order_code";
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->order->total_final * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
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



        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {

            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }



        return redirect($vnp_Url);
    }

    public function return(Request $request)
    {
        $vnp_HashSecret = "GTSRPSEAUHEJTJMFZWYVCNHYTHFFIGAS"; //Chuỗi bí mật

        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            $inputData[$key] = $value;
        }
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }

        $secureHash = hash('sha256', $vnp_HashSecret . $hashData);

        if ($secureHash == $vnp_SecureHash) {

            $historyExists = StatusPaymentHistory::where(
                'order_code',
                $inputData['vnp_TxnRef']
            )->where('pay_date', $inputData['vnp_PayDate'])->first();

            $orderExists = Order::where(
                'order_code',
                $inputData['vnp_TxnRef']
            )->first();

            if (empty($historyExists)) {
                StatusPaymentHistory::create(
                    [
                        "order_code" => $inputData['vnp_TxnRef'],
                        "transaction_no" => $inputData['vnp_TransactionNo'],
                        "amount" => ($inputData['vnp_Amount'] != null && $inputData['vnp_Amount'] > 0) ? $inputData['vnp_Amount'] / 100 : 0,
                        "bank_code" => $inputData['vnp_BankCode'],
                        "card_type" => $inputData['vnp_CardType'],
                        "order_info" => $inputData['vnp_OrderInfo'],
                        "pay_date" => $inputData['vnp_PayDate'],
                        "response_code" => $inputData['vnp_ResponseCode'],
                        "key_code_customer" => $inputData['vnp_TmnCode'],
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
            echo "Chu ky khong hop le";
        }
    }
}
