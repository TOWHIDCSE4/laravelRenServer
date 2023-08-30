<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmailOrderCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emails;
    public $store;
    public $order_code;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $emails,
        $store,
        $order_code
    ) {


        $this->emails = $emails;
        $this->store = $store;
        $this->order_code = $order_code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        foreach (($this->emails ?? [])  as $email) {
            Mail::to([$email])
                ->send(new \App\Mail\SendMailOrderCustomer($this->store,  $this->order_code));
        }
    }
}
