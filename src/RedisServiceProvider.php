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
use MakiseCo\Bootstrapper;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;

use function array_key_exists;

class RedisServiceProvider implements ServiceProviderInterface
{
    public const SERVICE_NAME = 'redis';

    public function register(Container $container): void
    {
        /** @var Bootstrapper $bootstrapper */
        $bootstrapper = $container->get(Bootstrapper::class);

        $bootstrapper->addService(
            self::SERVICE_NAME,
            static function () use ($container) {
                $container->get(RedisManager::class)->initPools();
            },
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
                    $pool = new RedisPool(
                        ConnectionConfig::fromArray($connection['connection'])
                    );

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
