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
        $this->assertSame(0.0, $this->subject->getTime('doesNotExist'));
    }
}
