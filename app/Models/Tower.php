<?php

namespace App\Models;

use App\Helper\StatusMotelDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

class Tower extends BaseModel
{
    use HasFactory;
    use SearchableTrait;

    protected $guarded = [];
    protected $searchable = [];

    protected $appends = [
        'tower_service',
        'motel',
        'is_support_manage_tower',
        'total_motel',
        'total_empty_motel'
    ];
    // protected $with = ['motel'];

    protected $casts = [
        "has_park" => 'boolean',
        "has_wifi" => 'boolean',
        "has_wc" => 'boolean',
        "has_window" => 'boolean',
        "has_security" => 'boolean',
        "has_free_move" => 'boolean',
        "has_own_owner" => 'boolean',
        "has_air_conditioner" => 'boolean',
        "has_water_heater" => 'boolean',
        "has_kitchen" => 'boolean',
        "has_fridge" => 'boolean',
        "has_washing_machine" => 'boolean',
        "has_mezzanine" => 'boolean',
        "has_bed" => 'boolean',
        "has_wardrobe" => 'boolean',
        "has_tivi" => 'boolean',
        "has_pet" => 'boolean',
        "has_balcony" => 'boolean',
        "admin_verified" => 'boolean',
        "has_post" => 'boolean',
        "has_finger_print"  => 'boolean',
        "has_kitchen_stuff"  => 'boolean',
        "has_table"  => 'boolean',
        "has_picture"  => 'boolean',
        "has_decorative_lights" => 'boolean',
        "has_tree"  => 'boolean',
        "has_pillow"  => 'boolean',
        "has_mattress"  => 'boolean',
        "has_shoes_rasks"  => 'boolean',
        "has_curtain"  => 'boolean',
        "has_mirror"  => 'boolean',
        "has_sofa"  => 'boolean',
        "has_ceiling_fans" => 'boolean',
        "has_contract" => 'boolean',
    ];

    public function moServices()
    {
        return $this->hasMany('App\Models\Service');
    }

    public function getMotelAttribute()
    {
        // return $this->hasMany('App\Models\Motel', 'id', 'tower_id')->select('id', 'motel_name', 'number_floor', 'area', 'money');
        return Motel::where([
            ['tower_id', $this->id],
            ['user_id', $this->user_id]
        ])
            ->when(request('supporter_manage_tower_id') != null, function ($query) {
                $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->where('connect_manage_motels.supporter_manage_tower_id', request('supporter_manage_tower_id'))
                    ->pluck('motels.id');
                $exceptMotelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->whereNotIn('motels.id', $motelIds)
                    ->where('connect_manage_motels.supporter_manage_tower_id', request('supporter_manage_tower_id'))
                    ->pluck('motels.id');
                $query->whereIn('motels.id', $motelIds);
                $query->whereNotIn('motels.id', $exceptMotelIds);
            })
            ->select('id', 'user_id', 'motel_name', 'number_floor', 'area', 'money',  'deposit', 'capacity', 'status')
            ->get();
    }

    public function getTowerServiceAttribute()
    {
        return DB::table('tower_services')->where('tower_id', $this->id)->get();
    }

    public function getFurnitureAttribute($value)
    {
        return json_decode($value);
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    function getIsSupportManageTowerAttribute()
    {
        return $this->user_id != request('user')->id ? true : false;
    }

    // Tổng phòng trống
    function getTotalEmptyMotelAttribute()
    {
        $motel = 0;
        if (request('supporter_manage_tower_id') != null) {
            $motel = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                ->where([
                    ['motels.status', StatusMotelDefineCode::MOTEL_EMPTY],
                    ['motels.tower_id', $this->id],
                ])
                ->where('connect_manage_motels.supporter_manage_tower_id', request('supporter_manage_tower_id'))
                ->count();
        }
        return $motel;
    }

    // Tổng phòng
    function getTotalMotelAttribute()
    {
        $motel = 0;
        if (request('supporter_manage_tower_id') != null) {
            $motel = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                ->where([
                    ['motels.tower_id', $this->id],
                ])
                ->where('connect_manage_motels.supporter_manage_tower_id', request('supporter_manage_tower_id'))
                ->count();
        }
        return $motel;
    }
}
