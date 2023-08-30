<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Helper\StatusMoPostDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UToUMessages extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];
    protected $hidden = ['list_mo_post_id'];

    protected $appends = ['mo_posts'];

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getListMoPostIdAttribute($value)
    {
        return json_decode($value ?? '[]');
    }

    public function getIsSenderAttribute($value)
    {
        return $value ? true : false;
    }

    public function getMoPostsAttribute()
    {
        if ($this->list_mo_post_id != null) {
            return MoPost::where('status', StatusMoPostDefineCode::COMPLETED)
                ->whereIn('id', $this->list_mo_post_id)
                ->select(
                    'id',
                    'motel_id',
                    'tower_id',
                    'tower_name',
                    'phone_number',
                    'title',
                    'motel_name',
                    'area',
                    'capacity',
                    'sex',
                    'money',
                    'province_name',
                    'district_name',
                    'wards_name',
                    'address_detail',
                    'images',
                    'admin_verified'
                )
                ->get();
        }
        return [];
    }
}
