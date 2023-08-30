<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;

class CategoryHelpPost extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $appends = ['help_posts'];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    // protected $searchable = [
    //     'columns' => [
    //         'category_help_posts.title'
    //     ],
    // ];

    public function getHelpPostsAttribute()
    {
        return DB::table('help_posts')
            ->join('post_category_help_posts', 'help_posts.id', '=', 'post_category_help_posts.help_post_id')
            ->where([['category_help_post_id', $this->id], ['help_posts.is_show', true]])->select('help_posts.*')->get();
    }
}
