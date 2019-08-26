<?php

return [
    'token' => [
        'expire' => 60 * 60 * 24,
        'http'   => [
            'token'  => 'Api-Token',
            'hash'   => 'Api-Token-Hash',
            'expire' => 'Api-Token-Expire',
        ],
    ]
];