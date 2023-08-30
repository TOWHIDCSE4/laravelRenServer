<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItemServiceSell extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['service_sell'];

    public function service_sell()
    {
        return $this->belongsto('App\Models\ServiceSell');
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }
}
