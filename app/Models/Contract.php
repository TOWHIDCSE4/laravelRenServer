<?php

namespace App\Models;

use Nicolaslopezj\Searchable\SearchableTrait;
use App\Helper\TypeFCM;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Contract extends BaseModel
{
    use HasFactory;
    use SearchableTrait;


    protected $guarded = [];
    // protected $with = ['user'];
    protected $casts = [
        'is_represent' => 'boolean',
    ];

    protected $appends = [
        'motel',
        'list_renter',
        'host',
        'tower'
    ];

    protected $searchable = [
        // 'columns' => [
        //     // 'users.phone_number' => 4,
        // 'motels.motel_name' => 3,
        //     // 'renters.phone_number' => 3,
        //     // 'renters.name' => 2
        // ],
        // 'joins' => [
        //     // 'users' => ['contracts.user_id', 'users.id'],
        //     // 'motels' => ['contracts.motel_id', 'motels.id'],
        //     // 'user_contracts' => ['contracts.id', 'user_contracts.contract_id'],
        //     // 'renters' => ['user_contracts.renter_phone_number', 'renters.phone_number'],
        // ]
    ];

    public function motels()
    {
        return $this->hasMany('Motels');
    }

    public function User()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getImagesDepositAttribute($value)
    {
        return json_decode($value);
    }

    public function getMoServicesAttribute($value)
    {
        return json_decode($value);
    }
    public function getFurnitureAttribute($value)
    {
        return json_decode($value);
    }
    // public function getListRenterAttribute($value)
    // {
    //     return json_decode($value);
    // }

    public function getRenterOriginAttribute($value)
    {
        return json_decode($value);
    }

    public function getMotelAttribute()
    {
        return Motel::where('id', $this->motel_id)->first();
    }

    public function getTowerAttribute()
    {
        return Tower::where('id', $this->tower_id)->first();
    }

    public function getHostAttribute()
    {
        $hostExist = User::where('id', $this->user_id)->first();
        if ($hostExist != null) {
            return $hostExist;
        }
        return null;
    }

    public function getListRenterAttribute()
    {
        $listRenter = DB::table('renters')
            ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
            ->where([
                ['user_contracts.contract_id', $this->id],
                ['user_contracts.user_id', $this->user_id],
                ['renters.user_id', $this->user_id]
            ])
            ->select('renters.*', 'is_represent')
            ->get()->toArray();

        foreach ($listRenter as $renter) {
            $renter->is_represent = $renter->is_represent == 1 ? true : false;
            $renter->has_contract = $renter->has_contract == 1 ? true : false;
        }

        return $listRenter;
    }

    public function getTypeNameAttribute()
    {
        if ($this->type == TypeFCM::NEW_CONTRACT) {
            return 'Bạn có một hợp đồng mới ' . $this->references_value;
        }
        return '';
    }
}
