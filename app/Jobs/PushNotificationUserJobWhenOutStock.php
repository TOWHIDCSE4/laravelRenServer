<?php

namespace App\Jobs;

use App\Helper\InventoryUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\User\GeneralSettingController;
use App\Models\Branch;
use App\Models\ElementDistribute;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\Store;
use App\Models\SubElementDistribute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushNotificationUserJobWhenOutStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store_id;
    protected $branch_id;
    protected $product_id;
    protected $element_distribute_id;
    protected $sub_element_distribute_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $store_id,
        $branch_id,
        $product_id,
        $element_distribute_id,
        $sub_element_distribute_id
    ) {
        $this->store_id = $store_id;
        $this->branch_id = $branch_id;
        $this->product_id = $product_id;
        $this->element_distribute_id = $element_distribute_id;
        $this->sub_element_distribute_id = $sub_element_distribute_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $store = Store::where('id', $this->store_id)->first();
        $branch = Branch::where('id', $this->branch_id)->first();
        $product = Product::where('id', $this->product_id)->first();
        $ele = ElementDistribute::where('id', $this->element_distribute_id)->first();
        $sub_ele = SubElementDistribute::where('id', $this->sub_element_distribute_id)->first();


        $count_his = InventoryHistory::where('store_id',  $this->store_id)
            ->where('branch_id', $this->branch_id)
            ->where('product_id', $this->product_id)
            ->where('element_distribute_id', $this->element_distribute_id)
            ->where('sub_element_distribute_id', $this->sub_element_distribute_id)
            ->count();

        $config = GeneralSettingController::defaultOfStoreID($this->store_id);
        $allow_semi_negative = $config['allow_semi_negative'];
        $near = $config['noti_stock_count_near'];

        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
            $this->store_id,
            $this->branch_id,
            $this->product_id,
            $this->element_distribute_id,
            $this->sub_element_distribute_id
        );

        $stock = $data_stock['stock'];

        if ($count_his > 1 && $allow_semi_negative == false && $product->check_inventory == true && $data_stock['stock'] <= $near) {

            $content = "Sản phẩm " . $product->name . ' sắp hết hàng, chỉ còn lại ' . $stock;

            if ($sub_ele != null) {
                $content = "Sản phẩm " . $product->name . ' phân loại ' . $ele->name . ',' . $sub_ele->name . ' sắp hết hàng, chỉ còn lại ' . $stock;
            } else
            if ($ele != null) {
                $content = "Sản phẩm " . $product->name . ' phân loại ' . $ele->name . ' sắp hết hàng, chỉ còn lại ' . $stock;
            }


            PushNotificationUserJob::dispatch(
                $store->id,
                $store->user_id,
                'CN ' . $branch->name . ' ',
                $content,
                TypeFCM::NEAR_OUT_STOCK,
                $product->id,
                $this->branch_id,
            );
        }
    }
}
