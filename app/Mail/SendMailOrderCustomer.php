<?php

namespace App\Mail;

use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Models\Order;
use App\Models\StoreAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\GeneralSetting;

class SendMailOrderCustomer extends Mailable
{
    use Queueable, SerializesModels;


    public $store;
    public $order_code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($store, $order_code)
    {
        //
        $this->store = $store;
        $this->order_code = $order_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $store_name = $this->store->name ?? "";
        $order_code = $this->order_code;

        $orderExists = Order::where('order_code', $order_code)
            ->with('line_items')
            ->first();

        $addressPickupExists = StoreAddress::where(
            'store_id',
            $this->store->id
        )->where('is_default_pickup', true)->first();

        $orderExists->customer_address =  $orderExists->getCustomerAddressAttribute();




        $data = GeneralSettingController::defaultOfStoreID( $this->store->id);
        $from_email = $data->email_send_to_customer ?? "noreply@ikitech.vn";


        return $this->view('email_order_customer', [
            'order' => $orderExists,
            'store' => $this->store,
            'addressPickupExists' =>   $addressPickupExists,
        ])
            ->from( $from_email, $store_name)
            ->subject("Thông tin đơn hàng #$order_code - $store_name");
    }
}
