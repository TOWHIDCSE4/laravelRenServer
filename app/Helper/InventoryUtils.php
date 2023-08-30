<?php

namespace App\Helper;

use App\Jobs\PushNotificationUserJobWhenOutStock;
use App\Models\Branch;
use App\Models\InventoryEleDis;
use App\Models\InventoryHistory;
use App\Models\InventorySubDis;
use App\Models\Product;
use App\Models\ProductInventory;

class InventoryUtils
{

    const MAIN_PRODUCT_STOCK = "MAIN_PRODUCT_STOCK"; //
    const ELEMENT_DISTRIBUTE_STOCK = "ELEMENT_DISTRIBUTE_STOCK"; //
    const SUB_ELEMENT_DISTRIBUTE_STOCK = "SUB_ELEMENT_DISTRIBUTE_STOCK"; //

    const STATUS_TRANSFER_AWAIT = 0; //Chờ chuyển
    const STATUS_TRANSFER_CANCEL = 1; //Đã hủy
    const STATUS_TRANSFER_OK = 2; //Đã nhận kho

    const STATUS_INVENTORY_CHECKED = 0; //Đã check
    const STATUS_INVENTORY_BALANCE = 1; //Đã cân bằng

    const STATUS_IMPORT_STOCK_ORDER = 0; //Đặt hàng
    const STATUS_IMPORT_STOCK_BROWSING = 1; //Duyệt
    const STATUS_IMPORT_STOCK_WAREHOUSE = 2; //Nhập kho
    const STATUS_IMPORT_STOCK_COMPLETED = 3; //Hoàn thành
    const STATUS_IMPORT_STOCK_CANCELED = 4; //Đã hủy
    const STATUS_IMPORT_STOCK_PAUSE = 5; //Kết thúc
    const STATUS_IMPORT_STOCK_REFUND = 6; //Trả hàng
    const STATUS_IMPORT_STOCK_REFUNDED = 7; //Đã trả hết

    const STATUS_IMPORT_STOCK_UNPAID = 0; //Chưa thanh toán
    const STATUS_IMPORT_STOCK_PART_PAYMENT = 1; //Thanh toán 1 một phần
    const STATUS_IMPORT_STOCK_PAID = 2; //Đã thanh toán

    const TYPE_EDIT_STOCK = 0; //Cân bằng sửa kho
    const TYPE_TALLY_SHEET_STOCK = 1; //Cân bằng kiểm kho
    const TYPE_IMPORT_STOCK = 2; //Nhập kho
    const TYPE_INIT_STOCK = 3; //Khởi tạo kho
    const TYPE_EXPORT_ORDER_STOCK = 4; //Xuất đơn hàng
    const TYPE_REFUND_IMPORT_STOCK = 5; //Hoàn trả nhập hàng
    const TYPE_REFUND_ORDER = 6; //Hoàn trả đơn hàng
    const TYPE_EDIT_COST_OF_CAPITAL = 7; //Điều chỉnh giá vốn
    const TYPE_IMPORT_AUTO_CHANGE_COST_OF_CAPITAL = 8; //Tự động điều chỉnh giá vốn khi nhập hàng
    const TYPE_TRANSFER_STOCK_RECEIVER = 9; //Nhận hàng từ chi nhánh khách
    const TYPE_TRANSFER_STOCK_SENDER = 10; //Chuyển hàng đến chi nhánh khác

    static public function add_sub_stock_by_id($store_id, $branch_id, $product_id, $element_distribute_id, $sub_element_distribute_id, $value = 0, $type = null, $references_id = null, $references_value = null)
    {
        if ($value != 0) {
            $status_stock =  InventoryUtils::get_stock_by_distribute_by_id(
                $store_id,
                $branch_id,
                $product_id,
                $element_distribute_id,
                $sub_element_distribute_id
            );

            $reality_exist = $status_stock['stock'];

            InventoryUtils::update_cost_of_capital_or_stock_by_id(
                $store_id,
                $branch_id,
                $product_id,
                $element_distribute_id,
                $sub_element_distribute_id,
                null,
                $reality_exist + $value,
                $type,
                $references_id,
                $references_value
            );
        }
    }


    static public function update_cost_of_capital_or_stock_by_id($store_id, $branch_id, $product_id, $element_distribute_id, $sub_element_distribute_id, $cost_of_capital, $stock, $type = null, $references_id = null, $references_value = null)
    {
        PushNotificationUserJobWhenOutStock::dispatch(
            $store_id,
            $branch_id,
            $product_id,
            $element_distribute_id,
            $sub_element_distribute_id,
        );


        $product = Product::where('id', $product_id)->first();
        if ($product->check_inventory == false) {
            return [
                'status' => false
            ];
        }

        $before_stock = 0;
        $before_cost_of_capital =  0;
        if (!empty($element_distribute_id) && !empty($sub_element_distribute_id)) {

            $inventorySub = InventorySubDis::where('branch_id', $branch_id)
                ->where('element_distribute_id', $element_distribute_id)
                ->where('sub_element_distribute_id', $sub_element_distribute_id)
                ->where('product_id', $product_id)
                ->first();


            if ($inventorySub != null) {

                $before_stock =  $inventorySub->stock ?? 0;
                $before_cost_of_capital =  $inventorySub->cost_of_capital ?? 0;

                if ($cost_of_capital !== null && $before_cost_of_capital != $cost_of_capital) {
                    $inventorySub->update([
                        'cost_of_capital' => $cost_of_capital
                    ]);


                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' =>  $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_COST_OF_CAPITAL,
                        "stock" =>  $before_stock,
                        'cost_of_capital' => $cost_of_capital,
                        "change" => 0,
                        "change_money" => ($cost_of_capital * $stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }

                $before_stock =  $inventorySub->stock ?? 0;
                $before_cost_of_capital =  $inventorySub->cost_of_capital ?? 0;


                if ($stock !== null && $before_stock != $stock) {
                    $inventorySub->update([
                        'stock' => $stock
                    ]);


                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' =>  $element_distribute_id,
                        'sub_element_distribute_id' => $sub_element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_STOCK,
                        "stock" => $stock,
                        'cost_of_capital' => $before_cost_of_capital,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($cost_of_capital * $stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            } else {


                if ($cost_of_capital !== null || $stock !== null) {
                    InventorySubDis::create([
                        "store_id" =>  $store_id,
                        "product_id" => $product_id,
                        "element_distribute_id" => $element_distribute_id,
                        "sub_element_distribute_id" =>  $sub_element_distribute_id,
                        "branch_id" => $branch_id,
                        "cost_of_capital" => $cost_of_capital,
                        "stock" => $stock,
                    ]);
                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' =>  $element_distribute_id,
                        'sub_element_distribute_id' =>  $sub_element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_INIT_STOCK,
                        "stock" => $stock,
                        "cost_of_capital" => $cost_of_capital,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($cost_of_capital * $stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            }


            InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($store_id, $product_id);
            return [
                'status' => true,
                'product_id' => $product_id,
                'element_distribute_id' =>  $element_distribute_id,
                'sub_element_distribute_id' =>  $sub_element_distribute_id,
            ];
        } else if (!empty($element_distribute_id)) {

            $inventory_element =    InventoryEleDis::where('product_id', $product_id)
                ->where('element_distribute_id', $element_distribute_id)
                ->where('branch_id', $branch_id)
                ->where('store_id', $store_id)->first();

            if ($inventory_element  != null) {

                $before_stock =  $inventory_element->stock ?? 0;
                $before_cost_of_capital =  $inventory_element->cost_of_capital ?? 0;

                if ($cost_of_capital !== null && $before_cost_of_capital != $cost_of_capital) {
                    $inventory_element->update(Helper::removeItemArrayIfNullValue(
                        [
                            "cost_of_capital" => $cost_of_capital,
                        ]
                    ));

                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' =>  $element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_COST_OF_CAPITAL,
                        "stock" =>  $before_stock,
                        "cost_of_capital" => $cost_of_capital,
                        "change" => 0,
                        "change_money" => ($cost_of_capital * $before_stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }


                $before_stock =  $inventory_element->stock ?? 0;
                $before_cost_of_capital =  $inventory_element->cost_of_capital ?? 0;

                if ($stock !== null && $stock != $before_stock) {
                    $inventory_element->update(Helper::removeItemArrayIfNullValue(
                        [
                            "stock" => $stock
                        ]
                    ));


                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' =>  $element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_STOCK,
                        "stock" => $stock,
                        "cost_of_capital" =>  $before_cost_of_capital,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($before_cost_of_capital * $stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            } else {
                if ($cost_of_capital !== null || $stock !== null) {
                    InventoryEleDis::create([
                        "store_id" =>  $store_id,
                        "product_id" => $product_id,
                        "element_distribute_id" => $element_distribute_id,
                        "branch_id" => $branch_id,
                        "cost_of_capital" => $cost_of_capital,
                        "stock" => $stock
                    ]);

                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        'element_distribute_id' => $element_distribute_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_STOCK,
                        "stock" => $stock,
                        "cost_of_capital" => $cost_of_capital,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($cost_of_capital * $stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            }
            InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($store_id, $product_id);
            return [
                'status' => true,
                'product_id' => $product_id,
                'element_distribute_id' =>  $element_distribute_id,
            ];
        } else {

            $productInventoryExists =    ProductInventory::where('product_id', $product_id)
                ->where('branch_id', $branch_id)
                ->where('store_id', $store_id)->first();

            if ($productInventoryExists != null) {
                $before_stock =  $productInventoryExists->stock ?? 0;
                $before_cost_of_capital =  $productInventoryExists->cost_of_capital ?? 0;

                if ($cost_of_capital !== null &&  $before_cost_of_capital != $cost_of_capital) {
                    $productInventoryExists->update([
                        'cost_of_capital' => $cost_of_capital
                    ]);

                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_COST_OF_CAPITAL,
                        "stock" =>  $before_stock,
                        "cost_of_capital" => $cost_of_capital,
                        "change" => 0,
                        "change_money" => ($cost_of_capital * $before_stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }

                $before_stock =  $productInventoryExists->stock ?? 0;
                $before_cost_of_capital =  $productInventoryExists->cost_of_capital ?? 0;


                if ($stock !== null && $stock != $before_stock) {


                    $productInventoryExists->update([
                        'stock' => $stock
                    ]);


                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        "type" => $type ?? InventoryUtils::TYPE_EDIT_STOCK,
                        "stock" => $stock,
                        "cost_of_capital" => $before_cost_of_capital,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($before_cost_of_capital * $stock) - ($before_cost_of_capital * $before_stock),
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            } else {


                if ($cost_of_capital !== null ||  $stock !== null) {
                    ProductInventory::create([
                        "store_id" => $store_id,
                        "product_id" => $product_id,
                        "branch_id" => $branch_id,
                        "cost_of_capital" => $cost_of_capital ?? 0,
                        "stock" => $stock ?? 0,
                    ]);

                    InventoryHistory::create([
                        'store_id' => $store_id,
                        'branch_id' =>  $branch_id,
                        'product_id' => $product_id,
                        "type" => InventoryUtils::TYPE_EDIT_STOCK,
                        "stock" => $stock ?? 0,
                        "change" => $stock -  $before_stock,
                        "change_money" => ($cost_of_capital * $stock),
                        "cost_of_capital" => $cost_of_capital  ?? 0,
                        "references_id" => $references_id,
                        "references_value" => $references_value
                    ]);
                }
            }

            InventoryUtils::update_total_stock_all_branch_to_quantity_in_stock_by_id($store_id, $product_id);
            return [
                'status' => true,
                'product_id' => $product_id,
            ];
        }
        return [
            'status' => false,
            'product_id' => $product_id,
        ];
    }

    static public function get_stock_by_distribute_by_id($store_id, $branch_id, $product_id, $element_distribute_id, $sub_element_distribute_id)
    {

        if (!empty($element_distribute_id) && !empty($sub_element_distribute_id)) {

            $inventorySub = InventorySubDis::where('element_distribute_id', $element_distribute_id)
                ->where('sub_element_distribute_id', $sub_element_distribute_id)
                ->where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->first();


            if ($inventorySub != null) {

                return  [
                    'type' =>  ProductUtils::HAS_SUB,
                    'stock' =>  $inventorySub->stock,
                    'cost_of_capital' => $inventorySub->cost_of_capital,
                    'distribute_id' => "",
                    'element_distribute_id' =>  $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id
                ];
            } else {
                return  [
                    'type' =>  ProductUtils::HAS_SUB,
                    'stock' =>  0,
                    'cost_of_capital' => 0,
                    'distribute_id' => "",
                    'element_distribute_id' =>  $element_distribute_id,
                    'sub_element_distribute_id' => $sub_element_distribute_id
                ];
            }
        } else if (!empty($element_distribute_id)) {

            $inventory_ele = InventoryEleDis::where('element_distribute_id', $element_distribute_id)
                ->where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->first();


            if ($inventory_ele  != null) {
                return  [
                    'type' =>  ProductUtils::HAS_ELE,
                    'stock' =>  $inventory_ele->stock ?? 0,
                    'cost_of_capital' => $inventory_ele->cost_of_capital,
                    'distribute_id' => "",
                    'element_distribute_id' => $element_distribute_id,
                ];
            } else {
                return  [
                    'type' =>  ProductUtils::HAS_ELE,
                    'stock' =>  0,
                    'cost_of_capital' => 0,
                    'distribute_id' => "",
                    'element_distribute_id' => $element_distribute_id,
                ];
            }
        } else {

            $productInventoryExists =    ProductInventory::where('product_id', $product_id)
                ->where('branch_id', $branch_id)
                ->where('store_id', $store_id)->first();

            if ($productInventoryExists != null) {

                return  [
                    'type' =>  ProductUtils::NO_ELE_SUB,
                    'stock' =>  $productInventoryExists->stock,
                    'cost_of_capital' => $productInventoryExists->cost_of_capital,
                ];
            } else {
                ProductInventory::create([
                    "product_id" => $product_id,
                    "branch_id" => $branch_id,
                    "store_id" => $store_id,
                    "stock" => 0,
                    'cost_of_capital' => 0,
                ]);
                return  [
                    'type' =>  ProductUtils::NO_ELE_SUB,
                    'stock' =>  0,
                    'cost_of_capital' => 0,
                ];
            }
        }

        return  [
            'type' =>  ProductUtils::NO_ELE_SUB,
            'stock' =>  0,
            'cost_of_capital' => 0,
        ];
    }


    static public function update_total_stock_all_branch_to_quantity_in_stock_by_id($store_id, $product_id)
    {

        $total_stock = 0;
        $branches = Branch::where('store_id', $store_id)->get();

        $product = Product::where('store_id',  $store_id)->where('id', $product_id)->first();
        $product_type = ProductUtils::check_type_distribute($product);


        if ($product_type  == ProductUtils::NO_ELE_SUB) {

            foreach ($branches  as $branch) {
                $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
                    $store_id,
                    $branch->id,
                    $product->id,
                    null,
                    null,
                );

                $total_stock +=  $data_stock['stock'];
            }
        }


        if ($product_type  == ProductUtils::HAS_ELE) {
            $distributes = $product->distributes[0];
            $element_distributes = $distributes['element_distributes'];


            foreach ($element_distributes as $element_distribute) {
                $total_stock_ele = 0;

                foreach ($branches  as $branch) {
                    $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
                        $store_id,
                        $branch->id,
                        $product->id,
                        $element_distribute->id,
                        null,
                    );

                    $total_stock_ele += $data_stock['stock'];
                }

                $element_distribute->update(
                    [
                        'quantity_in_stock' =>  $total_stock_ele
                    ]
                );
                $total_stock +=  $total_stock_ele;
            }
        }



        if ($product_type  == ProductUtils::HAS_SUB) {
            $distributes = $product->distributes[0];
            $element_distributes = $distributes['element_distributes'];




            foreach ($element_distributes as $element_distribute) {
                foreach ($element_distribute['sub_element_distributes'] as $sub_element_distribute) {
                    $total_stock_sub = 0;
                    foreach ($branches  as $branch) {
                        $data_stock = InventoryUtils::get_stock_by_distribute_by_id(
                            $store_id,
                            $branch->id,
                            $product->id,
                            $element_distribute->id,
                            $sub_element_distribute->id,
                        );
                        $total_stock_sub += $data_stock['stock'];
                    }


                    $sub_element_distribute->update(
                        [
                            'quantity_in_stock' =>  $total_stock_sub
                        ]
                    );
                    $total_stock +=  $total_stock_sub;
                }
            }
        }


        $product->update([
            'quantity_in_stock' =>   $total_stock,

        ]);
    }
}
