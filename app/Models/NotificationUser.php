<?php

namespace App\Models;

use App\Helper\NotiUserDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class NotificationUser extends BaseModel
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'unread' => 'boolean',
    ];

    public function getUnreadAttribute($value)
    {
        if ($this->role != NotiUserDefineCode::ALL_USER_IN_SYSTEM) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        if (request('user') != null && $this->role == NotiUserDefineCode::ALL_USER_IN_SYSTEM) {
            $NotiAdminExist = DB::table('read_noti_admins')->where([
                ['user_id', request('user')->id],
                ['noti_user_id', $this->id]
            ])->exists();
            return filter_var($NotiAdminExist != null ? !$NotiAdminExist : !$NotiAdminExist, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }
}
