<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewerPost extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'mo_post'
    ];

    public function getMoPostAttribute()
    {
        return MoPost::where('id', $this->mo_post_id)->first();
    }
}
