<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class SessionUser extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['user'];

    public function getUserAttribute()
    {
        return User::where('id', $this->user_id)->first();
    }
}
