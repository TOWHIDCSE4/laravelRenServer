<?php

namespace App\Models;

use App\Helper\ServiceUnitDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['name_type_unit'];

    public function getNameTypeUnitAttribute()
    {
        return ServiceUnitDefineCode::getServiceUnitCode($this->type_unit, false);
    }
}
