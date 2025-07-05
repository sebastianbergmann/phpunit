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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullResultCache::class)]
#[Small]
final class NullTestResultCacheTest extends TestCase
{
    public function testHasWorkingStubs(): void
    {
        $cache = new NullResultCache;
        $cache->load();
        $cache->persist();

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'testName'))->isUnknown());
        $this->assertSame(0.0, $cache->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'testName')));
    }
}
