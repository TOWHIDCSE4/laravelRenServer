<?php

return [
    'push_url' => 'https://fcm.googleapis.com/fcm/send',
    'server_key' => env('FIREBASE_SERVER_KEY', null), // Lấy trong tài khoản firebase
    'device_type' => [
        'ios' => 'iOS',
        'android' => 'android'
    ],
    'sound' => 'default'
];
