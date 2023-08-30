<?php

namespace App\Services\Shipper;

use App\Helper\Place;
use App\Models\Store;
use App\Services\Shipper\GHN\GHNUtils;
use Exception;
use GuzzleHttp\Client;


class SendOrderService
{

    public static function send_order_ghtk($orderDB, $addressPickupExists, $token)
    {

        $config = config('saha.shipper.list_shipper')[0];
        $send_order_url = $config["send_order_url"];

        //Dữ liệu up
        $products = array();
        foreach ($orderDB->line_items as $line_item) {
            array_push(
                $products,
                [
                    "name" => $line_item->product->name,
                    "price" => $line_item->item_price,
                    "weight" => 0.1,
                    "quantity" => $line_item->quantity,
                    "product_code" => $line_item->product->id
                ]

            );
        }

        $order = [
            "id" => $orderDB->order_code,
            "pick_name" => $addressPickupExists->name,
            "pick_address" => $addressPickupExists->address_detail,

            "pick_tel" => $addressPickupExists->phone,

            "pick_province" => Place::getNameProvince($addressPickupExists->province),
            "pick_district" => Place::getNameDistrict($addressPickupExists->district),
            "pick_ward" => Place::getNameWards($addressPickupExists->wards),

            "tel" => $orderDB->customer_phone,
            "name" => $orderDB->customer_name,
            "address" => $orderDB->customer_address_detail,
            "province" => Place::getNameProvince($orderDB->customer_province),
            "district" => Place::getNameDistrict($orderDB->customer_district),
            "ward" => Place::getNameWards($orderDB->customer_wards),
            "pick_money" =>  $orderDB->total_final -    $orderDB->total_shipping_fee,
            "value" =>  $orderDB->total_before_discount,
            "hamlet" => "Khác",
            // "is_freeship"=> "1",
            // "pick_date"=> "2016-09-30"
        ];



        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => [
                        'order' => $order,
                        'products' => $products,
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->success == false) {
                return new Exception($jsonResponse->message);
            } else {
                //   {#1640
                //   +"success": true
                //   +"message": "Các đơn hàng đã được add vào hệ thống GHTK thành công. Thông tin đơn hàng thành công được trả về trong trường success_orders."
                //   +"order": {#1638
                //     +"partner_id": "1812211JLY75H4"
                //     +"label": "S19328958.HN11.VP11B.651117667"
                //     +"area": 1
                //     +"fee": 22000
                //     +"insurance_fee": 0
                //     +"estimated_pick_time": "Chiều 2022-01-14"
                //     +"estimated_deliver_time": "Sáng 2022-01-15"
                //     +"products": []
                //     +"status_id": 2
                //     +"tracking_id": 651117667
                //     +"sorting_code": "HN11.VP11B"
                //     +"is_xfast": 0
                //   }
                //   +"warning_message": ""
                // }

                return [
                    'code' => $jsonResponse->order->label,
                    'fee' => $jsonResponse->order->fee,
                ];
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

    public static function send_order_ghn($orderDB, $addressPickupExists, $token)
    {

        $config = config('saha.shipper.list_shipper')[1];
        $send_order_url = $config["send_order_url"];

        //Dữ liệu up
        $items = array();
        foreach ($orderDB->line_items as $line_item) {
            array_push(
                $items,
                [
                    "name" => $line_item->product->name,
                    "price" => $line_item->item_price,
                    "weight" => 1,
                    "quantity" => $line_item->quantity,
                    "code" => (string)$line_item->product->id,
                    "length" => 2,
                    "width" => 2,
                    "height" => 2,
                ]

            );
        }

        $province_name = Place::getNameProvince($orderDB->customer_province);
        $district_name = Place::getNameDistrict($orderDB->customer_district);
        $wards_name = Place::getNameWards($orderDB->customer_wards);


     $provinceIdTo = GHNUtils::getIDProvinceGHN( $province_name);
     $districtIdTo = GHNUtils::getIDDistrictGHN($provinceIdTo,  $district_name );

     
     $wardCodeTo = GHNUtils::getWardCodeGHN($token, $districtIdTo,   $wards_name  );

        $orderData = [

            "payment_type_id" => 2,
            "required_note" => "KHONGCHOXEMHANG",
            "to_name" => $orderDB->customer_name,
            "to_phone" => $orderDB->customer_phone,
            "to_address" => $orderDB->customer_address_detail,
            "to_ward_code" => $wardCodeTo ,
            "to_district_id" =>  $districtIdTo,
            "cod_amount" =>  $orderDB->total_final -    $orderDB->total_shipping_fee,
            "weight" => 500,
            "length" => 20,
            "width" => 20,
            "height" => 20,
            "service_id" => 0,
            "service_type_id" => 2,
            "items" => $items
        ];



        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' =>  $orderData
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            if ($jsonResponse->code != 200) {
                return new Exception($jsonResponse->code_message_value);
            } else {
                // {#1635
                //     +"code": 200
                //     +"code_message_value": "Do diễn biến phức tạp của dịch Covid-19, thời gian giao hàng có thể dài hơn dự kiến từ 1-5 ngày."
                //     +"data": {#1629
                //       +"order_code": "GAN66XDB"
                //       +"sort_code": "190-G-01-A8"
                //       +"trans_type": "truck"
                //       +"ward_encode": ""
                //       +"district_encode": ""
                //       +"fee": {#1615
                //         +"main_service": 22000
                //         +"insurance": 0
                //         +"station_do": 0
                //         +"station_pu": 0
                //         +"return": 0
                //         +"r2s": 0
                //         +"coupon": 0
                //       }
                //       +"total_fee": 22000
                //       +"expected_delivery_time": "2022-01-15T23:59:59Z"
                //     }
                //     +"message": "Success"
                //     +"message_display": "Tạo đơn hàng thành công. Mã đơn hàng: GAN66XDB"
                //   }

                return [
                    'code' => $jsonResponse->data->order_code,
                    'fee' => $jsonResponse->data->total_fee,
                ];
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

    public static function send_order_vtp($orderDB, $addressPickupExists, $token)
    {

        $config = config('saha.shipper.list_shipper')[2];
        $send_order_url = $config["send_order_url"];

        //Dữ liệu up
        $LIST_ITEM = array();
        foreach ($orderDB->line_items as $line_item) {
            array_push(
                $LIST_ITEM,
                [
                    "PRODUCT_NAME" => $line_item->product->name,
                    "PRODUCT_PRICE" => $line_item->item_price,
                    "PRODUCT_WEIGHT" => 100,
                    "PRODUCT_QUANTITY" => $line_item->quantity,
                ]

            );
        }


        $store = Store::where('id', $orderDB->store_id)->first();
        $order = [
            "ORDER_NUMBER" => $orderDB->order_code,
            "GROUPADDRESS_ID" =>  5818802,
            "CUS_ID" => $orderDB->customer_phone,
            "SENDER_FULLNAME" =>  $store->name,
            "SENDER_ADDRESS" => $addressPickupExists->address_detail,
            "SENDER_PHONE" =>  $addressPickupExists->phone,
            "SENDER_WARD" =>  1,
            "SENDER_DISTRICT" =>  1,
            "SENDER_PROVINCE" =>  1,
            "RECEIVER_FULLNAME" => $orderDB->customer_name,
            "RECEIVER_ADDRESS" =>  $orderDB->customer_address_detail,
            "RECEIVER_PHONE" =>  $orderDB->customer_phone,
            "RECEIVER_WARD" =>  1,
            "RECEIVER_DISTRICT" => 1,
            "RECEIVER_PROVINCE" =>  1,
            "PRODUCT_TYPE" =>  "HH",
            "ORDER_PAYMENT" =>  3,
            "ORDER_SERVICE" =>  "VCN",
            "MONEY_TOTALFEE" =>  0,
            "MONEY_FEECOD" =>  0,
            "MONEY_FEEVAS" =>  0,
            "MONEY_FEEINSURRANCE" =>  0,
            "MONEY_FEE" =>  0,
            "MONEY_FEEOTHER" =>  0,
            "MONEY_TOTALVAT" =>  0,
            "MONEY_TOTAL" => $orderDB->total_final -    $orderDB->total_shipping_fee,
            // "MONEY_COLLECTION" => $orderDB->total_final -    $orderDB->total_shipping_fee,
            "LIST_ITEM" => $LIST_ITEM
        ];



        //////

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'token' => $token,
        ];

        try {
            $response = $client->post(
                $send_order_url,
                [
                    'headers' => $headers,
                    'timeout'         => 15,
                    'connect_timeout' => 15,
                    'query' => [],
                    'json' => $order
                ]
            );

            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);

            if ($jsonResponse->status != 200) {
                return new Exception($jsonResponse->message);
            } else {
                // {
                //     "status": 200,
                //     "error": false,
                //     "message": "OK",
                //     "data": {
                //         "ORDER_NUMBER": "15573271722",
                //         "MONEY_COLLECTION": 0,
                //         "EXCHANGE_WEIGHT": 50,
                //         "MONEY_TOTAL": 11000,
                //         "MONEY_TOTAL_FEE": 10000,
                //         "MONEY_FEE": 0,
                //         "MONEY_COLLECTION_FEE": 0,
                //         "MONEY_OTHER_FEE": 0,
                //         "MONEY_VAS": 0,
                //         "MONEY_VAT": 1000,
                //         "KPI_HT": 48.0,
                //         "RECEIVER_PROVINCE": 1,
                //         "RECEIVER_DISTRICT": 2,
                //         "RECEIVER_WARDS": 40
                //     }
                // }

                return [
                    'code' => $jsonResponse->data->ORDER_NUMBER,
                    'fee' => $jsonResponse->data->MONEY_TOTAL,
                ];
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
