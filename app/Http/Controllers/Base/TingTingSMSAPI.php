<?php

namespace App\Http\Controllers\Base;


class TingTingSMSAPI
{
    private $apiKey = '16eZwau4S4fDIREj3vT640155a128601';
    private $API_URL = 'https://v1.tingting.im/api';
    private $WIDGET_URL = 'https://widgetapiv1.tingting.im/api';

    public function __construct($key)
    {
        $this->apiKey = $key;
    }

    //send message to a phone number through Zalo OA
    public function sendZNS($to, $sender, $tempid, $tempdata, $failoverdata = null, $sendTime = '', $timezone = '')
    {

        $params = [
            'to' => $to,
            'sender' => $sender,
            'tempid' => $tempid,
            'temp_data' => $tempdata
        ];

        if (!empty($failoverdata) && is_array($failoverdata)) {

            if (!isset($failoverdata['sender']) || !isset($failoverdata['content'])) {
                return null;
            }

            $params['failover'] = $failoverdata;
        }

        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/zns';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    //send message to a phone number through SMS
    public function sendSMS($to, $sender, $content, $sendTime = '', $timezone = '')
    {
        $params = [
            'to' => $to,
            'sender' => $sender,
            'content' => $content
        ];

        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/sms';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    //call to a phone number and reading the content using text to speech
    public function call($to, $sender, $content, $sendTime = '', $timezone = '')
    {
        $params = [
            'to' => $to,
            'sender' => $sender,
            'content' => $content
        ];
        if (!empty($sendTime)) {
            $params['send_time'] = $sendTime;
        }
        if (!empty($timezone)) {
            $params['timezone'] = $timezone;
        }
        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/call';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }


    public function session($configId, $to = '')
    {
        $params = [
            'config_id' => $configId
        ];
        if (!empty($to)) {
            $params['to'] = $to;
        }

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->WIDGET_URL . '/session';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function createPin($configId, $to, $channel)
    {
        $params = [
            'config_id' => $configId,
            'to' => $to,
            'channel' => $channel
        ];

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/pin';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function verifyPin($msgId, $pinCode)
    {
        $params = [
            'msg_id' => $msgId,
            'pin_code' => $pinCode
        ];

        $json = json_encode($params);

        $headers = array('Content-type: application/json', 'apikey: ' . $this->apiKey);

        $url = $this->API_URL . '/verify';
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);

        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }
}
