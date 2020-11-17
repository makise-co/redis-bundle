# redis-bundle
Makise-Co Redis integration bundle

## Usage
* Add RedisServiceProvider to your app config
* Create redis configuration file in config/redis.php:
    ```php
    <?php
    
    declare(strict_types=1);
    
    use function MakiseCo\Env\env;
    
    return [
        'defaults' => [
            // a connection pool named as "example" will be used as default connection pool
            'connection' => 'example',
        ],
        'connections' => [
            // "example" is a pool name
            'example' => [
                'connection' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => (int)env('REDIS_PORT', 6379),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => (int)env('REDIS_DATABASE', 0),
    //                'timeout' => 10.0,
    //                'readTimeout' => 5.0,
    //                'retryInterval' => 5,
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
    ```
* Initialize redis connection pools:
    ```php
    /** @var \Psr\Container\ContainerInterface $container */
    // redis connection pools are automatically initialized for HTTP server
    $container->get(\MakiseCo\Redis\RedisManager::class)->initPools();
    ```
* Take connection pool from RedisManager:
    ```php
    /** @var \Psr\Container\ContainerInterface $container */
    $redisManager = $container->get(\MakiseCo\Redis\RedisManager::class);

    $pool = $redisManager->getPool('example');
    // or get default connection pool
    $pool = $redisManager->getPool();
  
    $redis = $pool->borrow();
    // do something with Redis connection
    try {
        $redis->set('test', '1');
    } finally {
        $pool->return($redis);
    }
  
    // or use LazyConnection as an abstraction over connection pool
    $lazyConnection = $redisManager->getLazyConnection('example');
    // or get lazy connection for default connection pool
    $lazyConnection = $redisManager->getLazyConnection();
    $lazyConnection->set('test', '1');
    ```
