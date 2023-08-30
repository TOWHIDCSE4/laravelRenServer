<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollaboratorReferMotel extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $appends = [
        'host',
        'mo_post',
        'user_referral',
        'user',
    ];

    protected $searchable = [
        'columns' => []
    ];

    public function getHostAttribute()
    {
        return User::join('mo_posts', 'users.id', '=', 'mo_posts.user_id')->where('mo_posts.motel_id', $this->motel_id)->select('users.*')->first();
    }
    public function getUserReferralAttribute()
    {
        if ($this->user_referral_id != null) {
            return User::where('id', $this->user_referral_id)->select('users.*')->first();
        }
        return null;
    }
    public function getUserAttribute()
    {
        if ($this->user_id != null) {
            return User::where('id', $this->user_id)->select('users.*')->first();
        }
        return null;
    }

    public function getImagesHostPaidAttribute($value)
    {
        return json_decode($value);
    }

    public function getMoPostAttribute()
    {
        return MoPost::where('mo_posts.motel_id', $this->motel_id)->first();
    }
}
