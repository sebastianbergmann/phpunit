<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\TestSuite\BeforeTestSuite
 */
final class BeforeTestSuiteTest extends TestCase
{
    public function testTypeIsBeforeTestSuite(): void
    {
        $testSuite = new TestSuite();

        $event = new BeforeTestSuite($testSuite);

        $this->assertTrue($event->type()->is(new NamedType('before-test-suite')));
        $this->assertSame($testSuite, $event->testSuite());
    }
}
