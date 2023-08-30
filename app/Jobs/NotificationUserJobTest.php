<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Helper\TypeFCM;
use App\Models\NotificationUser;
use App\Models\UserDeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationUserJobTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $user_id;
    protected $content;
    protected $title;
    protected $type;
    protected $role;
    protected $references_value;
    protected $deviceTokens;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $user_id,
        $title,
        $content,
        $type,
        $role,
        $references_value,
        $deviceTokens
    ) {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->role = $role;
        $this->references_value = $references_value;
        $this->deviceTokens = $deviceTokens;
    }


    /*
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = [
            'body' => $this->content,
            'title' =>  $this->title,
            'type' => $this->type,
            'references_value' => $this->references_value,
        ];


        $splitToken = array_chunk($this->deviceTokens, 500);

        foreach ($splitToken as $listToken) {
            $this->subscribeTopic($listToken, $this->type);
            $this->sendNotification($data, $this->type);
            $this->unsubscribeTopic($listToken, $this->type);
        }
    }


    public function sendNotification($data, $topicName = null)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            'to' => '/topics/' . $topicName,
            'notification' => [
                'body' => $data['body'] ?? 'Something',
                'title' => $data['tile'] ?? 'Something',
                'image' => $data['image'] ?? null,
                'sound' => 'saha',
                'priority' => 'high',
                'android_channel_id' => 'noti_push_app_1',
                "content_available" => true,
            ],
            "webpush" =>  [
                "headers" => [
                    "Urgency" => "high"
                ]
            ],
            "android" =>  [
                "priority" => "high"
            ],
            "priority" =>  'high',
            "sound" => "alarm",
            'data' => [
                'references_value' => $data['references_value'] ?? null,
                'title' => $data['title'] ?? null,
                'type' => $data['type'] ?? null,
                'url' => $data['url'] ?? null,
                'redirect_to' => $data['redirect_to'] ?? null,
                'type' => $data['type'] ?? null,
                "sound" => "alarm",
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                        'sound' => 'saha',
                        'badge' => 1,
                    ],
                ],
                'fcm_options' => [
                    'image' => $data['image'] ?? null,
                ],
            ],
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function subscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function unsubscribeTopic($deviceTokens, $topicName = null)
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $url
     * @param array $dataPost
     * @param string $method
     * @return bool
     * @throws GuzzleException
     */
    private function execute($url, $dataPost = [], $method = 'POST')
    {
        $result = false;
        try {
            $client = new Client();
            $result = $client->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . "AAAACLGn77M:APA91bFub_lCosMGuMGpCEnfFMCwqI0sflPJBj1k5Oo-p3vsV0ZbXIh3cZVx-agSe0Fr6PIIuq9lIh9eWeLYI1WgVGOTFosumGcvvRCMlVrQ03Roa4P8HbshCYkO3EmbOBr2S20FriDl",
                ],
                'json' => $dataPost,
                'timeout' => 300,
            ]);

            $result = $result->getStatusCode() == Response::HTTP_OK;
        } catch (Exception $e) {
            Log::debug($e);
        }

        return $result;
    }
}
