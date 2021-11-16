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
use MakiseCo\Http\Events\WorkerExit;
use MakiseCo\Http\Events\WorkerStarted;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_key_exists;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $dispatcher = $container->get(EventDispatcher::class);

        // initialize redis pools when http worker started
        $dispatcher->addListener(
            WorkerStarted::class,
            static function () use ($container) {
                $container->get(RedisManager::class)->initPools();
            }
        );

        // close redis pools when http worker stopped
        $dispatcher->addListener(
            WorkerExit::class,
            static function () use ($container) {
                $container->get(RedisManager::class)->closePools();
            }
        );

        $container->set(
            RedisManager::class,
            static function (ConfigRepositoryInterface $config) {
                $defaults = $config->get('redis.defaults', []);
                $redisManager = new RedisManager($defaults['connection'] ?? 'default');

                foreach ($config->get('redis.connections', []) as $name => $connection) {
                    $replication = $connection['connection']['replication'] ?? '';
                    if ($replication === 'sentinel') {
                        $pool = new RedisPool(
                            SentinelConnectionConfig::fromArray($connection['connection']),
                            new RedisSentinelConnector(),
                        );
                    } else {
                        $pool = new RedisPool(
                            ConnectionConfig::fromArray($connection['connection'])
                        );
                    }

                    $poolConfig = $connection['pool'];
                    if (array_key_exists('minActive', $poolConfig)) {
                        $pool->setMinActive($poolConfig['minActive']);
                    }
                    if (array_key_exists('maxActive', $poolConfig)) {
                        $pool->setMinActive($poolConfig['maxActive']);
                    }
                    if (array_key_exists('maxWaitTime', $poolConfig)) {
                        $pool->setMaxWaitTime($poolConfig['maxWaitTime']);
                    }
                    if (array_key_exists('maxIdleTime', $poolConfig)) {
                        $pool->setMaxIdleTime($poolConfig['maxIdleTime']);
                    }
                    if (array_key_exists('validationInterval', $poolConfig)) {
                        $pool->setValidationInterval($poolConfig['validationInterval']);
                    }

                    $redisManager->addPool($name, $pool);
                }

                return $redisManager;
            }
        );
    }
}
