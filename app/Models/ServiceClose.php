<?php

namespace App\Models;

use App\Helper\ServiceUnitDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceClose extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at'];
    protected $with = ['ServiceCloseItems'];

    public function ServiceCloseItems()
    {
        if (str_contains(request(), 'bills_gen')) {
            return $this->hasMany('App\Models\ServiceCloseChild')->where([['type_unit', '<>', ServiceUnitDefineCode::ANOTHER], ['type_unit', '<>', ServiceUnitDefineCode::NOT_CAL]]);
        } else {
            return $this->hasMany('App\Models\ServiceCloseChild');
        }
    }
}
