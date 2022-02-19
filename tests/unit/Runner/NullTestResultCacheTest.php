<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;

/**
 * @group test-reorder
 * @small
 */
final class NullTestResultCacheTest extends TestCase
{
    public function testHasWorkingStubs(): void
    {
        $cache = new NullTestResultCache;
        $cache->load();
        $cache->persist();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState('testName'));
        $this->assertSame(0.0, $cache->getTime('testName'));
    }
}
