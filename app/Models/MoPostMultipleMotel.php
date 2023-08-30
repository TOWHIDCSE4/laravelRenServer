<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Nicolaslopezj\Searchable\SearchableTrait;

class MoPostMultipleMotel extends BaseModel
{
    use SearchableTrait;
    protected $guarded = [];

    // protected $hidden = ['created_at', 'updated_at', 'motel_name', 'floor', 'area', 'money'];

    // public function MoPost()
    // {
    //     return $this->belongsTo(MoPost::class)
    //         ->select(['motels.id', 'motels.motel_name', 'motels.number_floor', 'motels.area', 'motels.money', 'motels.deposit', 'motels.capacity']);
    // }
}
