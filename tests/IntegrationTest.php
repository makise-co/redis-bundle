<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Redis\Tests;

use MakiseCo\Application;
use MakiseCo\Redis\RedisManager;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class IntegrationTest extends TestCase
{
    public function testItWorks(): void
    {
        Coroutine\run(function () {
            $app = new Application(
                dirname(__DIR__),
                dirname(__DIR__) . '/config'
            );
            $redisManager = $app->getContainer()->get(RedisManager::class);
            $redisManager->initPools();

            $redisManager->getPool()->set('test', '123');

            try {
                self::assertSame('123', $redisManager->getPool()->get('test'));
            } catch (\Throwable $e) {
                $redisManager->closePools();

                throw $e;
            }

            $redisManager->getPool('sentinel')->set('test', '456');

            try {
                self::assertSame('456', $redisManager->getPool()->get('test'));
            } finally {
                $redisManager->closePools();
            }
        });
    }
}
