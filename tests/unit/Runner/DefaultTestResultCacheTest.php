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
use PHPUnit\Framework\TestStatus\TestStatus;
use ReflectionClass;

/**
 * @covers \PHPUnit\Runner\DefaultTestResultCache
 * @small
 */
final class DefaultTestResultCacheTest extends TestCase
{
    /**
     * @var DefaultTestResultCache
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new DefaultTestResultCache();
    }

    public function testGetTimeForNonExistentTestNameReturnsFloatZero(): void
    {
        $this->assertSame(0.0, $this->subject->time('doesNotExist'));
    }

    public function testOldResultCacheFormatDoesNotTriggerError(): void
    {
        // PHPUnit before version 10 used integer constants instead of TestStatus.
        // Note: this is a quick and cheap test for this uncommon edge case, without having
        // to set up a full end-to-end test with an old cache file
        $cache          = new DefaultTestResultCache;
        $reflectedCache = new ReflectionClass($cache);
        $defects        = $reflectedCache->getProperty('defects');
        $defects->setAccessible(true);
        $defects->setValue($cache, [
            'testOne' => TestStatus::skipped(),
            'testTwo' => 1,
        ]);

        $targetCache = new DefaultTestResultCache;
        $cache->copyStateToCache($targetCache);

        // Validate expected behaviour: the test with old success value is silently ignored
        $this->assertEquals(TestStatus::skipped(), $targetCache->status('testOne'));
        $this->assertEquals(TestStatus::unknown(), $targetCache->status('testTwo'));
    }
}
