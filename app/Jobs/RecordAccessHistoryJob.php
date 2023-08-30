<?php

namespace App\Jobs;

use App\Helper\Data\Post\DataPostExample;
use App\Models\AccessHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordAccessHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requsest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requsest)
    {
        $this->requsest = $requsest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        AccessHistory::create([
            "user_id" => $this->request->user != null ?  $this->request->user->id : null,
            "staff_id" => $this->request->staff != null ?  $this->request->staff->id : null,
            "store_id" =>  $this->request->store != null ?  $this->request->store->id : null,
            "link" =>  $this->request->fullUrl(),
            "user_agent" =>  $this->request->header('User-Agent'),
            "ip" =>   $this->request->ip(),
        ]);
        echo 'ok';
    }
}
