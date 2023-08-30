<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class ItemCartServiceSell extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $with = ['service_sell'];


    public function service_sell()
    {
        return $this->belongsto(ServiceSell::class);
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }
}
