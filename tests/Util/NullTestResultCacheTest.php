<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\NullTestResultCache;

/**
 * @group test-reorder
 */
class NullTestResultCacheTest extends TestCase
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
