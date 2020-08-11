<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Redis;

use MakiseCo\Database\Exceptions\PoolNotFoundException;
use MakiseCo\Disposable\DisposableInterface;

class RedisManager implements DisposableInterface
{
    /**
     * Key - database config name
     * Value - ConnectionPool
     *
     * @var RedisPool[]
     */
    protected array $pools = [];

    public function getPool(string $poolName): RedisPool
    {
        $pool = $this->pools[$poolName] ?? null;
        if (null === $pool) {
            throw new PoolNotFoundException($poolName);
        }

        return $pool;
    }

    public function getLazyConnection(string $poolName): RedisLazyConnection
    {
        $pool = $this->getPool($poolName);

        return new RedisLazyConnection($pool);
    }

    public function addPool(string $name, RedisPool $pool): void
    {
        $this->pools[$name] = $pool;
    }

    public function initPools(): void
    {
        foreach ($this->pools as $pool) {
            $pool->init();
        }
    }

    public function initPool(string $poolName): void
    {
        $pool = $this->getPool($poolName);
        $pool->init();
    }

    public function closePools(): void
    {
        foreach ($this->pools as $pool) {
            $pool->close();
        }
    }

    public function closePool(string $poolName): void
    {
        $pool = $this->getPool($poolName);
        $pool->close();
    }

    public function dispose(): void
    {
        $this->closePools();
    }
}
