<?php

namespace App\Jobs;

use App\Models\AppTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Attribute;
use App\Models\AttributeField;
use App\Models\BannerAd;
use App\Models\CarouselAppImage;
use App\Models\Category;
use App\Models\CategoryPost;
use App\Models\ConfigDataExample;
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

class DataDemoNewStoreJob implements ShouldQueue
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

    public function handle()
    {


        $configDataDB = ConfigDataExample::where('type_id', $this->career)->first();
        if ($configDataDB != null) {

            $store = Store::where('store_code', $configDataDB->store_code)->first();
            if ($store == null) {

                $this->handleWithDataLocal();
            } else {

                //Có store
                $this->handleWithDataDB($store);
            }
        } else {
            $this->handleWithDataLocal();
        }
    }

    public function handleWithDataDB($store)
    {

        $storeNew = Store::where('id', $this->store_id)->first();


        //Thêm thuộc tính 
        $fields = json_encode(["Thương hiệu", "Xuất xứ"]);
        $attributeFieldExists = AttributeField::where(
            'store_id',
            $this->store_id
        )->first();
        if (empty($attributeFieldExists)) {
            AttributeField::create(
                [
                    'store_id' =>     $this->store_id,
                    'fields' =>  $fields
                ]
            );
        }

        //Xu ly app theme
        $appTheme = AppTheme::where('store_id', $store->id)->first();

        if ($appTheme != null) {
            $appThemeNew  =   $appTheme->replicate();
            $appThemeNew->store_id =  $this->store_id;
            $appThemeNew->save();
        }

        //Xu ly web theme
        $webTheme = WebTheme::where('store_id', $store->id)->first();

        if ($webTheme != null) {
            $webThemeNew  =   $webTheme->replicate();
            $webThemeNew->store_id =  $this->store_id;
            $webThemeNew->domain =  "";
            $webThemeNew->save();
        }

        //Carousel App
        $carousels = CarouselAppImage::where('store_id', $store->id)->get();
        foreach ($carousels as  $carousel) {
            if ($carousel != null) {
                $carouselNew  =   $carousel->replicate();
                $carouselNew->store_id =  $this->store_id;
                $carouselNew->save();
            }
        }

        //Banner ADS
        $banes = BannerAd::where('store_id', $store->id)->get();
        foreach ($banes as  $bane) {
            if ($bane != null) {
                $baneNew  =   $bane->replicate();
                $baneNew->store_id =  $this->store_id;
                $baneNew->save();
            }
        }

        //Xu ly post
        $posts = Post::where('store_id', $store->id)->get()->reverse();
        foreach ($posts as $post) {
            $newPost = $post->replicate();
            $newPost->store_id =  $this->store_id;
            $newPost->save();

            //Liên kết post vs cate
            $postCategory = PostCategoryPost::where('post_id', $post->id)->first();
            if ($postCategory != null) {

                $category = CategoryPost::where('id', $postCategory->categorypost_id)->first();

                if ($category != null) {

                    $categoryNew = CategoryPost::where('title', $category->title)->where('store_id', $this->store_id)->first();

                    if ($categoryNew == null) {
                        $categoryNew  =   $category->replicate();
                        $categoryNew->store_id =  $this->store_id;
                        $categoryNew->save();
                    }

                    PostCategoryPost::create([
                        'post_id' =>  $newPost->id,
                        'categorypost_id' => $categoryNew->id
                    ]);
                }
            }
        }

        //Banner ADS
        $categories = Category::where('store_id', $store->id)->get();
        foreach ($categories as  $category) {
            if ($category != null) {
                $categoryNew  =   $category->replicate();
                $categoryNew->store_id =  $this->store_id;
                $categoryNew->save();
            }
        }

        $products = Product::where('store_id', $store->id)->get()->reverse();
        foreach ($products as $product) {
            $newProduct = $product->replicate();
            $newProduct->store_id =  $this->store_id;
            $newProduct->save();

            $productImages = ProductImage::where('product_id', $product->id)
                ->get();
            foreach ($productImages as $productImage) {
                ProductImage::create([
                    'product_id' => $newProduct->id,
                    'image_url' => $productImage->image_url
                ]);
            }

            //Liên kết product vs cate
            $productCategories = ProductCategory::where('product_id', $product->id)->get();
            if (count($productCategories) > 0) {

                foreach ($productCategories as  $productCategory) {
                    $category = Category::where('id', $productCategory->category_id)->first();

                    if ($category != null) {

                        $categoryNew = Category::where('name', $category->name)->where('store_id', $this->store_id)->first();

                        if ($categoryNew == null) {
                            $categoryNew = $category->replicate();
                            $categoryNew->store_id = $this->store_id;
                            $categoryNew->save();
                        }

                        ProductCategory::create([
                            'product_id' =>  $newProduct->id,
                            'category_id' => $categoryNew->id
                        ]);
                    }
                }
            }
        }
    }


    public function handleWithDataLocal()
    {
        $career =  $this->career;
        $store_id =  $this->store_id;

        $rawXML = null;
        try {
            $rawXML =  Storage::get('new_store_data/store_' . $career . '.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (\Exception $e) {
        }

        if ($rawXML  === null) {
            return;
        } else {

            $xml = simplexml_load_string($rawXML);

            $json = json_encode($xml);
            $array = json_decode($json, true);


            $i = 0;
            foreach ($array["products"]['product'] as $product) {
                $array["products"]['product'][$i]["description"] = $xml->products->product[$i]->description->__toString();
                $i++;
            }


            $store = Store::where('id', $store_id)->first();

            //Banner
            foreach ($array["banners"]['banner'] as $banner) {
                CarouselAppImage::create([
                    "title" => "",
                    "image_url" => $banner,
                    "store_id" => $store_id
                ]);
            }

            //AppTheme

            $appThemeExists = AppTheme::where(
                'store_id',
                $store_id
            )->first();

            $appThemeUpdate = [
                'store_id' => $store_id,
                'logo_url' => $array['logo'] ?? "https://dr.myiki.vn/img/default_logo.png",
                'color_main_1' => $array['color_main_1'] ?? "#FF0000",
                'is_scroll_button' => 0,
                'type_button' => 0

            ];
            if ($appThemeExists !== null) {


                $appThemeExists->update(
                    $appThemeUpdate
                );
            } else {


                $appThemeExists =  AppTheme::create(
                    $appThemeUpdate
                );
            }

            //Home Buttons
            //Add item
            // if ($array["home_buttons"]['home_button'] != null && is_array($array["home_buttons"]['home_button'])) {


            //     $homeButtons = [];
            //     foreach ($array["home_buttons"]['home_button'] as $homeButton) {

            //         $image_url = $homeButton["image_url"] ?? null;
            //         $title = $homeButton["title"] ?? null;
            //         $type_action = $homeButton["type_action"] ?? null;
            //         $value = $homeButton["value"] ?? null;

            //         if (isset($type_action) && isset($title)) {
            //             array_push($homeButtons, [
            //                 "title" => $title,
            //                 "type_action" => $type_action,
            //                 "value" => $value,
            //                 "image_url" => $image_url,
            //             ]);
            //         }
            //     }

            //     $homeButtonExists = HomeButton::where(
            //         'store_id',
            //         $store_id
            //     )->first();


            //     $json_buttons = json_encode($homeButtons);

            //     if ($homeButtonExists !== null) {
            //         $homeButtonExists->update(
            //             [
            //                 "json_buttons" =>  $json_buttons
            //             ]
            //         );
            //     } else {

            //         HomeButton::create(
            //             [
            //                 'store_id' =>  $store_id,
            //                 "json_buttons" => " $json_buttons "
            //             ]
            //         );
            //     }
            // }


            //Category
            $save_cate = [];
            foreach ($array["categories"]['category'] as $category) {
                $categoryCreate = Category::create(
                    [
                        'image_url' => $category['image'],
                        'name' => $category['name'],
                        'store_id' => $store_id
                    ]
                );
                $save_cate[$category['id']] =  $categoryCreate->id;
            }
            ///////////
            $attributes = [];
            foreach ($array["attributes"]['attribute'] as $attribute) {
                array_push($attributes, $attribute);
            }
            $fields = json_encode($attributes);

            $attributeFieldExists = AttributeField::where(
                'store_id',
                $store_id
            )->first();

            if (empty($attributeFieldExists)) {
                AttributeField::create(
                    [
                        'store_id' => $store_id,
                        'fields' =>  $fields
                    ]
                );
            } else {
                $attributeFieldExists->update(
                    [
                        'fields' =>  $fields
                    ]
                );
            }
            ///////////
            foreach ($array["products"]['product'] as $product) {
                $name = $product["name"];
                $description = $product["description"];
                $price = floatval($product["price"]);
                $quantity_in_stock = (int)($product["quantity_in_stock"]);


                $productCreate = Product::create(
                    [
                        'description' => $description,
                        'name' => $name,
                        'store_id' => $store_id,
                        'price' => $price,

                        'status' => 0,
                        'quantity_in_stock' => $quantity_in_stock
                    ]
                );


                if ($product["images"]['image'] !== null && count((array)$product["images"]['image']) > 0) {

                    foreach ((array)$product["images"]['image'] as $image) {
                        ProductImage::create(
                            [
                                'image_url' => $image,
                                'product_id' => $productCreate->id,
                            ]
                        );
                    }
                }

                ProductCategory::create(
                    [
                        'product_id' => $productCreate->id,
                        'category_id' => (int)   $save_cate[$product["categories"]]
                    ]
                );

                if (isset($product['list_distribute']) && isset($product['list_distribute']['distribute']) && is_array($product['list_distribute']['distribute']) && count((array)$product['list_distribute']['distribute']) > 0) {

                    foreach ((array)$product['list_distribute']['distribute'] as $distribute) {
                        if (isset($distribute["element_distributes"]) && count($distribute["element_distributes"]) > 0) {

                            $distributeCreated = Distribute::create(
                                [
                                    'product_id' => $productCreate->id,
                                    'store_id' => $store_id,
                                    'name' => $distribute["name"],
                                ]
                            );


                            foreach ($distribute["element_distributes"] as $element_distribute) {
                                ElementDistribute::create(
                                    [
                                        'product_id' => $productCreate->id,
                                        'store_id' => $store_id,
                                        'name' => $element_distribute['name'],
                                        'image_url' => isset($element_distribute["image_url"]) ? $element_distribute["image_url"] : null,
                                        'distribute_id' => $distributeCreated->id
                                    ]
                                );
                            }

                            ProductDistribute::create(
                                [
                                    'store_id' => $store_id,
                                    'product_id' => $productCreate->id,
                                    'distribute_id' => $distributeCreated->id
                                ]
                            );
                        }
                    }
                }

                if (isset($product['list_attribute']) && isset($product['list_attribute']['attribute']) && is_array($product['list_attribute']['attribute']) && count((array)$product['list_attribute']['attribute']) > 0) {

                    foreach ((array)$product['list_attribute']['attribute'] as $attribute) {
                        if (isset($attribute["name"]) && isset($attribute["value"]) != null) {
                            $distributeCreated = Attribute::create(
                                [
                                    'store_id' => $store_id,
                                    'product_id' => $productCreate->id,
                                    'name' => $attribute["name"],
                                    'value' => $attribute["value"],
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
