<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\RedisLock\Facade;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use Verdient\Hyperf3\RedisLock\RedisLock;

/**
 * 锁
 *
 * @author Verdient。
 */
class Lock
{
    /**
     * 获取连接对象
     *
     * @author Verdient。
     */
    public static function connection(): Redis
    {
        return ApplicationContext::getContainer()->get(Redis::class);
    }

    /**
     * 获取锁定的键名
     *
     * @param string|int|float $key 标识
     *
     * @author Verdient。
     */
    protected static function lockKey(string|int|float $key): string
    {
        return '__lock__' . md5((string) $key);
    }

    /**
     * 锁定
     *
     * @param string|int|float $key 标识
     * @param int $ttl 最大存活时间
     *
     * @author Verdient。
     */
    public static function lock(string|int|float $key, int $ttl = 10800): bool
    {
        $redisLock = new RedisLock(static::connection(), static::lockKey($key), $ttl);

        return $redisLock->acquire();
    }

    /**
     * 解锁
     *
     * @param string|int|float $key 标识
     *
     * @author Verdient。
     */
    public static function unlock(string|int|float $key): bool
    {
        $redisLock = new RedisLock(static::connection(), static::lockKey($key));

        return $redisLock->release();
    }

    /**
     * 获取锁是否存在
     *
     * @param string|int|float $key 标识
     *
     * @author Verdient。
     */
    public static function exists(string|int|float $key): bool
    {
        $redisLock = new RedisLock(static::connection(), static::lockKey($key));

        return $redisLock->exists();
    }
}
