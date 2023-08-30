<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class findFastMotel extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'province_name',
            'district_name',
            'name',
            'phone_number',
        ],
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
    ];

    protected $appends = [
        'user'
    ];

    public function getUserAttribute()
    {
        if (request('user') != null) {
            return User::where('phone_number', $this->phone_number)->first();
        } else {
            return null;
        }
    }
}
