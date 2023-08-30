<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeviceToken extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
