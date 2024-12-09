<?php

namespace DetectionTests;

use DateTime;
use DateInterval;
use Detection\Cache\CacheItem;
use PHPUnit\Framework\TestCase;

final class CacheItemTest extends TestCase
{
    public function testConstructor(): void
    {
        $item = new CacheItem('key', true);
        $this->assertTrue($item->get());
        $this->assertEquals('key', $item->getKey());
    }

    public function testSet(): void
    {
        $item = new CacheItem('key', true);
        $item->set(false);
        $this->assertFalse($item->get());
    }

    public function testIsHit(): void
    {
        $item = new CacheItem('key', true);

        $this->assertTrue($item->isHit());

        $item->expiresAfter(10);

        $this->assertTrue($item->isHit());
    }

    public function testIsHitBasedOnExpiresAfter(): void
    {
        $item = new CacheItem('key', true);
        $item->expiresAfter(0);

        $this->assertTrue($item->isHit());

        $item->expiresAfter(1000);

        $this->assertTrue($item->isHit());

        $item->expiresAfter(new DateInterval('PT1S'));

        $this->assertTrue($item->isHit());
    }

    public function testIsHitBasedOnExpiresAt(): void
    {
        $item = new CacheItem('key', true);
        $item->expiresAt((new DateTime())->add(new DateInterval('PT10S')));
        $this->assertTrue($item->isHit());
    }

    public function testIsHitMissBasedOnExpiresAt(): void
    {
        $item = new CacheItem('key', true);
        $item->expiresAt((new DateTime())->sub(new DateInterval('PT10S')));
        $this->assertFalse($item->isHit());
    }

    public function testExpiresAfterDoesnNotAcceptNegativeValues(): void
    {
        $item = new CacheItem('key', true);
        $item->expiresAfter(-10);

        $this->assertNull($item->expiresAfter);
    }


}
