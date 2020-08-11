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
                    'maxIdleTime' => (float)env('REDIS_POOL_MAX_IDLE_TIME', 15.0),
                    'idleCheckInterval' => (float)env('REDIS_POOL_IDLE_CHECK_INTERVAL', 30.0),
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
    $pool = $container->get(\MakiseCo\Redis\RedisManager::class)->getPool('example');
  
    $redis = $pool->borrow();
    // do something with Redis connection
    try {
        $redis->set('test', '1');
    } finally {
        $pool->return($redis);
    }
  
    // or use LazyConnection as an abstraction under pool
    $lazyConnection = $container->get(\MakiseCo\Redis\RedisManager::class)->getLazyConnection('example');
    $lazyConnection->set('test', '1');
    ```
