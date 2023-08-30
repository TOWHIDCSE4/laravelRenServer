<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\AdminDiscoverItemUi;
use App\Models\AdminDiscoverUi;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminDiscoverItemUiController extends Controller
{
    /*
    *  Tạo ui item admin discover
    *  
    *  @bodyQuery image url ảnh
    *  @bodyQuery content 
    *  @bodyQuery admin_discover int mã admin discover
    */
    public function create(Request $request)
    {
        $adminDiscoverExists = DB::table('admin_discover_uis')->where('id', $request->admin_discover_id)->first();
        if (!isset($request->admin_discover_id) || $adminDiscoverExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[0],
                'msg' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[1]
            ]);
        }

        if (Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        $adminDiscoverItemCreate = AdminDiscoverItemUi::create([
            'admin_discover_id' => $request->admin_discover_id,
            "district" => $request->district,
            "province" => $adminDiscoverExists->province,
            "district_name" => Place::getNameDistrict($request->district),
            'content' => $request->content,
            'image' => $request->image,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverItemCreate
        ]);
    }


    /*
    *  Cập nhật ui admin item discover
    *  
    *  @bodyQuery content 
    *  @bodyQuery admin_discover_id int mã admin discover
    *  @bodyQuery image url ảnh
    */
    public function update(Request $request, $id)
    {
        $adminDiscoverItemExist = AdminDiscoverItemUi::where('id', $id)->first();

        if ($adminDiscoverItemExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADMIN_DISCOVER_ITEM_EXISTS[0],
                'msg' => MsgCode::NO_ADMIN_DISCOVER_ITEM_EXISTS[1],
            ]);
        }

        if (isset($request->district)) {
            if (Place::getNameDistrict($request->district) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DISTRICT[0],
                    'msg' => MsgCode::INVALID_DISTRICT[1],
                ]);
            }
        }

        if (isset($request->admin_discover_id)) {
            $adminDiscoverExist = AdminDiscoverUi::where('id', $request->admin_discover_id)->first();
            if ($adminDiscoverExist == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[0],
                    'msg' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[1],
                ]);
            }
        }

        $adminDiscoverItemExist->update([
            'admin_discover_id' => isset($request->admin_discover_id) ? $request->admin_discover_id : $adminDiscoverItemExist->admin_discover_id,
            "district" => isset($request->district) ? $request->district : $adminDiscoverItemExist->district,
            "district_name" => isset($request->district) ? Place::getNameDistrict($request->district) : $adminDiscoverItemExist->district,
            'content' => $request->content,
            'image' => $request->image,
        ]);

        $adminDiscoverItemExist->save();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverItemExist
        ]);
    }

    /*
    *  Xem 1 admin item discover
    */
    public function getOne(Request $request, $id)
    {
        $adminDiscoverItemExist = AdminDiscoverItemUi::where('id', $id)->first();

        if ($adminDiscoverItemExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADMIN_DISCOVER_ITEM_EXISTS[0],
                'msg' => MsgCode::NO_ADMIN_DISCOVER_ITEM_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverItemExist
        ]);
    }

    /*
    *  Xóa ui admin discover
    *  
    *  @bodyQuery list_id_item_discover_ui
    */
    public function delete(Request $request)
    {
        $IdDeleted = [];
        $data = null;
        if (is_array($request->list_id_item_discover_ui)) {
            foreach ($request->list_id_item_discover_ui as $itemDiscoverId) {
                $existAdminDiscoverItem = AdminDiscoverItemUi::where('id', $itemDiscoverId)->first();
                if ($existAdminDiscoverItem != null) {
                    array_push($IdDeleted, $existAdminDiscoverItem->id);
                    $existAdminDiscoverItem->delete();
                }
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_ID_DISCOVER_ITEM_UI[0],
                'msg' => MsgCode::INVALID_LIST_ID_DISCOVER_ITEM_UI[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['list_id_discover_item_ui_deleted' => $IdDeleted]
        ]);
    }


    /*
    *  Danh sách ui admin discover
    *  
    *  @queryParam admin_discover_id int mã 
    */
    public function getAll(Request $request)
    {
        $listAdminDiscoverItem = AdminDiscoverItemUi::when(request('admin_discover_id') != null, function ($query) {
            $query->where('admin_discover_id', request('admin_discover_id'));
        })
            ->get();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listAdminDiscoverItem
        ]);
    }
}
