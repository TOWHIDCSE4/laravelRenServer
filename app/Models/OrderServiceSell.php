<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class OrderServiceSell extends BaseModel
{
    use HasFactory;
    // use SearchableTrait;
    use Searchable;

    protected $searchable = [];

    protected $guarded = [];

    protected $appends = ['list_category_service_sell'];

    public function getListCategoryServiceSellAttribute()
    {
        $listCategory = CategoryServiceSell::join('line_item_service_sells', 'category_service_sells.id', '=', 'line_item_service_sells.category_service_sell_id')
            ->where('line_item_service_sells.order_service_sell_id', $this->id)
            ->select('category_service_sells.*');

        if (!isset(request('user')->is_admin) && !request('user')->is_admin == true) {
            return $listCategory->where('user_id', request('user')->id);
        }
        $listCategory = $listCategory->groupBy('category_service_sells.id')->get();
        foreach ($listCategory as &$category) {
            $category->list_service_sell = LineItemServiceSell::where([
                ['order_service_sell_id', $this->id],
                ['category_service_sell_id', $category->id],
            ])->orderBy('created_at', 'desc')->get();
        }
        return $listCategory;
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }
}
