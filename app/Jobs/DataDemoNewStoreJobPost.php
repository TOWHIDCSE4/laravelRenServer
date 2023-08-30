<?php

namespace App\Jobs;

use App\Helper\Data\Post\DataPostExample;
use App\Models\AppTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Attribute;
use App\Models\AttributeField;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\Distribute;
use App\Models\ElementDistribute;
use App\Models\MsgCode;
use App\Models\Post;
use App\Models\PostCategoryPost;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDistribute;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\WebTheme;
use Illuminate\Support\Facades\Storage;

class DataDemoNewStoreJobPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $career;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id, $career)
    {
        $this->store_id = $store_id;
        $this->career = $career;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $career =  $this->career;
        $store_id =  $this->store_id;

        $store = Store::where('id', $store_id)->first();


        $store_id = $store->id;
        $store_name = $store->name;

        $categoryCreate = CategoryPost::create(
            [
                'image_url' => "https://i.imgur.com/0NQhkzs.jpg",
                'title' => 'Chính sách',
                'store_id' => $store_id,
                'description' => "Danh mục chính sách",
            ]
        );

        $webThemeExists = WebTheme::where(
            'store_id',
            $store_id
        )->first();

        $webThemeUpdate = [
            'store_id' => $store_id,
            'is_scroll_button' => 0,
        ];
        if ($webThemeExists !== null) {
            $webThemeExists->update(
                $webThemeUpdate
            );
        } else {
            $webThemeExists  = WebTheme::create(
                $webThemeUpdate
            );
        }

        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách hỗ trợ',
                'image_url' => 'https://i.imgur.com/oCmjI0r.jpg',
                'summary' => 'Chính sách hỗ trợ',
                'content' => DataPostExample::getSupportPolicy($store_name),
                'is_show' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeExists->update(['post_id_support_policy' => $postCreate->id]);
        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách bảo mật',
                'image_url' => 'https://i.imgur.com/NeXik0E.jpg',
                'summary' => 'Chính sách bảo mật',
                'content' => DataPostExample::getPrivacyPolicy($store_name),
                'is_show' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeExists->update(['post_id_privacy_policy' => $postCreate->id]);
        //////////////////////////////////////////////////////////////////
        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Chính sách đổi trả',
                'image_url' => 'https://i.imgur.com/7dPjFRM.jpg',
                'summary' => 'Chính sách đổi trả',
                'content' => DataPostExample::getReturnPolicy($store_name),
                'is_show' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );
        $webThemeExists->update(['post_id_return_policy' => $postCreate->id]);

        //////////////////////////////////////////////////////////////////

        $postCreate = Post::create(
            [
                'store_id' => $store_id,
                'title' => 'Điều khoản điều kiện',
                'image_url' => 'https://i.imgur.com/0NQhkzs.jpg',
                'summary' => 'Điều khoản điều kiện',
                'content' => DataPostExample::getTermConditions($store_name),
                'is_show' => true,
            ]
        );
        PostCategoryPost::create(
            [
                'post_id' => $postCreate->id,
                'categorypost_id' => $categoryCreate->id
            ]
        );

        $webThemeExists->update(['post_id_terms' => $postCreate->id]);
        echo 'ok';
    }
}
