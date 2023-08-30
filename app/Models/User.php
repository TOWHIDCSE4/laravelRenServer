<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use AjCastro\Searchable\Searchable;
use App\Helper\AccountRankDefineCode;
use App\Helper\HostRankDefineCode;
use App\Helper\StatusMoPostDefineCode;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use Searchable;

    protected $searchable = [
        'columns' => [
            'users.name',
            'users.phone_number',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "phone_number",
        "email",
        "password",
        'area_code',
        "name",
        "date_of_birth",
        "avatar_image",
        "sex",
        "is_vip",
        'is_host',
        'is_admin',
        'host_rank',
        'account_rank',
        'social_id',
        'has_post',
        'status',
        'self_referral_code',
        'is_choosed_decent',
        'has_referral_code',
        'referral_code',
        'cmnd_number',
        'cmnd_front_image_url',
        'cmnd_back_image_url',
        'bank_account_number',
        'bank_account_name',
        'bank_name'

    ];

    protected $appends = ['host_rank_name', 'account_rank_name', 'total_post', 'system_permission', 'balance_account'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_vip' => 'boolean',
        'is_host' => 'boolean',
        'is_admin' => 'boolean',
        'is_authorized' => 'boolean',
        'has_post' => 'boolean',
        'has_referral_code' => 'boolean',
        'is_choosed_decent' => 'boolean'
    ];

    // public function getIsHostAttribute($value)
    // {
    //     if ($value) {
    //         return true;
    //     } else if (!$value) {
    //         return false;
    //     } else {
    //         return null;
    //     }
    // }

    public function getTotalPostAttribute()
    {
        return DB::table('mo_posts')->where([['user_id', $this->id], ['status', StatusMoPostDefineCode::COMPLETED]])->count();
    }

    public function getSystemPermissionAttribute()
    {
        $system_permission = SystemPermission::join('user_permissions', 'system_permissions.id', '=', 'user_permissions.system_permission_id')
            ->where('user_permissions.user_id', $this->id)
            ->select('system_permissions.*')
            ->first();

        return $system_permission;
    }

    // public function getHasReferralCodeAttribute()
    // {
    //     $referralCodeExist = DB::table('collaborator_refer_motels')->where('user_id', $this->id)->exists();
    //     if ($referralCodeExist) {
    //         return true;
    //     }

    //     return false;
    // }

    public function getHostRankNameAttribute()
    {
        return HostRankDefineCode::getHostRankCode($this->host_rank ?? 0, true);
    }

    public function getAccountRankNameAttribute()
    {
        return AccountRankDefineCode::getAccountRankCode($this->account_rank ?? 0, true);
    }

    public function getBalanceAccountAttribute()
    {
        $eWalletCollaborators = DB::table('e_wallet_collaborators')
            ->where('e_wallet_collaborators.user_id', $this->id)
            ->first();
        if ($eWalletCollaborators != null) {
            return $eWalletCollaborators->account_balance;
        }
        return null;
    }
}
