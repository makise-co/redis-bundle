<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

use function MakiseCo\Env\env;

return [
    'defaults' => [
        // a connection pool named as "default" will be used as default connection pool
        'connection' => 'default',
    ],
    'connections' => [
        // "example" is a pool name
        'default' => [
            'connection' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => (int)env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD'),
                'database' => (int)env('REDIS_DATABASE', 0),
                'timeout' => 10.0,
                'readTimeout' => 5.0,
                'retryInterval' => 5,
            ],
            'pool' => [
                'minActive' => (int)env('REDIS_POOL_MIN_ACTIVE', 0),
                'maxActive' => (int)env('REDIS_POOL_MAX_ACTIVE', 2),
                'maxWaitTime' => (float)env('REDIS_POOL_MAX_WAIT_TIME', 5.0),
                'maxIdleTime' => (int)env('REDIS_POOL_MAX_IDLE_TIME', 30),
                'validationInterval' => (float)env('REDIS_POOL_VALIDATION_INTERVAL', 30.0),
            ],
        ],
    ],
];
