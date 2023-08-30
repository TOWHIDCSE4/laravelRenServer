<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostCategoryHelpPost extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];
    protected $hidden = ['help_post_id', 'category_help_post_id'];
    protected $appends = ['HelpPost', 'CategoryHelpPost'];

    public function GetHelpPostAttribute()
    {
        $helpPost = DB::table('help_posts')->where('id', $this->help_post_id)->first();
        if ($helpPost != null) {
            $helpPost->is_show = $helpPost->is_show == 1 ? true : false;
        }
        return $helpPost;
    }

    public function GetCategoryHelpPostAttribute()
    {
        $categoryHelpPost = DB::table('category_help_posts')->where('id', $this->category_help_post_id)->first();
        if ($categoryHelpPost != null) {
            $categoryHelpPost->is_show = $categoryHelpPost->is_show == 1 ? true : false;
        }
        return $categoryHelpPost;
    }
}
