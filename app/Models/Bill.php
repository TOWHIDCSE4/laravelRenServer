<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use AjCastro\Searchable\Searchable;
use App\Helper\StatusBillDefineCode;
use App\Models\Base\BaseModel;

class Bill extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];
    protected $hidden = ['is_init'];

    protected $casts = [
        'has_use_deposit' => 'boolean'
    ];

    protected $searchable = [
        'columns' => [
            'bills.content',
            'motels.motel_name'
        ],
        'join' => [
            'contracts' => ['contracts', 'bills.contract_id', '=', 'contracts.id'],
            'motels' => ['contracts.motel_id', 'motels.id']
        ]
    ];

    protected $appends = [
        'motel',
        'service_close',
        'bill_status',
        // 'contract'
    ];

    public function getMotelAttribute()
    {
        $motel = Motel::join('contracts', 'motels.id', '=', 'contracts.motel_id')
            ->join('bills', 'contracts.id', '=', 'bills.contract_id')
            ->where([
                ['contracts.id', $this->contract_id],
                ['bills.contract_id', $this->contract_id]
            ])
            ->select('motels.*')
            ->first();

        // $motel = $motel != null ? $motel->toArray() : (object)[];
        if ($motel != null) {
            $motel = $motel->toArray();
            unset($motel['mo_services']);
        } else {
            $motel = (object)[];
        }

        return $motel;
    }

    public function getServiceCloseAttribute()
    {
        return ServiceClose::where('id', $this->service_close_id)->first();
    }

    // public function getContractAttribute()
    // {
    //     DB::table('contracts')->where('id', $this->contract_id)->select()->first();
    // }

    public function getHostAttribute()
    {
        $userExist = User::join('contracts', 'users.id', '=', 'contracts.user_id')
            ->where('contracts.id', $this->contract_id)
            ->select('users.*')
            ->first();

        if ($userExist != null) {
            return $userExist;
        }
        return $userExist;
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getBillLogAttribute($value)
    {
        return json_decode($value);
    }

    public function getBillStatusAttribute()
    {
        return StatusBillDefineCode::getStatusBillCode($this->status, false);
    }
}
