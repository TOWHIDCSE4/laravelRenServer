<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Helper\TypeMoneyFromEWalletDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWalletCollaboratorHistory extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $appends = [
        'user_referred',
        'user_referral'
    ];

    protected $casts = [
        'take_out_money' => 'boolean'
    ];

    public function getUserReferredAttribute()
    {
        if ($this->type_money_from == TypeMoneyFromEWalletDefineCode::USER_REFERRED && $this->value_reference != null) {
            return User::where('id', $this->value_reference)->first();
        }

        return null;
    }

    public function getUserReferralAttribute()
    {
        if ($this->type_money_from == TypeMoneyFromEWalletDefineCode::USER_REFERRAL && $this->value_reference != null) {
            return User::where('id', $this->value_reference)->first();
        }
        return null;
    }
}
