<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Helper\ServiceUnitDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Motel;
use App\Models\Service;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    /**
     * Danh cách dịch vụ
     */
    public function getAll(Request $request)
    {
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Service::where('user_id', $request->user->id)->get(),
        ]);
    }


    /**
     * Thêm 1 dịch vụ
     * 
     * @bodyParam user_id int id user
     * @bodyParam service_name string tên dịch vụ
     * @bodyParam service_icon string icon dịch vụ
     * @bodyParam service_unit double phí dịch vụ cho mỗi đơn vị
     * @bodyParam service_charge double phí dịch vụ cho mỗi đơn vị
     * @bodyParam type_unit int loại đơn vị sẽ tính phí dựa theo 
     * 
     */
    public function create(Request $request)
    {
        $Service = Service::where([['user_id', $request->user->id], ['service_name', $request->service_name]])->first();

        if ($Service  != null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => true,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ]);
        }

        if (ServiceUnitDefineCode::getServiceUnitCode($request->type_unit, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
            ]);
        }

        $service_created = Service::create([
            "user_id" => $request->user->id,
            "service_name"  => $request->service_name,
            "service_icon"  => $request->service_icon,
            "service_unit"  => $request->service_unit,
            "service_charge" => $request->service_charge,
            "note" => $request->note,
            "type_unit" => $request->type_unit,
        ]);

        // $listMotelId = Motel::where('user_id', $request->user->id)->pluck('id')->toArray();

        // foreach ($listMotelId as $motelId) {
        //     MoService::create([
        //         "motel_id" => $motelId,
        //         "service_name"  => $request->service_name,
        //         "service_icon"  => $request->service_icon,
        //         "service_unit"  => $request->service_unit,
        //         "service_charge" => $request->service_charge,
        //         "note" => $request->note,
        //         "type_unit" => $request->type_unit,
        //     ]);
        // }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $service_created
        ]);
    }


    /**
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $service_id = request("service_id");

        $motelExists = Service::where(
            'id',
            $service_id
        )
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Service::where('id', '=',   $motelExists->id)->get(),
        ]);
    }

    /**
     * Cập nhật 1 dịch vụ
     * 
     * @bodyParam user_id int id user
     * @bodyParam service_name string tên dịch vụ
     * @bodyParam service_icon string icon dịch vụ
     * @bodyParam service_unit double phí dịch vụ cho mỗi đơn vị
     * @bodyParam service_charge double phí dịch vụ cho mỗi đơn vị
     * @bodyParam type_unit int loại đơn vị sẽ tính phí dựa theo 
     * 
     */
    public function update(Request $request)
    {

        $service_id = request("service_id");

        $serviceExist = Service::where(
            'id',
            $service_id
        )
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        $Service = Service::where([
            ['service_name', $request->name],
            ['user_id', $request->user->id],
            ['id', '<>', $serviceExist->id]
        ])->first();

        if ($Service  != null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ]);
        }

        if (ServiceUnitDefineCode::getServiceUnitCode($request->type_unit, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
            ]);
        }



        if ($serviceExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ]);
        }

        if (ServiceUnitDefineCode::getServiceUnitCode($request->type_unit, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
            ]);
        }


        $serviceExist->update(Helper::removeItemArrayIfNullValue(
            [
                "service_name"  => $request->service_name,
                "service_icon"  => $request->service_icon,
                "service_unit"  => $request->service_unit,
                "service_charge" => $request->service_charge,
                "type_unit" => $request->type_unit,
                "note" => $request->note,
            ]
        ));

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Service::where('id', '=',   $serviceExist->id)->first()
        ]);
    }

    /**
     * Xóa 1 dịch vụ
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $service_id = request("service_id");

        $motelExists = Service::where(
            'id',
            $service_id
        )->where(function ($query) use ($request) {
            if ($request->user->is_admin != true) {
                $query->where('user_id', $request->user->id);
            }
        })
            ->first();

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ]);
        }

        $idDeleted = $motelExists->id;
        $motelExists->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
