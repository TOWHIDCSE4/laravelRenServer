<?php

namespace App\Http\Controllers\Api;

class SpeedSMSAPI
{
    const SMS_TYPE_QC = 1; // loai tin nhan quang cao
    const SMS_TYPE_CSKH = 2; // loai tin nhan cham soc khach hang
    const SMS_TYPE_BRANDNAME = 3; // loai tin nhan brand name cskh
    const SMS_TYPE_NOTIFY = 4; // sms gui bang brandname Notify
    const SMS_TYPE_GATEWAY = 5; // sms gui bang so di dong ca nhan tu app android, download app tai day: https://speedsms.vn/sms-gateway-service/

    // private $ROOT_URL = "https://api.speedsms.vn/index.php";
    private $ROOT_URL = "https://v1.tingting.im/api/sms";
    private $accessToken = "Your API access token";

    function __construct($api_key)
    {
        $this->accessToken = $api_key;
    }

    public function getUserInfo()
    {
        $url = $this->ROOT_URL . '/user/info';
        $headers = array('Accept: application/json');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $this->accessToken . ':x');

        $results = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        } else {
            curl_close($ch);
        }
        return json_decode($results, true);
    }

    public function sendSMS($to, $smsContent, $smsType, $sender)
    {
        if (!is_array($to) || empty($to) || empty($smsContent))
            return null;

        $type = SpeedSMSAPI::SMS_TYPE_CSKH;
        if (!empty($smsType))
            $type = $smsType;

        if ($type < 1 || $type > 8)
            return null;

        if (($type == 3 || $type == 5 || $type == 7 || $type == 8) && empty($sender))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent, 'sms_type' => $type, 'sender' => $sender));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/sms/send';

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
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function sendMMS($to, $smsContent, $link, $sender)
    {
        if (!is_array($to) || empty($to) || empty($smsContent))
            return null;

        $type = SpeedSMSAPI::SMS_TYPE_CSKH;
        if (!empty($smsType))
            $type = $smsType;

        if ($type < 1 || $type > 8)
            return null;

        if (($type == 3 || $type == 5) && empty($sender))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent, 'link' => $link, 'sender' => $sender));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/mms/send';
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
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }

    public function sendVoice($to, $smsContent)
    {
        if (empty($to) || empty($smsContent))
            return null;

        $json = json_encode(array('to' => $to, 'content' => $smsContent));

        $headers = array('Content-type: application/json');

        $url = $this->ROOT_URL . '/voice/otp';
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
        curl_setopt($http, CURLOPT_USERPWD, $this->accessToken . ':x');
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
            return json_decode($result, true);
        }
    }
}

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
