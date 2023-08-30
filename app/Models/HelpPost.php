<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpPost extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_show' => 'boolean'
    ];
}
