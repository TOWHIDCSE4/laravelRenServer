<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class CategoryServiceSell extends BaseModel
{
    use HasFactory;
    use SearchableTrait;
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected $searchable = [
        'columns' => [
            'category_service_sells.name' => 10,
        ],
    ];
}
