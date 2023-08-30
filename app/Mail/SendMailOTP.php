<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp)
    {
        //
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emailotp', ['VERIFICATION_CODE' => $this->otp, 'body' => 'green', 'slot' => 'sds'])
            ->from('noreply@ikitech.vn', 'IKITECH.VN')
            // ->sender('noreply@ikitech.vn', 'IKITECH.VN')
            // ->receiver('noreply@ikitech.vn', 'IKITECH.VN')
            ->subject("$this->otp là mã xác nhận IKITECH của bạn");
    }
}
