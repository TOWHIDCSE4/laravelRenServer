<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Helper\StatusContractDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Renter extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $appends = ['user', 'motel', 'bill', 'contract', 'contract_active'];

    protected $casts = [
        'has_contract' => 'boolean'
    ];

    protected $searchable = [
        'columns' => [
            'name',
            'phone_number'
        ],
    ];

    public function getUserAttribute()
    {
        return User::where('phone_number', $this->phone_number)->first();
    }

    public function getMotelAttribute()
    {
        return Motel::where('id', $this->motel_id)->first();
    }

    // public function user()
    // {
    //     return $this->hasMany(User::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getBillAttribute()
    {
        $bill = Bill::join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->where([
                ['user_contracts.renter_phone_number', $this->phone_number],
                ['is_init', false]
            ])
            ->select('bills.*')
            ->take(20)
            ->orderByDesc('bills.id')
            ->get();

        return $bill;
    }

    public function getContractActiveAttribute()
    {
        $contract = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['user_contracts.user_id', $this->user_id],
                ['user_contracts.renter_phone_number', $this->phone_number]
            ])
            ->select('contracts.*')
            ->first();

        return $contract;
    }

    public function getContractAttribute()
    {
        $contract = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
            ->where([
                ['user_contracts.user_id', $this->user_id],
                ['user_contracts.renter_phone_number', $this->phone_number]
            ])
            ->select('contracts.*')
            ->take(20)
            ->orderByDesc('contracts.id')
            ->get();

        return $contract;
    }
}
