<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Helper\AccountRankDefineCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Place;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

class MoPost extends BaseModel
{
    use HasFactory;
    // use Searchable;
    use SearchableTrait;
    protected $guarded = [];

    // protected $with = ['motel'];
    // protected $hidden = ['images, images'];
    // protected $searchable = [
    //     'columns' => [
    //         // 'mo_posts.province_name' => 5,
    //         // 'mo_posts.district_name' => 5,
    //         'mo_posts.title',
    //         // 'mo_posts.motel_name' => 6,
    //         // 'mo_posts.phone_number' => 5,
    //     ],
    // ];

    protected $searchable = [
        'columns' => [
            'mo_posts.province_name' => 5,
            'mo_posts.district_name' => 5,
            'mo_posts.wards_name' => 2,
            'mo_posts.title' => 10,
            'mo_posts.motel_name' => 5,
            'mo_posts.phone_number' => 1,
        ],
    ];

    protected $appends = [
        'host',
        'is_favorite',
        // 'total_post',
        'total_views',
        'host_rank',
        // 'config_commission',
        'motel'
    ];

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
        "available_motel" => 'boolean',
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
        "admin_confirm_commission" => 'boolean',
    ];

    public function getMoneyCommissionUserAttribute($value)
    {
        if (request('user') != null && (request('user')->account_rank == AccountRankDefineCode::LOYAL || request('user')->is_admin == true)) {
            return $value;
        }
        return null;
    }

    public function getMotelAttribute()
    {
        // return Motel::join('mo_post_multiple_motels', 'motels.id', '=', 'mo_post_multiple_motels.motel_id')
        //     ->join('contracts', 'motels.id', '=', 'contracts.motel_id')
        //     ->where([
        //         ['mo_post_multiple_motels.mo_post_id', $this->id],
        //         ['contracts.status', '<>', StatusContractDefineCode::COMPLETED]
        //     ])
        //     ->select('motels.*')
        //     ->distinct()
        //     ->get();

        $motelHasContract = DB::table('mo_post_multiple_motels')
            ->join('contracts', 'contracts.motel_id', '=', 'mo_post_multiple_motels.motel_id')
            ->where([
                ['mo_post_multiple_motels.mo_post_id', $this->id],
                ['contracts.status', StatusContractDefineCode::COMPLETED]
            ])
            ->distinct()
            ->pluck('contracts.motel_id');
        return Motel::join('mo_post_multiple_motels', 'motels.id', '=', 'mo_post_multiple_motels.motel_id')
            ->whereNotIn('motels.id', $motelHasContract)
            ->where([
                ['mo_post_multiple_motels.mo_post_id', $this->id],
            ])
            ->select('motels.*')
            ->distinct()
            ->get();
    }

    public function getMoneyCommissionAdminAttribute($value)
    {
        if (request('user') != null && (request('user')->is_admin == true || request('user')->is_host == true)) {
            return $value;
        }
        return null;
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getHostRankAttribute()
    {
        if (isset($this->user_id)) {
            $userExists = DB::table('users')->where('id', $this->user_id)->first();
            if (isset($userExists->host_rank)) {
                return DB::table('users')->where('id', $this->user_id)->first()->host_rank;
            }
            return 0;
        }
        return 0;
    }

    // public function getConfigCommissionAttribute()
    // {
    //     if (request('user') != null) {
    //         if (request('user')->account_rank != AccountRankDefineCode::NORMAL) {
    //             $configCommission = ConfigCommission::where('motel_id', $this->id)->first();
    //             if ($configCommission != null) {
    //                 return  $configCommission;
    //             }
    //         }
    //     }

    //     return null;
    // }

    public function getHostAttribute()
    {
        $userExist = User::where('id', $this->user_id)->first();

        if ($userExist != null) {
            return $userExist;
        }
        return $userExist;
    }

    public function getFurnitureAttribute($value)
    {
        return json_decode($value);
    }

    public function getMoServicesAttribute($value)
    {
        return json_decode($value) ?? [];
    }

    public function getTotalViewsAttribute()
    {
        return DB::table('viewer_posts')->where('mo_post_id', $this->id)->count();
    }

    // public function getMoServicesAttribute()
    // {
    //     return DB::table('mo_services')
    //         ->join('motels', 'mo_services.motel_id', 'motels.id')
    //         ->where([
    //             ['motels.user_id', $this->user_id],
    //             ['mo_services.motel_id', $this->motel_id]
    //         ])
    //         ->select('mo_services.*')
    //         ->get();
    // }

    public function getIsFavoriteAttribute()
    {
        if (request('user') != null) {
            if (DB::table('mo_post_favorites')->where([
                ['user_id', request('user')->id],
                ['mo_post_id', $this->id],
            ])->exists()) {
                return true;
            }
        }

        return false;
    }

    public function getCustomerAddressAttribute()
    {
        return [
            "name" => $this->customer_name,
            "address_detail" => $this->customer_address_detail,
            "country" => $this->customer_country,
            "province" => $this->customer_province,
            "district" => $this->customer_district,
            "wards" => $this->customer_wards,
            "village" => $this->customer_village,
            "postcode" => $this->customer_postcode,
            "email" => $this->customer_email,
            "phone" => $this->customer_phone,
            "province_name" => Place::getNameProvince($this->customer_province),
            "district_name" => Place::getNameDistrict($this->customer_district),
            "wards_name" => Place::getNameWards($this->customer_wards),
        ];
    }
}
