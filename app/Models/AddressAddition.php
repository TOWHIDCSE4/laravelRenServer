<?php

namespace App\Models;

use App\Helper\Place;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressAddition extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $hidden = ['updated_at'];

    protected $appends = ['province_name', 'district_name', 'ward_name'];

    function getProvinceNameAttribute()
    {
        return Place::getNameProvince($this->province);
    }
    function getDistrictNameAttribute()
    {
        return Place::getNameDistrict($this->district);
    }
    function getWardNameAttribute()
    {
        return Place::getNameWards($this->wards);
    }
}
