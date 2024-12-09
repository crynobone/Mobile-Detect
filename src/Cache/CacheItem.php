<?php

namespace Detection\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

/**
 * Simple cache item (key, value, ttl) that is being
 * used by all the detection methods of Mobile Detect class.
 */
class CacheItem implements CacheItemInterface
{
    /**
     * @var string Unique key for the cache record.
     */
    protected string $key;
    /**
     * @var bool|null Mobile Detect only needs to store booleans (e.g. "isMobile" => true)
     */
    protected bool|null $value = null;
    /**
     * @var DateTimeInterface|null
     */
    public DateTimeInterface|null $expiresAt = null;
    /**
     * @var DateInterval|null
     */
    public DateInterval|null $expiresAfter = null;

    public function __construct($key, $value = null, $expiresAt = null, $expiresAfter = null)
    {
        $this->key = $key;
        if (!is_null($value)) {
            $this->value = $value;
        }
        $this->expiresAt = $expiresAt;
        $this->expiresAfter = $expiresAfter;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): string|bool
    {
        return $this->value;
    }

    public function set($value): void
    {
        $this->value = $value;
    }

    public function isHit(): bool
    {
        // Item never expires.
        if ($this->expiresAt === null) {
            return true;
        }

        if ($this->expiresAt > new DateTime()) {
            return true;
        }

        return false;
    }

    public function expiresAt($expiration): void
    {
        $expiresAt = null;

        if ($expiration instanceof \DateInterval) {
            $expiresAt = (new DateTime())->add($expiration);
        } elseif (is_int($expiration)) {
            if ($expiration > 0) {
                $expiresAt = new DateTime("{$expiration} seconds");
            }
        }

        $this->expiresAt = $expiresAt;
    }

    public function expiresAfter($time): void
    {
        $expiresAfter = null;

        if ($time instanceof \DateInterval) {
            $expiresAfter = $time;
        } elseif (is_int($time)) {
            if ($time > 0) {
                $expiresAfter = new \DateInterval("PT{$time}S");
            }
        }

        $this->expiresAfter = $expiresAfter;
    }
}
