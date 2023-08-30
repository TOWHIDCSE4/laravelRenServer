<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminDiscoverItemUi extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'total_mo_post'
    ];

    public function discovers()
    {
        return $this->belongsTo('App\Models\AdminDiscoverUi', 'admin_discover_id', 'id')
            ->select('id', 'content', 'image', 'district', 'district_name');
    }

    public function getTotalMoPostAttribute()
    {
        return DB::table('mo_posts')->where([
            ['province', $this->province],
            ['district', $this->district]
        ])->count();
    }
}
