<?php

return [
    'list_shipper' => [
        0 => [
            'name' => 'Giao hàng tiết kiệm',
            'id' => 0,
            'fee_url' => 'https://services.giaohangtietkiem.vn/services/shipment/fee',
            'send_order_url' => 'https://services.giaohangtietkiem.vn/services/shipment/order/?ver=1.5',
            'check_token_url' => 'https://services.giaohangtietkiem.vn/services/shipment/fee',
            'get_history_status_url' => 'https://services.giaohangtietkiem.vn/services/shipment/v2/',
            'ship_speed' => true,
            'image_url' => "https://i.imgur.com/JyXVvB0.png"
        ],
        1 => [
            'name' => 'Giao hàng nhanh',
            'id' => 1,
            'fee_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee',
            'send_order_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create',
            'check_token_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services',
            'get_history_status_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail',
            'ship_speed' => true,
            'image_url' => "https://i.imgur.com/5L3Cyag.png"
        ],
        2 => [
            'name' => 'Viettel Post',
            'id' => 2,
            'fee_url' => 'https://partner.viettelpost.vn/v2/order/getPrice',
            'check_token_url' => 'https://partner.viettelpost.vn/v2/user/listInventory',
            'send_order_url' => 'https://partner.viettelpost.vn/v2/order/createOrder',
            'get_history_status_url' => 'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail',
            'ship_speed' => true,
            'image_url' => "https://i.imgur.com/4FhU86q.png"
        ],
    ],
];
