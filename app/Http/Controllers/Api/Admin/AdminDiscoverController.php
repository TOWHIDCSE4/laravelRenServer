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

class AdminDiscoverController extends Controller
{
    /*
    *  Tạo ui admin discover
    *  
    *  @bodyQuery province mã tỉnh thành phố
    *  @bodyQuery image ảnh
    */
    public function create(Request $request)
    {

        if (isset($request->province)) {
            if (Place::getNameProvince($request->province) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_PROVINCE[0],
                    'msg' => MsgCode::INVALID_PROVINCE[1],
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PROVINCE_IS_REQUIRE[0],
                'msg' => MsgCode::PROVINCE_IS_REQUIRE[1],
            ]);
        }

        $adminDiscoverCreate = AdminDiscoverUi::create([
            'province' => $request->province,
            'image' => $request->image,
            'content' => $request->content,
            "province_name" => Place::getNameProvince($request->province)
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverCreate
        ]);
    }


    /*
    *  Cập nhật ui admin discover
    *  
    *  @bodyQuery province mã tỉnh thành phố
    *  @bodyQuery image ảnh
    *  @bodyQuery content ảnh
    */
    public function update(Request $request, $id)
    {
        $adminDiscoverExist = AdminDiscoverUi::where('id', $id)->first();

        if ($adminDiscoverExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[0],
                'msg' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[1],
            ]);
        }

        if (isset($request->province)) {
            if (Place::getNameProvince($request->province) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_PROVINCE[0],
                    'msg' => MsgCode::INVALID_PROVINCE[1],
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PROVINCE_IS_REQUIRE[0],
                'msg' => MsgCode::PROVINCE_IS_REQUIRE[1],
            ]);
        }

        $adminDiscoverExist->update([
            'image' => $request->image != null ? $request->image : $adminDiscoverExist->image,
            'content' => $request->content != null ? $request->content : $adminDiscoverExist->content,
            'province' => ($request->province != null ? $request->province : $adminDiscoverExist) ?? $adminDiscoverExist->province,
            "province_name" => $request->province ? Place::getNameProvince($request->province) : Place::getNameProvince($adminDiscoverExist->province)
        ]);

        $adminDiscoverExist->save();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverExist
        ]);
    }

    /*
    *  Lấy một ui admin discover
    */
    public function getOne(Request $request, $id)
    {
        $adminDiscoverExist = AdminDiscoverUi::where('id', $id)->first();

        if ($adminDiscoverExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[0],
                'msg' => MsgCode::NO_ADMIN_DISCOVER_EXISTS[1],
            ]);
        }

        $adminDiscoverExist->list_discover_item = AdminDiscoverItemUi::where('admin_discover_id', $adminDiscoverExist->id)->get();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $adminDiscoverExist
        ]);
    }

    /*
    *  Xóa ui admin discover
    *  
    *  @bodyQuery list_id_discover_ui
    */
    public function delete(Request $request)
    {
        $IdDeleted = [];
        $data = null;
        if (is_array($request->list_id_discover_ui)) {
            foreach ($request->list_id_discover_ui as $bannerId) {
                $existAdminDiscover = AdminDiscoverUi::where('id', $bannerId)->first();
                if ($existAdminDiscover != null) {
                    array_push($IdDeleted, $existAdminDiscover->id);
                    $existAdminDiscover->delete();
                }
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_ID_DISCOVER_UI[0],
                'msg' => MsgCode::INVALID_LIST_ID_DISCOVER_UI[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['list_id_discover_ui_deleted' => $IdDeleted]
        ]);
    }


    /*
    *  Danh sách ui admin discover
    *  
    */
    public function getAll(Request $request)
    {
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => AdminDiscoverUi::get()
        ]);
    }
}
