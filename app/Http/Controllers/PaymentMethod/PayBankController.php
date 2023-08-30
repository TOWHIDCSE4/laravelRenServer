<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationJob;
use App\Models\Order;
use App\Models\OrderRecord;
use App\Models\PaymentMethod;
use App\Models\StatusPaymentHistory;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;


/**
 * @group  Customer/thanh toán
 */
class PayBankController extends Controller
{

    function currency_format($number, $suffix = 'đ')
    {
        if (!empty($number)) {
            return number_format($number, 0, ',', '.') . "{$suffix}";
        }
    }

    public function create(Request $request)
    {
        $order_code = $request->order->order_code;
        $orderExists = Order::where(
            'order_code',
            $order_code
        )->first();

        if ($orderExists == null) {
            echo "Không tồn tại hóa đơn";
        } else {
            if ($orderExists->payment_status == 2) {
                return response()->view('success_paid');
            } else {

                $methodExists = PaymentMethod::where('store_id', $request->store->id)
                    ->where('method_id', 1)->first();
                $payment_guide = [];

                if ($methodExists && isset($methodExists->json_data)) {
                    $config = json_decode($methodExists->json_data);
                    $use =  $methodExists->use;
                }

                if (isset($config->payment_guide) && is_array($config->payment_guide) && count($config->payment_guide) > 0) {

                    $payment_guide = $config->payment_guide;
                }


                return response()->view(
                    'bank_pay',
                    [
                        'order' => $request->order,
                        'total_final' =>  $this->currency_format($request->order->total_final),
                        'payment_guide' => $payment_guide
                    ]
                );
            }
        }
    }
}
