<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Exception;
use PHPUnit\Framework\TestCase;

final class TestWithDifferentStatuses extends TestCase
{
    public function testThatFails(): void
    {
        $this->fail();
    }

    public function testThatErrors(): void
    {
        throw new Exception();
    }

    public function testThatPasses(): void
    {
        $this->assertTrue(true);
    }

    public function testThatIsMarkedAsIncomplete(): void
    {
        $this->markTestIncomplete();
    }

    public function testThatIsMarkedAsRisky(): void
    {
        $this->markAsRisky();
    }

    public function testThatIsMarkedAsSkipped(): void
    {
        $this->markTestSkipped();
    }

    public function testThatAddsAWarning(): void
    {
        $this->addWarning('Sorry, Dave!');
    }

    public function testWithCreatePartialMockWarning(): void
    {
        $this->createPartialMock(\PHPUnit\TestFixture\Mockable::class, ['mockableMethod', 'fakeMethod1', 'fakeMethod2']);
    }

    public function testWithCreatePartialMockPassesNoWarning(): void
    {
        $mock = $this->createPartialMock(\PHPUnit\TestFixture\Mockable::class, ['mockableMethod']);
        $this->assertNull($mock->mockableMethod());
    }
}
