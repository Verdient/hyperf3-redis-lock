<?php

namespace Verdient\Hyperf3\RedisLock;

use Hyperf\Redis\Redis;

/**
 * Redis 锁
 *
 * @author Verdient。
 */
class RedisLock
{
    /**
     * @param Redis $redis Redis对象
     * @param string $name 名称
     * @param int $ttl 生存时间
     *
     * @author Verdient。
     */
    public function __construct(
        protected Redis $redis,
        protected string $name,
        protected ?int $ttl = null
    ) {}

    /**
     * 取得锁
     *
     * @author Verdient。
     */
    public function acquire(): bool
    {
        $result = $this->redis->setnx($this->name, 1);

        if (intval($result) === 1 && $this->ttl > 0) {
            $this->redis->expire($this->name, $this->ttl);
        }

        return intval($result) === 1;
    }

    /**
     * 释放锁
     *
     * @author Verdient。
     */
    public function release(): bool
    {
        return intval($this->redis->del($this->name)) === 1;
    }

    /**
     * @param int $seconds 等待的秒数
     * @param int $interval 重试间隔
     *
     * @author Verdient。
     */
    public function block(int $seconds, $interval = 250000): bool
    {
        $starting = time();
        while (!$this->acquire()) {
            usleep($interval);
            if (time() - $seconds >= $starting) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取锁是否已存在
     *
     * @author Verdient。
     */
    public function exists(): bool
    {
        return $this->redis->exists($this->name) === 0;
    }
}
