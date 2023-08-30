<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use AjCastro\Searchable\Searchable;
use App\Helper\AccountRankDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\DB;

class Motel extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    // protected $hidden = ['created_at', 'updated_at'];

    protected $searchable = [
        'columns' => [
            'province_name',
            'district_name',
            'title',
            'address_detail',
            'motels.motel_name',
        ],
    ];

    protected $casts = [
        "has_park" => 'boolean',
        "has_wifi" => 'boolean',
        "has_wc" => 'boolean',
        "is_room_hidden" => 'boolean',
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

    protected $with = ['moServices'];

    protected $appends = ['total_favorite', 'config_commission', 'tower_name', 'is_support_manage_motel'];

    public function moServices()
    {
        return $this->hasMany('App\Models\MoService');
    }

    public function getMoServiceAttribute()
    {
        return DB::table('mo_services')->where('id', $this->motel_id)->first();
    }

    public function getFurnitureAttribute($value)
    {
        return json_decode($value);
    }

    public function getHostAttribute()
    {
        $userExist = User::where('id', $this->user_id)->first();

        if ($userExist != null) {
            return $userExist;
        }
        return $userExist;
    }

    public function getTotalFavoriteAttribute()
    {
        $amountFavorites = MotelFavorite::where('motel_id', $this->id)
            ->count();
        return $amountFavorites;
    }

    public function getTowerNameAttribute()
    {
        $tower = Tower::where('id', $this->tower_id)
            ->first();
        if ($tower) {
            return $tower->tower_name;
        }
        return null;
    }

    public function getConfigCommissionAttribute()
    {
        if (request('user') != null) {
            if (request('user')->account_rank != AccountRankDefineCode::NORMAL) {
                $configCommission = ConfigCommission::where('motel_id', $this->id)->first();
                if ($configCommission != null) {
                    return  $configCommission;
                }
            }
        }

        return null;
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    function getIsSupportManageMotelAttribute()
    {
        if (request('user') != null) {
            if (request('user')->id == $this->user_id) {
                return true;
            }

            return DB::table('connect_manage_motels')
                ->join('supporter_manage_towers', 'connect_manage_motels.supporter_manage_tower_id', '=', 'supporter_manage_towers.id')
                ->where('supporter_manage_towers.supporter_id', request('user')->id)
                ->where('connect_manage_motels.motel_id', $this->id)
                ->exists();
        }
        return true;
    }

    // query supporter motels
    const MY_MOTELS = 0;
    const MY_MOTELS_AND_MOTEL_SUPPORTER = 1;
    const MY_MOTELS_SUPPORTER = 2;
}
