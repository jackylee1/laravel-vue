<?php

return [
    'shanzhen'   => [
        'pid' => env('SHANZHEN_PID'),
        'key' => env('SHANZHEN_KEY'),
        'url' => [
            'order'   => env('SHANZHEN_ORDER_URI'),
            'reserve' => env('SHANZHEN_RESERVE_URI'),
            'query' => env('SHANZHEN_QUERY_URI')
        ]
    ]
];
