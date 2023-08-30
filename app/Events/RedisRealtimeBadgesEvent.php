<?php

namespace App\Events;

use App\Http\Controllers\Api\User\BadgesController;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RedisRealtimeBadgesEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $user_id;
    public $staff_id;
    public $customer_id;
    public $store_id;
    public $data_badges;

    public function __construct($user_id, $staff_id, $customer_id)
    {

        $this->user_id = $user_id ?? null;
        $this->staff_id = $staff_id ?? null;
        $this->customer_id = $customer_id ?? null;
        $this->store_id = $store_id ?? null;

        if ($this->user_id != null) {
            $this->data_badges = BadgesController::data_badges($this->user_id, null, null);
        }

        if ($this->staff_id != null) {
            $this->data_badges = BadgesController::data_badges(null, $this->staff_id, null);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('channel-name');
        return ['chat'];
    }

    public function broadcastAs()
    {
        if ($this->user_id != null) {
            return 'badges_user';
        }

        if ($this->staff_id != null) {
            return 'badges_staff';
        }

        if ($this->customer_id != null) {
            return 'badges_customer';
        }
    }
}
