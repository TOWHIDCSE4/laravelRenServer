<?php

namespace App\Mail;

use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Models\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOTPCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $store;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $store)
    {
        //
        $this->otp = $otp;
        $this->store = $store;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $store_name = $this->store->name;

        $data = GeneralSettingController::defaultOfStoreID( $this->store->id);
        $from_email = $data->email_send_to_customer ?? "noreply@ikitech.vn";

        return $this->view('emailotp_customer', [
            'VERIFICATION_CODE' => $this->otp,
            'store' =>  $this->store, 'slot' => 'sds'
        ])
            ->from( $from_email, $store_name)
            ->subject("$this->otp là mã xác nhận $store_name của bạn");
    }
}
