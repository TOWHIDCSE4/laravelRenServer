<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\HostRankDefineCode;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MoPost;
use App\Models\Motel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Helper\ParamUtils;
use App\Helper\StatusMoPostDefineCode;
use App\Models\AdminContact;
use App\Models\AdminDiscoverItemUi;
use App\Models\AdminDiscoverUi;
use App\Models\ConfigAdmin;
use App\Models\MoPostFindMotel;
use App\Models\MoPostRoommate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Cộng đồng/Cộng đồng tìm phòng
 */

const MO_POST = "MO_POST"; //
const MO_POST_OUTSTANDING = "MO_POST_OUTSTANDING"; //
const MO_POST_FIND_MOTEL = "MO_POST_FIND_MOTEL"; //
const MO_POST_FIND_ROOMMATE = "MO_POST_FIND_ROOMMATE"; //

class HomeController extends Controller
{
    /**
     * 
     * Api home
     * 
     */
    public function homeApp(Request $request)
    {
        $outstandingMoPost = new \Illuminate\Database\Eloquent\Collection;
        $limit = 10;

        $vipPost = MoPost::where('mo_posts.status', StatusMoPostDefineCode::COMPLETED)
            ->join('users', 'mo_posts.user_id', '=', 'users.id')
            ->where('users.host_rank', HostRankDefineCode::VIP)
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->select('mo_posts.*')
            ->orderBy('created_at', 'desc')->take($limit)->get();

        $listIdPostVip = clone  $vipPost;
        $listIdPostVip = $listIdPostVip->pluck('id')->toArray();
        $countListOutstanding = count($listIdPostVip);

        $prestigePost = MoPost::where('mo_posts.status', StatusMoPostDefineCode::COMPLETED)
            ->join('users', 'mo_posts.user_id', '=', 'users.id')
            ->where('users.host_rank', HostRankDefineCode::PRESTIGE)
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->select('mo_posts.*')
            ->orderBy('created_at', 'desc')->take($limit - $countListOutstanding)->get();

        $listIdPostPrestige = clone  $prestigePost;
        $listIdPostPrestige = $listIdPostPrestige->pluck('id')->toArray();
        $countListOutstanding = $countListOutstanding + count($prestigePost);

        $listIdOutstanding = array_merge($listIdPostPrestige, $listIdPostVip);
        $outstandingMoPost = $outstandingMoPost->merge($vipPost);
        $outstandingMoPost = $outstandingMoPost->merge($prestigePost);

        if ($countListOutstanding < $limit) {
            $addListOutstanding = MoPost::where('status', StatusMoPostDefineCode::COMPLETED)
                ->whereNotIn('id', $listIdOutstanding)
                ->when(request('province') != null, function ($query) {
                    $query->where('province', request('province'));
                })
                ->when(request('district') != null, function ($query) {
                    $query->where('district', request('district'));
                })
                ->orderBy('created_at', 'desc')->take($limit - $countListOutstanding)->get();

            $listIdAddListOutstanding = clone  $addListOutstanding;
            $listIdAddListOutstanding = $listIdAddListOutstanding->pluck('id')->toArray();

            $listIdOutstanding = array_merge($listIdPostPrestige, $listIdPostVip, $listIdAddListOutstanding);

            $outstandingMoPost = $outstandingMoPost->merge($addListOutstanding);
        }

        $newMoPost = MoPost::where('status', StatusMoPostDefineCode::COMPLETED)
            ->whereNotIn('id', $listIdOutstanding)
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->orderBy('created_at', 'desc')->take(10)->get();

        $moPostFindMotels = MoPostFindMotel::where('status', StatusMoPostDefineCode::COMPLETED)
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->orderBy('created_at', 'desc')->take(10)->get();

        $moPostFindRoommates = MoPostRoommate::where('status', StatusMoPostDefineCode::COMPLETED)
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->orderBy('created_at', 'desc')->take(10)->get();

        $banners = DB::table('admin_banners')
            ->select('image_url', 'title', 'action_link')
            ->take(10)
            ->orderBy('created_at', 'desc')
            ->get();
        $adminContacts = AdminContact::first();
        $adminDiscovers = AdminDiscoverUi::take(10)
            // ->select('id', 'province', 'province_name', 'image', 'content')
            ->orderBy('created_at', 'desc')
            ->get();

        $listServiceSell = DB::table('service_sells')
            ->take(10)
            ->select('id', 'service_sell_icon', 'name')
            ->orderBy('created_at', 'desc')
            ->get();

        $listCategoryServiceSell = DB::table('category_service_sells')->select('id', 'name', 'image')->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'layouts' => [
                [
                    "title" => "Bài viết nổi bật",
                    "type" => MO_POST_OUTSTANDING,
                    "list" => $outstandingMoPost
                ],
                [
                    "title" => "Bài đăng mới",
                    "type" => MO_POST,
                    "list" => $newMoPost
                ]
            ],
            "mo_post_find_motels" => $moPostFindMotels,
            "mo_post_find_roommates" => $moPostFindRoommates,
            'banners' => $banners,
            'admin_contacts' => $adminContacts,
            'admin_discovers' => $adminDiscovers,
            'list_service_sell' => $listServiceSell,
            'list_category_service_sell' => $listCategoryServiceSell,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ]);
    }

    /**
     * 
     * Api home
     * 
     */
    public function introApp(Request $request)
    {
        $introApp = ConfigAdmin::first()->intro_app ?? '1.0.0';


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $introApp,
        ]);
    }

    /**
     * Tìm kiếm phòng
     * 
     * @queryParam type_motel int
     * @queryParam price_from double
     * @queryParam price_to double
     * @queryParam is_verify boolean
     * @queryParam sort [created_at, max_price, min_price]
     * @queryParam gender [0 male, 1 female, 2 all]
     * @queryParam descending boolean
     * @queryParam search string 
     * @queryParam province int mã tỉnh thành phố
     * @queryParam district int mã huyện
     * @queryParam wards int mã xã, thị trấn
     * 
     */
    public function search(Request $request)
    {
        $type_motel = $request->type_motel;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $is_verify = $request->is_verify;
        $gender = $request->gender;
        $descending = $request->descending ? 'desc' : 'asc';
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $data = Motel::when(!empty($type_motel), function ($query) use ($type_motel) {
            $query->where('type_motel', $type_motel);
        })
            ->when(!empty($is_verify), function ($query) use ($is_verify) {
                $query->where('status', $is_verify);
            })
            ->when(!empty($gender), function ($query) use ($gender) {
                $query->where('gender', $gender);
            })
            ->when(!empty($price_from) && !empty($price_to), function ($query) use ($price_from, $price_to) {
                $query->where([
                    ['money', '>=', $price_from],
                    ['money', '<=', $price_to],
                ]);
            })
            ->when(!empty($price_from), function ($query) use ($price_from) {
                $query->where('money', '>=', $price_from);
            })
            ->when(!empty($price_to), function ($query) use ($price_to) {
                $query->where('money', '<=', $price_to);
            })
            ->when(!empty($request->province), function ($query) use ($request) {
                $query->where('province', $request->province);
            })
            ->when(!empty($request->district), function ($query) use ($request) {
                $query->where('district', $request->district);
            })
            ->when(!empty($request->wards), function ($query) use ($request) {
                $query->where('wards', $request->wards);
            })
            ->when(request('has_wc') != null, function ($query) {
                $query->where('has_wc', filter_var(request('has_wc'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wifi') != null, function ($query) {
                $query->where('has_wifi', filter_var(request('has_wifi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_park') != null, function ($query) {
                $query->where('has_park', filter_var(request('has_park'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_window') != null, function ($query) {
                $query->where('has_window', filter_var(request('has_window'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_security') != null, function ($query) {
                $query->where('has_security', filter_var(request('has_security'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_free_move') != null, function ($query) {
                $query->where('has_free_move', filter_var(request('has_free_move'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_own_owner') != null, function ($query) {
                $query->where('has_own_owner', filter_var(request('has_own_owner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_air_conditioner') != null, function ($query) {
                $query->where('has_air_conditioner', filter_var(request('has_air_conditioner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_water_heater') != null, function ($query) {
                $query->where('has_water_heater', filter_var(request('has_water_heater'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen') != null, function ($query) {
                $query->where('has_kitchen', filter_var(request('has_kitchen'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_fridge') != null, function ($query) {
                $query->where('has_fridge', filter_var(request('has_fridge'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_washing_machine') != null, function ($query) {
                $query->where('has_washing_machine', filter_var(request('has_washing_machine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mezzanine') != null, function ($query) {
                $query->where('has_mezzanine', filter_var(request('has_mezzanine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_bed') != null, function ($query) {
                $query->where('has_bed', filter_var(request('has_bed'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wardrobe') != null, function ($query) {
                $query->where('has_wardrobe', filter_var(request('has_wardrobe'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tivi') != null, function ($query) {
                $query->where('has_tivi', filter_var(request('has_tivi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pet') != null, function ($query) {
                $query->where('has_pet', filter_var(request('has_pet'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_balcony') != null, function ($query) {
                $query->where('has_balcony', filter_var(request('has_balcony'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(!empty($sortBy) && MoPost::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when(!empty($request->search), function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data,
        ]);
    }

    /**
     * Lấy chi tiết khám phá
     */
    public function getDiscover(Request $request, $id)
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

        $list_discover_item = AdminDiscoverItemUi::where('admin_discover_id', $adminDiscoverExist->id)->get();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $list_discover_item
        ]);
    }

    /**
     * Lấy bài đăng vị trí gần nhất
     */
    public function getPostLocationNearest(Request $request)
    {
        $limit = $request->limit ?: 20;

        $handleSublocality = explode('. ', $request->sublocality);
        $handleSubadministrativeArea = explode('. ', $request->subadministrative_area);

        $sublocality = isset($handleSublocality[1]) ? $handleSublocality[1] : $handleSublocality[0];
        $subadministrativeArea = isset($handleSubadministrativeArea[1]) ? $handleSubadministrativeArea[1] : $handleSubadministrativeArea[0];
        $administrativeArea = $request->administrative_area;
        $countListPost = 0;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $moPosts = MoPost::where('mo_posts.status', StatusMoPostDefineCode::COMPLETED)
            ->when($administrativeArea != null, function ($query) use ($administrativeArea) {
                $query->where('province_name', 'LIKE', '%' . $administrativeArea . '%');
            })
            ->when($subadministrativeArea != null, function ($query) use ($subadministrativeArea) {
                $query->where('district_name', 'LIKE', '%' . $subadministrativeArea . '%');
            })
            ->when($sublocality != null, function ($query) use ($sublocality) {
                $query->where('wards_name', 'LIKE', '%' . $sublocality . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        $countListPost = count($moPosts);

        if ($countListPost <= 20) {
            $listIdMoPost = (clone $moPosts)->pluck('id')->toArray();

            $moPostTemp = MoPost::where([
                ['mo_posts.status', StatusMoPostDefineCode::COMPLETED],
                ['mo_posts.id', '<>', $listIdMoPost],
            ])
                ->when($administrativeArea != null, function ($query) use ($administrativeArea) {
                    $query->where('province_name', 'LIKE', '%' . $administrativeArea . '%');
                })
                ->when($subadministrativeArea != null, function ($query) use ($subadministrativeArea) {
                    $query->where('district_name', 'LIKE', '%' . $subadministrativeArea . '%');
                })
                ->orderBy('created_at', 'desc')
                ->take(20 - $countListPost)
                ->get();

            $moPosts = $moPosts->merge($moPostTemp);
            $countListPost = count($moPosts);

            if ($countListPost <= 20) {
                $moPostTemp = MoPost::where([
                    ['mo_posts.status', StatusMoPostDefineCode::COMPLETED],
                    ['mo_posts.id', '<>', $listIdMoPost],
                ])
                    ->when($administrativeArea != null, function ($query) use ($administrativeArea) {
                        $query->where('province_name', 'LIKE', '%' . $administrativeArea . '%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->take(20 - $countListPost)
                    ->get();

                $moPosts = $moPosts->merge($moPostTemp);
                $countListPost = count($moPosts);
            }
        }

        $moPosts = $moPosts->paginate($limit);



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPosts,
        ]);
    }
}
