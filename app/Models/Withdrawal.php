<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Withdrawal extends  BaseModel
{
    use HasFactory;
    protected $guarded = [];

    use SearchableTrait;

    protected $searchable = [
        'columns' => [],
    ];

    protected $appends = [
        'user'
    ];

    public function getUserAttribute()
    {
        return User::where('id', $this->user_id)->first();
    }
}
