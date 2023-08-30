<?php

namespace App\Services;

use App\Helper\Place;
use App\Http\Controllers\Api\User\ConfigShipController;
use App\Models\MsgCode;
use App\Models\Shipment;
use App\Services\Shipper\GHN\GHNUtils;
use Exception;
use GuzzleHttp\Client as GuzzleClient;


//0 tieu chuan
//1 sieu toc

class ShipperService
{

    static function check_token_partner_id($partner_id, $token)
    {



        $data = null;
        if ((int)$partner_id  == 0) {
            $data  =   ShipperService::check_token_ghtk($token);
        }

        if ((int)$partner_id  == 1) {
            $data  =   ShipperService::check_token_ghn($token);
        }

        if ((int)$partner_id  == 2) {
            $data  =   ShipperService::check_token_viettel($token);
        }

        return $data;
    }

    static function caculate_monney_all($shipperArr)
    {

        $datas = config('saha.shipper.list_shipper');
        $listShip = [];

        foreach ($datas as $shiperInFile) {
            $partnerExists = Shipment::where('store_id', $shipperArr['store_id'])
                ->where('partner_id', $shiperInFile['id'])
                ->where('use', true)
                ->whereNotNull('token')
                ->first();

            if ($partnerExists != null) {

                array_push($listShip, [
                    'id' => $shiperInFile['id'],
                    'name' => $shiperInFile['name'],
                    'ship_speed' => $shiperInFile['ship_speed'],
                    'shipper_config' => $partnerExists
                ]);
            }
        }

        if (count($listShip) == 0) {
            return [
                'info' => "Chưa cài đặt đơn vị vận chuyển",
                'data' => []
            ];
        }

        $data = array();
        $info = null;

        foreach ($listShip as $shiperDB) {

            $partner_id = $shiperDB['shipper_config']->partner_id;
            $token = $shiperDB['shipper_config']->token;
            $ship_speed = $shiperDB['ship_speed'];
            $name = $shiperDB['name'];


            //Giao tiêu chuẩn
            $res  =   ShipperService::caculate_monney_one_partner(
                $shipperArr,
                $partner_id,
                0,
                $token
            );

            if ($res instanceof Exception) {
                $info = $res->getMessage();
            } else {
                array_push(
                    $data,
                    array(
                        "partner_id" =>    $partner_id,
                        "fee" => $res,
                        "name" => $name,
                        "ship_type" => 0
                    )
                );
            }

            // //Thêm giao nhanh
            // if ($ship_speed == true) {

            //     $res  =   ShipperService::caculate_monney_one_partner(
            //         $shipperArr,
            //         $partner_id,
            //         1,
            //         $token
            //     );

            //     if ($res instanceof Exception) {

            //         $info = $res->getMessage();
            //     } else {
            //         array_push(
            //             $data,
            //             array(
            //                 "partner_id" =>    $partner_id,
            //                 "fee" => $res,
            //                 "name" => $name . " (siêu tốc)",
            //                 "ship_type" => 1
            //             )
            //         );
            //     }
            // }
        }


        return [
            'info' => $info,
            'data' =>  $data
        ];
    }

    static function caculate_monney_one_partner($shipperArr, $partner_id, $type_ship, $token)
    {

        $data = null;
        if ($partner_id  == 0) {
            $data  =   ShipperService::caculate_monney_ghtk($shipperArr, $type_ship, $token);
        }

        if ($partner_id  == 1) {
            $data  =   ShipperService::caculate_monney_ghn($shipperArr, $type_ship, $token);
        }

        if ($partner_id  == 2) {
            $data  =   ShipperService::caculate_monney_viettel($shipperArr, $type_ship, $token);
        }


        return $data;
    }

    public static function check_token_ghtk($token)
    {
        $config = config('saha.shipper.list_shipper')[0];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]
            );

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode == 401) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function caculate_monney_ghtk($shipperArr, $type_ship, $token)
    {

        $typeShip = 'none'; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = 'none';
        } else if ($type_ship == 1) {
            $typeShip = "xteam";
        }

        return ShipperService::res_ghtk($shipperArr,  $typeShip, $token);
    }

    public static function res_ghtk($shipperArr,  $typeShip, $token)
    {
        $config = config('saha.shipper.list_shipper')[0];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'query' => [
                        'pick_province' => Place::getNameProvince($shipperArr["from_province_id"]),
                        'pick_district' => Place::getNameDistrict($shipperArr["from_district_id"]),

                        'province' => Place::getNameProvince($shipperArr["to_province_id"]),
                        'district' => Place::getNameDistrict($shipperArr["to_district_id"]),
                        'deliver_option' =>  $typeShip,
                        'weight' => $shipperArr["weight"],
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success == false) {
                return new Exception($jsonResponse->message);
            } else {
                if ($jsonResponse->fee == null || !isset($jsonResponse->fee->fee)) {

                    return new Exception("null");
                }
                return $jsonResponse->fee->fee;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 0)->update(
                            [
                                'use' => false,
                            ]
                        );
                }
                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function check_token_ghn($token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]

            );

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode == 401) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function check_token_viettel($token)
    {

        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["check_token_url"];


        $client = new GuzzleClient();
        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];
        try {
            $response = $client->request(
                'GET',
                $fee_url,
                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                ]

            );
            $data = json_decode($response->getBody());
            if ($data->status != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            //Xoa khoi danh sach van chuyen
            if ($statusCode != 200) {
                return new Exception('401');
            }

            return 'SUCCESS';
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function caculate_monney_ghn($shipperArr, $type_ship, $token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        $provinceNameFrom = Place::getNameProvince($shipperArr["from_province_id"]);

        // if ($value["ProvinceName"] == $name || in_array($name,$value["NameExtension0"])==true ) {
        $provinceNameTo = Place::getNameProvince($shipperArr["to_province_id"]);

        $provinceIdFrom = GHNUtils::getIDProvinceGHN($provinceNameFrom);
        $provinceIdTo = GHNUtils::getIDProvinceGHN($provinceNameTo);



        $districtNameFrom = Place::getNameDistrict($shipperArr["from_district_id"]);
        $districtNameTo = Place::getNameDistrict($shipperArr["to_district_id"]);

        $districtIdFrom = GHNUtils::getIDDistrictGHN($provinceIdFrom, $districtNameFrom);
        $districtIdTo = GHNUtils::getIDDistrictGHN($provinceIdTo, $districtNameTo);

        $typeShip = 2; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = 2;
        } else if ($type_ship == 1) {
            $typeShip = 1;
        }

        try {
            $response = $client->request(
                'GET',
                $fee_url,

                [
                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'query' => [
                        'from_district_id' => $districtIdFrom,
                        'to_district_id' => $districtIdTo,
                        'service_type_id' => $typeShip,

                        'weight' => $shipperArr["weight"],
                        'height' => $shipperArr["height"],
                        'length' => $shipperArr["length"],
                        'width' => $shipperArr["width"],
                    ]

                ]

            );


            $body = (string) $response->getBody();


            $jsonResponse = json_decode($body);

            if ($jsonResponse->message != "Success") {

                return new Exception($jsonResponse->message);
            } else {

                if ($jsonResponse->data == null || !isset($jsonResponse->data->total)) {
                    return new Exception("null");
                }

                return $jsonResponse->data->total;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);


                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse->message);
            }


            return new Exception('error');
        } catch (Exception $e) {
            return new Exception('error');
        }
    }

    public static function caculate_monney_viettel($shipperArr, $type_ship, $token)
    {

        $config = config('saha.shipper.list_shipper')[2];
        $fee_url = $config["fee_url"];


        $client = new GuzzleClient();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];


        $from_province_id = $shipperArr["from_province_id"];
        $from_district_id = $shipperArr["from_district_id"];

        $to_province_id = $shipperArr["to_province_id"];
        $to_district_id = $shipperArr["to_district_id"];


        $typeShip = "VCN"; //tiêu chuẩn

        if ($type_ship == 0) {
            $typeShip = "VCN";
        } else if ($type_ship == 1) {
            $typeShip = "VHT";
        }


        // dd([
        //     "PRODUCT_WEIGHT" => 7500,
        //     "PRODUCT_PRICE" => 200000,

        //     "ORDER_SERVICE_ADD" => "",
        //     "ORDER_SERVICE" => $typeShip,
        //     "SENDER_PROVINCE" =>   $from_province_id,
        //     "SENDER_DISTRICT" =>   $from_district_id,
        //     "RECEIVER_PROVINCE" => $to_province_id,
        //     "RECEIVER_DISTRICT" =>  $to_district_id,
        //     "PRODUCT_TYPE" => "HH",
        //     "NATIONAL_TYPE" => 1
        // ]);

        try {
            $response = $client->post(

                $fee_url,

                [

                    'headers' => $headers,
                    'timeout'         => 5,
                    'connect_timeout' => 5,
                    'json' => [
                        "PRODUCT_WEIGHT" => 7500,
                        "PRODUCT_PRICE" => 200000,

                        "ORDER_SERVICE_ADD" => "",
                        "ORDER_SERVICE" => $typeShip,
                        "SENDER_PROVINCE" =>   $from_province_id,
                        "SENDER_DISTRICT" =>   $from_district_id,
                        "RECEIVER_PROVINCE" => $to_province_id,
                        "RECEIVER_DISTRICT" =>  $to_district_id,
                        "PRODUCT_TYPE" => "HH",
                        "NATIONAL_TYPE" => 1
                    ]

                ]

            );


            $body = (string) $response->getBody();


            $jsonResponse = json_decode($body);



            if ($jsonResponse->data == null) {

                return new Exception($jsonResponse->message);
            } else {


                if ($jsonResponse->data == null || !isset($jsonResponse->data->MONEY_TOTAL_FEE)) {
                    return new Exception("null");
                }

                return $jsonResponse->data->MONEY_TOTAL_FEE;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                //Xoa khoi danh sach van chuyen
                if ($statusCode == 401) {
                    Shipment::where('store_id', $shipperArr["store_id"])
                        ->where('partner_id', 1)->update(
                            [
                                'use' => false,
                            ]
                        );
                }

                return new Exception($jsonResponse);
            }


            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }
}
