<?php

namespace App\Models;

use App\Helper\ServiceUnitDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCloseChild extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['name_type_unit']; // 

    public function getNameTypeUnitAttribute() // 
    {
        return ServiceUnitDefineCode::getServiceUnitCode($this->type_unit, false);
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }
}
