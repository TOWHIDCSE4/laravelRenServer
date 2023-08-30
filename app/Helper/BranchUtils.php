<?php

namespace App\Helper;

use App\Models\Agency;
use App\Models\Branch;

class BranchUtils
{

    static function getBranchDefault($store_id)
    {
        $branch_list = Branch::where('store_id', $store_id)->get();

        if (count($branch_list) == 0) {
            $data = [
                'store_id' => $store_id,
                'name' => "Chi nhánh mặc định",
                'is_default' =>  true,
                'is_default_order_online' => true,
            ];

            $branchCreated = Branch::create($data);
            return  $branchCreated;
        } else {
            foreach ($branch_list  as $branch) {
                if ($branch->is_default == true) {
                    return $branch;
                }
            }

            $branch_list[0]->update([
                'is_default' =>  true,
            ]);

            return $branch_list[0];
        }
    }

    static function getBranchDefaultOrderOnline($store_id)
    {
        $branch_list = Branch::where('store_id', $store_id)->get();

        if (count($branch_list) == 0) {
            $data = [
                'store_id' => $store_id,
                'name' => "Chi nhánh mặc định",
                'is_default' =>  true,
                'is_default_order_online' => true,
            ];

            $branchCreated = Branch::create($data);
            return  $branchCreated;
        } else {
            foreach ($branch_list  as $branch) {
                if ($branch->is_default_order_online == true) {
                    return $branch;
                }
            }

            $branch_list[0]->update([
                'is_default_order_online' =>  true,
            ]);

            return $branch_list[0];
        }
    }
}
