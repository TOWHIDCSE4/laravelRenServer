<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationMotel extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'user',
        'host',
        'mo_post',
    ];

    public function getUserAttribute()
    {
        $userExist = User::where('id', $this->user_id)->first();
        if ($userExist != null) {
            return $userExist;
        }
        return null;
    }
    public function getHostAttribute()
    {
        $userExist = User::where('id', $this->host_id)->first();
        if ($userExist != null) {
            return $userExist;
        }
        return null;
    }
    public function getMoPostAttribute()
    {
        $userExist = MoPost::where('id', $this->mo_post_id)->first();
        if ($userExist != null) {
            return $userExist;
        }
        return null;
    }
}
