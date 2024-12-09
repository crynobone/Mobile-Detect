<?php

namespace DetectionTests;

use Detection\Cache\Cache;
use Detection\Cache\CacheException;
use Detection\Cache\CacheItem;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

final class CacheTest extends TestCase
{
    protected Cache $cache;
    protected function setUp(): void
    {
        $this->cache = new Cache();
    }

    public function testGetInvalidCacheKey()
    {
        $this->expectException(CacheException::class);
        $this->cache->get('');
    }

    public function testSetInvalidCacheKey()
    {
        $this->expectException(CacheException::class);
        $this->cache->set('', 'a', 100);
    }

    /**
     * @throws CacheException
     */
    public function testGetNonExistent()
    {
        $this->assertNull($this->cache->get('random'));
    }

    /**
     * @throws CacheException
     */
    public function testSetBoolean()
    {
        $this->cache->set('isMobile', true, 100);
        $this->assertInstanceOf(CacheItem::class, $this->cache->get('isMobile'));
        $this->assertTrue($this->cache->get('isMobile')->get());

        $this->cache->set('isTablet', false, 100);
        $this->assertInstanceOf(CacheItem::class, $this->cache->get('isTablet'));
        $this->assertFalse($this->cache->get('isTablet')->get());
    }

    /**
     * @throws CacheException
     */
    public function testGetTTL0()
    {
        $this->cache->set('isMobile', true, 0);
        $this->assertInstanceOf(CacheItem::class, $this->cache->get('isMobile'));
        $this->assertNull($this->cache->get('isMobile')->expiresAt);
        $this->assertNull($this->cache->get('isMobile')->expiresAfter);
    }

    public function testGetTtlIsInteger()
    {
        $this->cache->set('isMobile', true, 1000);
        $this->assertInstanceOf(CacheItem::class, $this->cache->get('isMobile'));
        $this->assertNUll($this->cache->get('isMobile')->expiresAt);
        $this->assertInstanceOf(\DateInterval::class, $this->cache->get('isMobile')->expiresAfter);
    }

    /**
     * @throws CacheException
     */
    public function testGetExpiresAfter()
    {
        $this->cache->set('isMobile', true);
        $this->assertInstanceOf(CacheItem::class, $this->cache->get('isMobile'));
        $this->assertNull($this->cache->get('isMobile')->expiresAfter);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testDelete()
    {
        $this->cache->set('isMobile', true, 100);
        $this->assertTrue($this->cache->get('isMobile')->get());
        $this->cache->delete('isMobile');
        $this->assertNull($this->cache->get('isMobile'));
    }

    /**
     * @throws CacheException
     */
    public function testClear()
    {
        $this->cache->set('isMobile', true);
        $this->cache->set('isTablet', true);
        $this->cache->clear();
        $this->assertNull($this->cache->get('isMobile'));
        $this->assertNull($this->cache->get('isTablet'));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testGetMultiple(): void
    {
        $this->cache->set('isMobile', true, 100);
        $this->cache->set('isTablet', false, 200);

        $this->assertEquals(
            [
            'isMobile' => (new CacheItem('isMobile', true))->expiresAfter(100),
            'isTablet' => (new CacheItem('isTablet', false))->expiresAfter(200),
            'isUnknown' => null,
            ],
            $this->cache->getMultiple(['isMobile', 'isTablet', 'isUnknown'])
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function testSetMultiple(): void
    {
        $this->cache->setMultiple(['isA' => true, 'isB' => false], 200);
        $this->assertEquals([
            'isA' => (new CacheItem('isA', true))->expiresAfter(200),
            'isB' => (new CacheItem('isB', false))->expiresAfter(200)
        ], $this->cache->getMultiple(['isA', 'isB']));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testDeleteMultiple(): void
    {
        $this->cache->setMultiple(['isA' => true, 'isB' => false, 'isC' => true], 300);

        $this->cache->deleteMultiple(['isA', 'isB']);

        $this->assertEquals([
            'isA' => null,
            'isB' => null,
            'isC' => (new CacheItem('isC', true))->expiresAfter(300)
        ], $this->cache->getMultiple(['isA', 'isB', 'isC']));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testHas(): void
    {
        $this->cache->set('isA', true);
        $this->assertTrue($this->cache->has('isA'));
    }
}
