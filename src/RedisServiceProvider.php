<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Redis;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Http\Events\WorkerStarted;
use MakiseCo\Pool\PoolConfig;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $dispatcher = $container->get(EventDispatcher::class);

        // initialize redis pools when http worker is started
        $dispatcher->addListener(
            WorkerStarted::class,
            static function () use ($container) {
                $container->get(RedisManager::class)->initPools();
            }
        );

        $container->set(
            RedisManager::class,
            static function (ConfigRepositoryInterface $config) {
                $defaults = $config->get('redis.defaults', []);
                $redisManager = new RedisManager($defaults['connection'] ?? 'default');

                foreach ($config->get('redis.connections', []) as $name => $connection) {
                    $poolConfig = new PoolConfig(
                        $connection['pool']['minActive'] ?? 0,
                        $connection['pool']['maxActive'] ?? 1,
                        $connection['pool']['maxWaitTime'] ?? 6,
                        $connection['pool']['maxIdleTime'] ?? 15,
                        $connection['pool']['idleCheckInterval'] ?? 30,
                    );

                    $pool = new RedisPool(
                        $poolConfig,
                        null,
                        RedisConnectionConfig::fromArray($connection['connection']),
                    );

                    $redisManager->addPool($name, $pool);
                }

                return $redisManager;
            }
        );
    }
}
