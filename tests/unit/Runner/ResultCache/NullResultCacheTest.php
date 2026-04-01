<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;

#[CoversClass(NullResultCache::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/result-cache')]
final class NullResultCacheTest extends TestCase
{
    public function testSetStatusIsNoOp(): void
    {
        $cache = new NullResultCache;
        $id    = ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setStatus($id, TestStatus::failure('failure'));

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testStatusReturnsUnknown(): void
    {
        $cache = new NullResultCache;
        $id    = ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testSetTimeIsNoOp(): void
    {
        $cache = new NullResultCache;
        $id    = ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setTime($id, 1.234);

        $this->assertSame(0.0, $cache->time($id));
    }

    public function testTimeReturnsZero(): void
    {
        $cache = new NullResultCache;
        $id    = ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertSame(0.0, $cache->time($id));
    }

    public function testLoadIsNoOp(): void
    {
        $cache = new NullResultCache;

        $cache->load();

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne'))->isUnknown());
    }

    public function testPersistIsNoOp(): void
    {
        $cache = new NullResultCache;

        $cache->persist();

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'testOne'))->isUnknown());
    }
}
