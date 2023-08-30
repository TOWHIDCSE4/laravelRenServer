<?php

namespace App\Models;

use App\Helper\StatusContractDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

class PersonChats extends BaseModel
{
    use HasFactory;
    use SearchableTrait;
    protected $guarded = [];
    // protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['to_user', 'is_my_host', 'is_my_renter'];
    protected $casts = [
        'seen' => 'boolean',
        'is_helper' => 'boolean',
    ];

    protected $searchable = [
        'columns' => [
            'users.phone_number' => 10,
            'users.name' => 10,
        ],
        'joins' => [
            'users' => ['person_chats.to_user_id', 'users.id'],
        ]
    ];

    public function getToUserAttribute()
    {
        return User::where('id', $this->to_user_id)->select('id', 'name', 'avatar_image', 'is_host', 'is_admin')->first();
    }

    public function getUserIdAttribute()
    {
        if (request('user') != null) {
            return request('user')->id;
        }
        return null;
    }

    public function getIsMyRenterAttribute()
    {
        $myRenter = PersonChats::join('users', 'person_chats.to_user_id', '=', 'users.id')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['person_chats.to_user_id', $this->to_user_id],
                ['contracts.status', StatusContractDefineCode::COMPLETED],
            ])
            ->exists();

        return $myRenter;
    }

    public function getLastListMoPostIdAttribute($value)
    {
        return json_decode($value ?? '[]');
    }

    public function getIsMyHostAttribute()
    {
        $myHost = PersonChats::join('users', 'person_chats.to_user_id', '=', 'users.id')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['person_chats.to_user_id', $this->user_id],
                ['contracts.status', StatusContractDefineCode::COMPLETED],
            ])
            ->exists();
        return $myHost;
    }

    public function getLastMessAttribute($value)
    {
        if (request('user') != null && $this->is_helper == true) {
            return UToUMessages::where([
                ['user_id', request('user')->id],
                ['vs_user_id', $this->to_user_id],
            ])
                ->orderByDesc('created_at')
                ->value('content');
        }

        return $value;
    }
    public function getUpdatedAtAttribute($value)
    {
        if (request('user') != null && $this->is_helper == true) {
            $lasted_at = UToUMessages::where([
                ['user_id', request('user')->id],
                ['vs_user_id', $this->to_user_id],
            ])
                ->orderByDesc('created_at')
                ->value('created_at');
            return $lasted_at != null ? $lasted_at : $this->lasted_at;
        }

        return $this->lasted_at;
    }
}
