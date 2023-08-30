<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectManageTower extends BaseModel
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    protected $guarded = [];

    protected $with = ['tower'];

    function tower()
    {
        return $this->hasOne(Tower::class, 'id', 'tower_id');
    }
    // function tower()
    // {
    //     return $this->belongsTo(Tower::class, 'tower_id');
    // }
}
