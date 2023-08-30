<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\Motel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Helper\ParamUtils;
use App\Helper\Place;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Models\MoPost;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group Admin/Quản lý/Phòng trọ
 */

class MotelController extends Controller
{
    /**
     * 
     * Danh cách phòng trọ
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $search = $request->search;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $hasContract = isset($request->has_contract) ? filter_var($request->has_contract, FILTER_VALIDATE_BOOLEAN) : null;
        $pendingContract = isset($request->pending_contract) ? filter_var($request->pending_contract, FILTER_VALIDATE_BOOLEAN) : null;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $motels = Motel::sortByRelevance(true)
            ->where(function ($query) use ($request) {
                $isHaveTower = filter_var($request->is_have_tower, FILTER_VALIDATE_BOOLEAN) ?? false;
                if ($isHaveTower && $request->tower_id != null) {
                    $query->where('motels.tower_id', $request->tower_id);
                } else {
                    $query->whereNull('motels.tower_id');
                }
            })
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user_id);
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->type != null, function ($query) {
                $query->where('motels.type', request('type'));
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('motels.status', $request->status);
            })
            ->when($request->province != null, function ($query) {
                $query->where('motels.province', request('province'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('motels.district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('motels.wards', request('wards'));
            })
            ->when(isset($hasContract) || isset($pendingContract), function ($query) use ($hasContract, $pendingContract) {
                if ($pendingContract == true) {
                    $query->join('contracts', 'motels.id', 'contracts.motel_id');
                    $query->whereIn('contracts.status', [StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                    $query->whereIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);
                    $query->select('motels.*');
                } else if ($hasContract) {
                    $query->join('contracts', 'motels.id', 'contracts.motel_id');
                    $query->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                    ]);
                    $query->select('motels.*');
                } else {
                    $query->WhereNotIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_DRAFT]);
                    $listIdMotelHasContract = DB::table('contracts')
                        ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                        ->pluck('motel_id');
                    $query->whereNotIn('motels.id', $listIdMotelHasContract);
                }
                $query->distinct('motels.id');
            })
            ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct('motels.id')
            ->groupBy('motels.id')
            ->search($search)
            ->get()
            ->each(function ($items) {
                $items->append('host');
            });

        $motels = $motels->paginate($limit);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels,
        ], 200);
    }


    /**
     * 
     * Thêm 1 phòng trọ
     * 
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam motel_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam sex int  0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
     * @bodyParam deposit double đặt cọc 
     * @bodyParam electric_money double tiền điện - 0 là free
     * @bodyParam water_money double tiền nước  tiền nước - 0 là free
     * @bodyParam has_wifi có wifi ko 
     * @bodyParam wifi_money có
     * @bodyParam has_park có
     * @bodyParam park_money có
     * @bodyParam province có
     * @bodyParam district có
     * @bodyParam wards có
     * @bodyParam address_detail có 
     * @bodyParam has_wc có
     * @bodyParam has_window có
     * @bodyParam has_security có
     * @bodyParam has_free_move có
     * @bodyParam has_own_owner có
     * @bodyParam has_air_conditioner có
     * @bodyParam has_water_heater có 
     * @bodyParam has_kitchen có
     * @bodyParam has_fridge có
     * @bodyParam has_washing_machine có
     * @bodyParam has_mezzanine có
     * @bodyParam has_bed có
     * @bodyParam has_wardrobe có
     * @bodyParam has_tivi có
     * @bodyParam has_pet có
     * @bodyParam has_balcony có
     * @bodyParam hour_open có
     * @bodyParam minute_open có
     * @bodyParam hour_close có
     * @bodyParam minute_close có
     * 
     * 
     */
    public function create(Request $request)
    {

        $motel_created = Motel::create([
            "user_id" => $request->user->id,
            "type"  => $request->type,
            "status"  => $request->status,
            "phone_number"  => $request->phone_number,
            "title" => $request->title,
            "description"  => $request->description,
            "motel_name"  => $request->motel_name,
            "capacity"  => $request->capacity,
            "sex"  => $request->sex,
            "area"  => $request->area,
            "money"  => $request->money,
            "deposit" => $request->deposit,
            "electric_money" => $request->electric_money,
            "water_money"  => $request->water_money,
            "has_wifi"  => $request->has_wifi,
            "wifi_money" => $request->wifi_money,
            "has_park" => $request->has_park,
            "is_room_hidden" => $request->is_room_hidden,
            "park_money" => $request->park_money,
            "province" => $request->province,
            "video_link" => $request->video_link,
            "district" => $request->district,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "wards" => $request->wards,
            "address_detail" => $request->address_detail,
            "has_wc" => $request->has_wc,
            "has_window" => $request->has_window,
            "has_security" => $request->has_security,
            "has_free_move" => $request->has_free_move,
            "has_own_owner" => $request->has_own_owner,
            "has_air_conditioner" => $request->has_air_conditioner,
            "has_water_heater" => $request->has_water_heater,
            "has_kitchen" => $request->has_kitchen,
            "has_fridge" => $request->has_fridge,
            "has_washing_machine" => $request->has_washing_machine,
            "has_mezzanine" => $request->has_mezzanine,
            "has_bed" => $request->has_bed,
            "has_wardrobe" => $request->has_wardrobe,
            "has_tivi" => $request->has_tivi,
            "has_pet" => $request->has_pet,
            "has_balcony" => $request->has_balcony,
            "has_finger_print" => $request->has_finger_print,
            "has_kitchen_stuff" => $request->has_kitchen_stuff,
            "has_table" => $request->has_table,
            "has_picture" => $request->has_picture,
            "has_decorative_lights" => $request->has_decorative_lights,
            "has_tree" => $request->has_tree,
            "has_pillow" => $request->has_pillow,
            "has_mattress" => $request->has_mattress,
            "has_shoes_rasks" => $request->has_shoes_rasks,
            "has_curtain" => $request->has_curtain,
            "has_mirror" => $request->has_mirror,
            "has_sofa" => $request->has_sofa,
            "has_ceiling_fans" => $request->has_ceiling_fans,
            "hour_open" => $request->hour_open,
            "minute_open" => $request->minute_open,
            "hour_close" => $request->hour_close,
            "minute_close" => $request->minute_close,
            "number_floor" => $request->number_floor,
            "used_at" => Helper::getTimeNowDateTime(),
            "quantity_vehicle_parked" => $request->quantity_vehicle_parked,
            "images" => json_encode($request->images)
        ]);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Motel::where('id', '=',   $motel_created->id)->first(),
        ]);
    }


    /**
     * Thong tin 1 phòng trọ
     * 
     */
    public function getOne(Request $request)
    {

        $motel_id = request("motel_id");

        $motelExists = Motel::where(
            'id',
            $motel_id
        )->first();

        if ($motelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Motel::where('id', '=',   $motelExists->id)->first(),
        ], 200);
    }

    /**
     * Cập nhật 1 phòng trọ
     * 
     * @bodyParam type int  0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho 
     * @bodyParam status int  0 chờ duyệt, 1 bị từ chối, 2 đồng ý
     * @bodyParam phone_number string số người liên hệ cho thuê
     * @bodyParam title string tiêu đề
     * @bodyParam description string nội dung mô tả
     * @bodyParam motel_name string số phòng
     * @bodyParam capacity int sức chứa người/phòng
     * @bodyParam sex int  0 tất cả, 1 nam , 2 nữ
     * @bodyParam area double diện tích m2
     * @bodyParam money double số tiền thuê vnd/ phòng
     * @bodyParam deposit double đặt cọc 
     * @bodyParam electric_money double tiền điện - 0 là free
     * @bodyParam water_money double tiền nước  tiền nước - 0 là free
     * @bodyParam has_wifi có wifi ko 
     * @bodyParam wifi_money có
     * @bodyParam has_park có
     * @bodyParam park_money có
     * @bodyParam province có
     * @bodyParam district có
     * @bodyParam wards có
     * @bodyParam address_detail có 
     * @bodyParam has_wc có
     * @bodyParam has_window có
     * @bodyParam has_security có
     * @bodyParam has_free_move có
     * @bodyParam has_own_owner có
     * @bodyParam has_air_conditioner có
     * @bodyParam has_water_heater có 
     * @bodyParam has_kitchen có
     * @bodyParam has_fridge có
     * @bodyParam has_washing_machine có
     * @bodyParam has_mezzanine có
     * @bodyParam has_bed có
     * @bodyParam has_wardrobe có
     * @bodyParam has_tivi có
     * @bodyParam has_pet có
     * @bodyParam has_balcony có
     * @bodyParam hour_open có
     * @bodyParam minute_open có
     * @bodyParam hour_close có
     * @bodyParam minute_close có
     * 
     */
    public function update(Request $request)
    {

        $motel_id = request("motel_id");

        $motelExists = Motel::where(
            'id',
            $motel_id
        )->first();

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $motelPostExists = MoPost::where('motel_id', $motel_id)
            ->first();


        $motelExists->update(Helper::removeItemArrayIfNullValue(
            [
                "type"  => $request->type,
                "status"  => $request->status ?? 0,
                "phone_number"  => $request->phone_number,
                "title" => $request->title,
                "description"  => $request->description,
                "motel_name"  => $request->motel_name,
                "capacity"  => $request->capacity,
                "video_link" => $request->video_link,
                "sex"  => $request->sex,
                "area"  => $request->area,
                "money"  => $request->money,
                "deposit" => $request->deposit,
                "electric_money" => $request->electric_money,
                "water_money"  => $request->water_money,
                "has_wifi"  => $request->has_wifi,
                "is_room_hidden" => $request->is_room_hidden,
                "wifi_money" => $request->wifi_money,
                "has_park" => $request->has_park,
                "park_money" => $request->park_money,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
                "address_detail" => $request->address_detail,
                "has_wc" => $request->has_wc,
                "has_window" => $request->has_window,
                "has_security" => $request->has_security,
                "has_free_move" => $request->has_free_move,
                "has_own_owner" => $request->has_own_owner,
                "has_air_conditioner" => $request->has_air_conditioner,
                "has_water_heater" => $request->has_water_heater,
                "has_kitchen" => $request->has_kitchen,
                "has_fridge" => $request->has_fridge,
                "has_washing_machine" => $request->has_washing_machine,
                "has_mezzanine" => $request->has_mezzanine,
                "has_bed" => $request->has_bed,
                "has_wardrobe" => $request->has_wardrobe,
                "has_tivi" => $request->has_tivi,
                "has_pet" => $request->has_pet,
                "has_balcony" => $request->has_balcony,
                "has_finger_print" => $request->has_finger_print ??  $motelExists->has_finger_print,
                "has_kitchen_stuff" => $request->has_kitchen_stuff ?? $motelExists->has_kitchen_stuff,
                "has_table" => $request->has_table ??  $motelExists->has_table,
                "has_picture" => $request->has_picture ?? $motelExists->has_picture,
                "has_decorative_lights" => $request->has_decorative_lights ?? $motelExists->has_decorative_lights,
                "has_tree" => $request->has_tree ??  $motelExists->has_tree,
                "has_pillow" => $request->has_pillow ??  $motelExists->has_pillow,
                "has_mattress" => $request->has_mattress ?? $motelExists->has_mattress,
                "has_shoes_rasks" => $request->has_shoes_rasks ??  $motelExists->has_shoes_rasks,
                "has_curtain" => $request->has_curtain ??  $motelExists->has_curtain,
                "has_mirror" => $request->has_mirror ?? $motelExists->has_mirror,
                "has_sofa" => $request->has_sofa ?? $motelExists->has_sofa,
                "has_ceiling_fans" => $request->has_ceiling_fans ??  $motelExists->has_ceiling_fans,
                "hour_open" => $request->hour_open,
                "minute_open" => $request->minute_open,
                "hour_close" => $request->hour_close,
                "minute_close" => $request->minute_close,
                "quantity_vehicle_parked" => $request->quantity_vehicle_parked,
                "number_floor" => $request->number_floor,
                "admin_verified" => filter_var($request->admin_verified ?: false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                "images" => json_encode($request->images)
            ]
        ));
        if ($motelExists != null) {
            if ($request->user->is_admin == true) {
                $adminVerified = true;
            } else if ($request->user->host_rank == HostRankDefineCode::VIP) {
                $adminVerified = true;
            } else {
                $adminVerified = $motelExists->admin_verified;
            }
        }

        DB::beginTransaction();
        try {
            $motelPostExists->update(
                [
                    "type"  => $request->type != null ? $request->type : $motelPostExists->type,
                    "phone_number"  => $request->phone_number != null ? $request->phone_number : $motelPostExists->phone_number,
                    "title" => $request->title != null ? $request->title : $motelPostExists->title,
                    "description"  => $request->description != null ? $request->description : $motelPostExists->description,
                    "motel_name"  => $request->motel_name != null ? $request->motel_name : $motelPostExists->motel_name,
                    "capacity"  => $request->capacity != null ? $request->capacity : $motelPostExists->capacity,
                    "sex"  => $request->sex != null ? $request->sex : $motelPostExists->sex,
                    "link_video" => $request->link_video,
                    "area"  => $request->area != null ? $request->area : $motelPostExists->area,
                    "money"  => $request->money != null ? $request->money : $motelPostExists->money,
                    "deposit" => $request->deposit != null ? $request->deposit : $motelPostExists->deposit,
                    "electric_money" => $request->electric_money != null ? $request->electric_money : $motelPostExists->electric_money,
                    "water_money"  => $request->water_money != null ? $request->water_money : $motelPostExists->water_money,
                    "has_wifi"  => $request->has_wifi != null ? $request->has_wifi : $motelPostExists->has_wifi,
                    "wifi_money" => $request->wifi_money != null ? $request->wifi_money : $motelPostExists->wifi_money,
                    "has_park" => $request->has_park != null ? $request->has_park : $motelPostExists->has_park,
                    "park_money" => $request->park_money != null ? $request->park_money : $motelPostExists->park_money,
                    "province" => $request->province != null ? $request->province : $motelPostExists->province,
                    "district" => $request->district != null ? $request->district : $motelPostExists->district,
                    "wards" => $request->wards != null ? $request->wards : $motelPostExists->wards,
                    "province_name" => Place::getNameProvince($request->province != null ? $request->province : $motelPostExists->province),
                    "district_name" => Place::getNameDistrict($request->district != null ? $request->district : $motelPostExists->district),
                    "wards_name" => Place::getNameWards($request->wards != null ? $request->wards : $motelPostExists->wards),
                    "address_detail" => $request->address_detail,
                    "has_wc" => $request->has_wc != null ? $request->has_wc : $motelPostExists->has_wc,
                    "has_window" => $request->has_window != null ? $request->has_window : $motelPostExists->has_window,
                    "has_security" => $request->has_security != null ? $request->has_security : $motelPostExists->has_security,
                    "has_free_move" => $request->has_free_move != null ? $request->has_free_move : $motelPostExists->has_free_move,
                    "has_own_owner" => $request->has_own_owner != null ? $request->has_own_owner : $motelPostExists->has_own_owner,
                    "has_air_conditioner" => $request->has_air_conditioner != null ? $request->has_air_conditioner : $motelPostExists->has_air_conditioner,
                    "has_water_heater" => $request->has_water_heater != null ? $request->has_water_heater : $motelPostExists->has_water_heater,
                    "has_kitchen" => $request->has_kitchen != null ? $request->has_kitchen : $motelPostExists->has_kitchen,
                    "has_fridge" => $request->has_fridge != null ? $request->has_fridge : $motelPostExists->has_fridge,
                    "has_washing_machine" => $request->has_washing_machine != null ? $request->has_washing_machine : $motelPostExists->has_washing_machine,
                    "has_mezzanine" => $request->has_mezzanine != null ? $request->has_mezzanine : $motelPostExists->has_mezzanine,
                    "has_bed" => $request->has_bed != null ? $request->has_bed : $motelPostExists->has_bed,
                    "has_wardrobe" => $request->has_wardrobe != null ? $request->has_wardrobe : $motelPostExists->has_wardrobe,
                    "has_tivi" => $request->has_tivi != null ? $request->has_tivi : $motelPostExists->has_tivi,
                    "has_pet" => $request->has_pet != null ? $request->has_pet : $motelPostExists->has_pet,
                    "has_balcony" => $request->has_balcony != null ? $request->has_balcony : $motelPostExists->has_balcony,
                    "has_finger_print" => $request->has_finger_print != null ? $request->has_finger_print : $motelPostExists->has_finger_print,
                    "has_kitchen_stuff" => $request->has_kitchen_stuff != null ? $request->has_kitchen_stuff : $motelPostExists->has_kitchen_stuff,
                    "has_table" => $request->has_table != null ? $request->has_table : $motelPostExists->has_table,
                    "has_picture" => $request->has_picture != null ? $request->has_picture : $motelPostExists->has_picture,
                    "has_decorative_lights" => $request->has_decorative_lights != null ? $request->has_decorative_lights : $motelPostExists->has_decorative_lights,
                    "has_tree" => $request->has_tree != null ? $request->has_tree : $motelPostExists->has_tree,
                    "has_pillow" => $request->has_pillow != null ? $request->has_pillow : $motelPostExists->has_pillow,
                    "has_mattress" => $request->has_mattress != null ? $request->has_mattress : $motelPostExists->has_mattress,
                    "has_shoes_rasks" => $request->has_shoes_rasks != null ? $request->has_shoes_rasks : $motelPostExists->has_shoes_rasks,
                    "has_curtain" => $request->has_curtain != null ? $request->has_curtain : $motelPostExists->has_curtain,
                    "has_mirror" => $request->has_mirror != null ? $request->has_mirror : $motelPostExists->has_mirror,
                    "has_sofa" => $request->has_sofa != null ? $request->has_sofa : $motelPostExists->has_sofa,
                    "has_ceiling_fans" => $request->has_ceiling_fans != null ? $request->has_ceiling_fans : $motelPostExists->has_ceiling_fans,
                    "hour_open" => $request->hour_open != null ? $request->hour_open : $motelPostExists->hour_open,
                    "minute_open" => $request->minute_open != null ? $request->minute_open : $motelPostExists->minute_open,
                    "hour_close" => $request->hour_close != null ? $request->hour_close : $motelPostExists->hour_close,
                    "minute_close" => $request->minute_close != null ? $request->minute_close : $motelPostExists->minute_close,
                    "number_floor" => $request->number_floor != null ? $request->number_floor : $motelPostExists->number_floor,
                    "quantity_vehicle_parked" => $request->quantity_vehicle_parked != null ? $request->quantity_vehicle_parked : $motelPostExists->quantity_vehicle_parked,
                    "furniture" =>  $request->furniture != null ? json_encode($request->furniture) : $motelPostExists->furniture,
                    "images" => $request->images != null ? json_encode($request->images) : $motelPostExists->images,
                    "money_commission_admin" => $request->money_commission_admin != null ? $request->money_commission_admin : $motelPostExists->money_commission_admin,
                    "money_commission_user" => $request->user->is_admin == true ? $request->money_commission_user : $motelExists->money_commission_user,
                    "admin_verified" => $adminVerified,
                    "percent_commission" => $request->percent_commission != null ? $request->percent_commission : $motelPostExists->percent_commission,
                    "percent_commission_collaborator" => $request->percent_commission_collaborator != null ? $request->percent_commission_collaborator : $motelPostExists->percent_commission_collaborator
                ]
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Motel::where('id', '=',   $motelExists->id)->first(),
        ]);
    }

    /**
     * Xóa 1 phòng trọ
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $motel_id = request("motel_id");
        $motelExists = Motel::where(
            'id',
            $motel_id
        )->first();
        if ($motelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ], 404);
        }

        $idDeleted = $motelExists->id;
        $motelExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
