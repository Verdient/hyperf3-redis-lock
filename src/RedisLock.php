<?php

namespace Verdient\Hyperf3\RedisLock;

use Hyperf\Redis\RedisProxy;

/**
 * Redis 锁
 * @author Verdient。
 */
class RedisLock
{
    /**
     * @param RedisProxy $redis Redis对象
     * @param string $name 名称
     * @param int $seconds 生存时间
     * @author Verdient。
     */
    public function __construct(
        protected RedisProxy $redis,
        protected string $name,
        protected int $seconds
    ) {
    }

    /**
     * 取得锁
     * @author Verdient。
     */
    public function acquire(): bool
    {
        $result = $this->redis->setnx($this->name, 1);

        if (intval($result) === 1 && $this->seconds > 0) {
            $this->redis->expire($this->name, $this->seconds);
        }

        return intval($result) === 1;
    }

    /**
     * 释放锁
     * @author Verdient。
     */
    public function release(): bool
    {
        return intval($this->redis->del($this->name)) === 1;
    }

    /**
     * @param int $seconds 等待的秒数
     * @param int $interval 重试间隔
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
}
