<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Redis\Exception;

class PoolNotFoundException extends \RuntimeException
{
    public function __construct(string $pool)
    {
        parent::__construct("Pool {$pool} not found", 0, null);
    }
}
