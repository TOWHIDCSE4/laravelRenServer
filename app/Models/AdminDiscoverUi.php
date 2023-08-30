<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminDiscoverUi extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['listDiscoverItem'];

    // protected $appends = ['list_discover_item'];

    // public function getListDiscoverItemAttribute()
    // {
    //     return AdminDiscoverItemUi::where('admin_discover_id', $this->id)
    //         ->select('id', 'content', 'image', 'district', 'district_name')
    //         ->with()
    //         ->get();
    // }

    public function listDiscoverItem()
    {
        return $this->hasMany(AdminDiscoverItemUi::class, 'admin_discover_id', 'id');
    }
}
