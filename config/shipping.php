<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chế độ vận chuyển
    |--------------------------------------------------------------------------
    |
    | demo: tự tạo mã vận đơn nội bộ mô phỏng, không gọi API bên ngoài.
    | api : dành cho giai đoạn kết nối GHN/GHTK/Viettel Post sau này.
    |
    */
    'mode' => env('SHIPPING_MODE', 'demo'),

    'demo' => [
        'default_provider' => env('SHIPPING_DEMO_PROVIDER', 'ghn'),
        'default_weight_per_item' => (int) env('SHIPPING_DEFAULT_WEIGHT_PER_ITEM', 250),
        'default_dimensions' => [
            'length' => (int) env('SHIPPING_DEFAULT_LENGTH', 25),
            'width' => (int) env('SHIPPING_DEFAULT_WIDTH', 18),
            'height' => (int) env('SHIPPING_DEFAULT_HEIGHT', 12),
        ],
    ],

    'providers' => [
        'ghn' => [
            'name' => 'GHN',
            'tracking_prefix' => 'GHN',
            'default_service' => 'Giao hàng tiêu chuẩn',
        ],
        'ghtk' => [
            'name' => 'GHTK',
            'tracking_prefix' => 'GHTK',
            'default_service' => 'Giao tiêu chuẩn',
        ],
        'viettel_post' => [
            'name' => 'Viettel Post',
            'tracking_prefix' => 'VTP',
            'default_service' => 'Chuyển phát tiêu chuẩn',
        ],
        'internal' => [
            'name' => 'Giao hàng nội bộ',
            'tracking_prefix' => 'CS',
            'default_service' => 'Giao hàng tiêu chuẩn',
        ],
    ],
];
