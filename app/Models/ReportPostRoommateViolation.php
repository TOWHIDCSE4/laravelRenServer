<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPostRoommateViolation extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'reason'
        ]
    ];

    protected $appends = [
        'user',
        'mo_post_find_motel',
    ];


    public function getUserAttribute()
    {
        $userExist = User::where('id', $this->user_id)->first();
        if ($userExist != null) {
            return $userExist;
        }
        return null;
    }
    public function getMoPostFindMotelAttribute()
    {
        $postExist = MoPostRoommate::where('id', $this->mo_post_roommate_id)->first();
        if ($postExist != null) {
            return $postExist;
        }
        return null;
    }
}
