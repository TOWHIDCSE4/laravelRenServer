<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

class PotentialUser extends  BaseModel
{
    use HasFactory;
    use SearchableTrait;

    protected $guarded = [];

    protected $casts = [
        'is_has_contract' => 'boolean'
    ];

    protected $searchable = [
        'columns' => [
            'users.phone_number' => 8,
            'users.name' => 8,
            'potential_users.title' => 10,
        ],
        'joins' => [
            'users' => ['potential_users.user_guest_id', 'users.id'],
        ],
    ];

    protected $appends = [
        'history_user_potential',
        'list_post_favorite',
        'user_guest',
        'bill',
        'contract',
    ];

    public function getListPostFavoriteAttribute()
    {
        $moPostFavorites = MoPostFavorite::where('user_id', $this->user_guest_id)
            ->select('mo_post_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        return $moPostFavorites;
    }

    public function getHistoryUserPotentialAttribute()
    {
        $historyPotential = HistoryPotentialUser::where('user_guest_id', $this->user_guest_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        return $historyPotential;
    }

    public function getUserGuestAttribute()
    {
        $userGuestExists = user::where('id', $this->user_guest_id)
            // ->select('id', 'name', 'phone_number', 'email', 'sex', 'avatar_image')
            ->first();
        return $userGuestExists;
    }

    public function getBillAttribute()
    {
        $bills = [];
        $user = DB::table('users')->where('id', $this->user_guest_id)->first();

        if ($user) {
            $bills = DB::table('bills')
                ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $user->phone_number],
                    ['is_init', false]
                ])
                ->select('bills.id', 'bills.date_payment', 'bills.total_final', 'bills.content')
                ->take(20)
                ->orderByDesc('id')
                ->get();
        }

        return $bills;
    }

    public function getContractAttribute()
    {
        $contracts = [];
        $user = DB::table('users')->where('id', $this->user_guest_id)->first();
        // $contracts = DB::table('contracts')
        //     ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
        //     // ->where('user_contracts.renter_phone_number', $request->user->phone_number)
        //     ->where('user_contracts.user_id', $this->user_guest_id)
        //     ->addSelect([
        //         'renter_phone_number' => DB::table('users')->select('phone_number')
        //             ->whereColumn('contracts.motel_id', 'users.id')
        //             ->limit(1)
        //     ])
        //     ->addSelect([
        //         'motel_name' => DB::table('motels')->select('motel_name')
        //             ->whereColumn('contracts.motel_id', 'motels.id')
        //             ->limit(1)
        //     ])
        //     ->select('contracts.id', 'contracts.rent_from', 'contracts.rent_to', 'contracts.total_final', 'contracts.content')
        //     ->get();
        if ($user) {
            $contracts = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $user->phone_number]
                ])
                ->select('contracts.*')
                ->orderByDesc('id')
                ->take(20)
                ->get();
        }

        return $contracts;
    }
}
