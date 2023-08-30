<?php

namespace App\Services\Shipper\GHN;

use App\Helper\StringUtils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class GHNUtils
{


    static function getIDProvinceGHN($name)
    {
        $contents = Storage::get('location/ghn/province.json');
        $jsonFile = json_decode($contents, true);

        $name = StringUtils::convert_name_lowercase($name);


        $listProvinces = $jsonFile["data"];
        $listProvinces = array_map(function ($value) {
            $value["ProvinceName"] = StringUtils::convert_name_lowercase($value["ProvinceName"]);

            if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {

                $listExtension = array_map(function ($value2) {
                    return StringUtils::convert_name_lowercase($value2);
                }, $value["NameExtension"]);

                $value["NameExtension"] = $listExtension;
            }


            return $value;
        }, $listProvinces);


        foreach ($listProvinces as $key => $value) {
            if ($value["ProvinceName"] == $name || in_array($name, $value["NameExtension"]) == true) {
                return $value["ProvinceID"];
            }
        }

        return null;
    }

    static function getIDDistrictGHN($provinceID, $districtName)
    {
        $contents = Storage::get('location/ghn/district.json');
        $jsonFile = json_decode($contents, true);


        $districtName = StringUtils::convert_name_lowercase($districtName);

        $listDistricts = $jsonFile["data"];
        $listDistricts = array_map(function ($value) {
            $value["DistrictName"] = StringUtils::convert_name_lowercase($value["DistrictName"]);


            if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {
                $listExtension = array_map(function ($value2) {
                    return StringUtils::convert_name_lowercase($value2);
                }, $value["NameExtension"]);

                $value["NameExtension"] = $listExtension;
            }



            return $value;
        }, $listDistricts);



        foreach ($listDistricts as $key => $value) {
            if ($value["ProvinceID"] == $provinceID && ($value["DistrictName"] == $districtName ||
                isset($value["NameExtension"]) &&  in_array($districtName, $value["NameExtension"]))) {
                return $value["DistrictID"];
            }
        }

        return null;
    }


    static function getWardCodeGHN($token, $district_id, $ward_name)
    {


        $config = config('saha.shipper.list_shipper')[0];
        $ward_url = "https://online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id=$district_id";
        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $ward_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' =>  [
                        "district_id" => $district_id
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->message);
            } else {

                $listWards = $jsonResponse->data;
                $listWards = json_decode(json_encode($listWards), true);

                $listWards = array_map(function ($value) {

                    $value["WardName"] = StringUtils::convert_name_lowercase($value["WardName"]);


                    if (array_key_exists("NameExtension", $value) && count($value["NameExtension"]) > 0) {
                        $listExtension = array_map(function ($value2) {
                            return StringUtils::convert_name_lowercase($value2);
                        }, $value["NameExtension"]);

                        $value["NameExtension"] = $listExtension;
                    }



                    return $value;
                }, $listWards);


                $ward_name = StringUtils::convert_name_lowercase($ward_name);
                foreach ($listWards as $key => $value) {


                    if ($value["DistrictID"] == $district_id && ($value["WardName"] == $ward_name ||
                        isset($value["NameExtension"]) &&  in_array($ward_name, $value["NameExtension"]))) {
                        return $value["WardCode"];
                    }
                }

                return null;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            if ($e->hasResponse()) {

                $body = (string) $e->getResponse()->getBody();
                $statusCode = $e->getResponse()->getStatusCode();

                $jsonResponse = json_decode($body);

                return new Exception($jsonResponse->message);
            }
            return new Exception('error');
        } catch (Exception $e) {

            return new Exception('error');
        }
    }
}
