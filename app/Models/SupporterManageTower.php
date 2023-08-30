<?php

namespace App\Models;

use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupporterManageTower extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['total_tower_manage', 'total_motel_manage', 'total_empty_motel', 'total_contract', 'towers', 'user'];

    // protected $with = ['towers'];

    // function towers()
    // {
    //     return $this->hasMany(ConnectManageTower::class, 'supporter_manage_tower_id', 'id')
    //         ->orderByDesc('id');
    //     ->with('tower:id,user_id');
    // $this->hasManyThrough(
    //     Tower::class,
    //     ConnectManageTower::class,
    //     'supporter_manage_tower_id',
    //     'id',
    //     'id',
    //     'tower_id'
    // );
    // }

    // Lấy danh sách tòa nhà
    function getUserAttribute()
    {
        return User::where('id', $this->supporter_id)->first();
    }

    // Lấy danh sách tòa nhà
    function getTowersAttribute()
    {

        $towers = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
            ->where([
                ['connect_manage_towers.supporter_manage_tower_id', $this->id]
            ])
            ->select('towers.id', 'towers.tower_name', 'towers.user_id', 'towers.province_name', 'towers.district_name', 'towers.wards_name', 'towers.address_detail', 'towers.images', 'towers.video_link')
            ->get();
        return $towers;
    }

    // Tổng tòa nhà đang quản lý
    function getTotalTowerManageAttribute()
    {
        $tower = Tower::join('connect_manage_towers', 'towers.id', '=', 'connect_manage_towers.tower_id')
            ->where('connect_manage_towers.supporter_manage_tower_id', $this->id)
            ->count();
        return $tower;
    }

    // Tổng phòng trống đang quản lý
    function getTotalEmptyMotelAttribute()
    {
        $motel = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
            ->where([
                ['connect_manage_motels.supporter_manage_tower_id', $this->id],
                ['motels.status', StatusMotelDefineCode::MOTEL_EMPTY],
            ])
            ->count();
        return $motel;
    }

    // Tổng phòng đang quản lý
    function getTotalMotelManageAttribute()
    {
        $motel = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
            ->where([
                ['connect_manage_motels.supporter_manage_tower_id', $this->id],
            ])
            ->count();
        return $motel;
    }

    // Tổng hợp đồng đang hoạt động
    function getTotalContractAttribute()
    {
        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
            ->where([
                ['connect_manage_motels.supporter_manage_tower_id', $this->id],
                ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
                ['motels.has_contract', true],
            ])
            ->pluck('motels.id');

        $contractActive = Contract::whereIn('motel_id', $motelIds)
            ->where('status', StatusContractDefineCode::COMPLETED)
            ->count();

        return $contractActive;
    }
}
