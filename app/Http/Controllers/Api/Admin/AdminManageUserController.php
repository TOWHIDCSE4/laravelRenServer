<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Helper\StringUtils;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Category;
use App\Models\CategoryChild;
use App\Models\CategoryPost;
use App\Models\Collaborator;
use App\Models\Combo;
use App\Models\Customer;
use App\Models\DateTimekeepingShift;
use App\Models\Decentralization;
use App\Models\ImportStock;
use App\Models\MsgCode;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\SessionUser;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\TallySheet;
use App\Models\TransferStock;
use App\Models\User;
use App\Models\UserAdvice;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group  Admin/Quản lý User
 *
 * APIs Quản lý User
 */
class AdminManageUserController extends Controller
{
    /**
     * Danh sách user
     * /stores?page=1&search=name&sort_by=id&descending=false

     * @queryParam  page Lấy danh sách ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: samsung
     * @queryParam  sort_by Sắp xếp theo VD: price
     * @queryParam  descending Giảm dần không VD: false 
     */

    public function getAll(Request $request)
    {
        $users = User::sortByRelevance(true)
            ->when(User::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(request('sort_by') == null, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->search(StringUtils::convert_name(request('search')))
            ->paginate(20);

        foreach ($users as $user) {

            $u = UserAdvice::where('phone_number', $user->phone_number)->first();
            $user->added_user_device = $u != null;
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $users,
        ]);
    }

    public function getDBSizeInMB($store_id, $table)
    {
        $result = DB::select(DB::raw('SELECT ' . $table . ' AS "Table",
                ((data_length + index_length) / 1024 / 1024) AS "Size"
                FROM information_schema.TABLES
                WHERE store_id ="' . 'laraveldemo' . '"
                ORDER BY (data_length + index_length) DESC'));

        $size = array_sum(array_column($result, 'Size'));
        $db_size = number_format((float)$size, 2, '.', '');
        dd($db_size);
    }

    /**
     * Thông tin một sản phẩm
     * @urlParam  id required ID User cần lấy thông tin.
     */
    public function getInfoDataWithPhone(Request $request, $id)
    {
        $phone_number = $request->route()->parameter('phone_number');
        $userExists = User::where(
            'phone_number',
            $phone_number
        )->first();

        if (empty($userExists)) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ]);
        } else {

            $list_store = Store::where('user_id', $userExists->id)->get();


            $data_store = [];
            foreach ($list_store  as $store) {
                array_push(
                    $data_store,
                    [
                        "store" => $store,
                        "total_function" => [
                            "TProduct" => [
                                "name" => "Tổng số sản phẩm (cả xóa)",
                                "count" => Product::where('store_id', $store->id)->count()
                            ],
                            "TPost" => [
                                "name" => "Tổng số bài viết",
                                "count" => Post::where('store_id', $store->id)->count()
                            ],
                            "TAgency" => [
                                "name" => "Tổng số đại lý",
                                "count" => Agency::where('store_id', $store->id)->count()
                            ],
                            "TCollaborator" => [
                                "name" => "Tổng số ctv",
                                "count" => Collaborator::where('store_id', $store->id)->count()
                            ],
                            "TBranch" => [
                                "name" => "Tổng số chi nhánh",
                                "count" => Branch::where('store_id', $store->id)->count()
                            ],
                            "TCategory" => [
                                "name" => "Tổng số danh mục sản phẩm",
                                "count" => Category::where('store_id', $store->id)->count()
                            ],
                            "TCategoryChild" => [
                                "name" => "Tổng số danh mục con",
                                "count" => CategoryChild::where('store_id', $store->id)->count()
                            ],
                            "TCategoryPost" => [
                                "name" => "Tổng số danh mục bài viết",
                                "count" => CategoryPost::where('store_id', $store->id)->count()
                            ],
                            "TCategoryPostChildren" => [
                                "name" => "Tổng số danh mục con bài viết",
                                "count" => CategoryPost::where('store_id', $store->id)->count()
                            ],
                            "TCart" => [
                                "name" => "Tổng số giỏ hàng hiện tại (đang lên customer hoặc pos)",
                                "count" => CategoryPost::where('store_id', $store->id)->count()
                            ],
                            "TCombo" => [
                                "name" => "Tổng số combo (đang diễn ra hoặc đã kết thúc)",
                                "count" => Combo::where('store_id', $store->id)->count()
                            ],
                            "TVoucher" => [
                                "name" => "Tổng số voucher (đang diễn ra hoặc đã kết thúc)",
                                "count" => Voucher::where('store_id', $store->id)->count()
                            ],
                            "TProductDiscount" => [
                                "name" => "Tổng số sp khuyến mãi (đang diễn ra hoặc đã kết thúc)",
                                "count" => ProductDiscount::where('store_id', $store->id)->count()
                            ],
                            "TCustomer" => [
                                "name" => "Tổng số khách hàng",
                                "count" => Customer::where('store_id', $store->id)->count()
                            ],

                            "TShift" => [
                                "name" => "Tổng số ca làm việc",
                                "count" => Shift::where('store_id', $store->id)->count()
                            ],
                            "TDecrentralization" => [
                                "name" => "Tổng số phân quyền",
                                "count" => Decentralization::where('store_id', $store->id)->count()
                            ],
                            "TStaff" => [
                                "name" => "Tổng số nhân viên",
                                "count" => Staff::where('store_id', $store->id)->count()
                            ],
                            "TOrder" => [
                                "name" => "Tổng số đơn hàng",
                                "count" => Order::where('store_id', $store->id)->count()
                            ],
                            "TSupplier" => [
                                "name" => "Tổng số nhà cung cấp",
                                "count" => Supplier::where('store_id', $store->id)->count()
                            ],
                            "TTallySheet" => [
                                "name" => "Tổng số phiếu kiểm hàng",
                                "count" => TallySheet::where('store_id', $store->id)->count()
                            ],
                            "TTransferStock" => [
                                "name" => "Tổng số phiếu chuyển hàng",
                                "count" => TransferStock::where('store_id', $store->id)->count()
                            ],
                            "TImportStock" => [
                                "name" => "Tổng số phiếu nhập hàng",
                                "count" => ImportStock::where('store_id', $store->id)->count()
                            ],
                        ]
                    ]
                );
            }
            $ssU = SessionUser::where('user_id', $userExists->id)->first();


            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  [
                    "user" => $userExists,
                    "token" =>   $ssU  == null ? null :   $ssU->token,
                    "data_store" => $data_store
                ],
            ]);
        }
    }

    /**
     * Thông tin một sản phẩm
     * @urlParam  id required ID User cần lấy thông tin.
     */
    public function getOneUser(Request $request, $id)
    {
        $id = $request->route()->parameter('user_id');
        $storeExists = User::where(
            'id',
            $id
        )->first();

        if (empty($storeExists)) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ]);
        } else {

            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $storeExists,
            ]);
        }
    }


    /**
     * Thông tin một sản phẩm
     * @urlParam  id required ID User cần lấy thông tin.
     * @bodyParam  functions required Danh sách chức năng
     * 
     */
    public function updateOneUser(Request $request, $id)
    {
        $id = $request->route()->parameter('user_id');
        $storeExists = User::where(
            'id',
            $id
        )->first();



        if (empty($storeExists)) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
            ]);
        } else {

            $functions = null;

            if (is_array($request->functions)) {

                $functions = json_encode($request->functions);
            }

            $storeExists->update(Helper::removeItemArrayIfNullValue(
                [
                    'functions_json' =>   $functions
                ]
            ));

            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' =>  $storeExists,
            ]);
        }
    }
}
