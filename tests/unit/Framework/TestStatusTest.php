<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestStatus;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\TestStatus\Error
 * @covers \PHPUnit\Framework\TestStatus\Failure
 * @covers \PHPUnit\Framework\TestStatus\Incomplete
 * @covers \PHPUnit\Framework\TestStatus\Known
 * @covers \PHPUnit\Framework\TestStatus\Risky
 * @covers \PHPUnit\Framework\TestStatus\Skipped
 * @covers \PHPUnit\Framework\TestStatus\Success
 * @covers \PHPUnit\Framework\TestStatus\TestStatus
 * @covers \PHPUnit\Framework\TestStatus\Unknown
 * @covers \PHPUnit\Framework\TestStatus\Warning
 *
 * @small
 */
final class TestStatusTest extends TestCase
{
    public function testCanBeUnknown(): void
    {
        $status = TestStatus::unknown();

        $this->assertFalse($status->isKnown());
        $this->assertTrue($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('unknown', $status->asString());
    }

    public function testCanBeSuccess(): void
    {
        $status = TestStatus::success();

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertTrue($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('success', $status->asString());
    }

    public function testCanBeFailure(): void
    {
        $status = TestStatus::failure('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertTrue($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('failure', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeError(): void
    {
        $status = TestStatus::error('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertTrue($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('error', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeWarning(): void
    {
        $status = TestStatus::warning('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertTrue($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('warning', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeRisky(): void
    {
        $status = TestStatus::risky('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertTrue($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('risky', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeIncomplete(): void
    {
        $status = TestStatus::incomplete('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertTrue($status->isIncomplete());
        $this->assertFalse($status->isSkipped());

        $this->assertSame('incomplete', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeSkipped(): void
    {
        $status = TestStatus::skipped('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertTrue($status->isSkipped());

        $this->assertSame('skipped', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeRepresentedAsIntegerValue(): void
    {
        $this->assertSame(-1, TestStatus::unknown()->asInt());
        $this->assertSame(0, TestStatus::success()->asInt());
        $this->assertSame(1, TestStatus::risky()->asInt());
        $this->assertSame(2, TestStatus::warning()->asInt());
        $this->assertSame(3, TestStatus::skipped()->asInt());
        $this->assertSame(4, TestStatus::incomplete()->asInt());
        $this->assertSame(5, TestStatus::failure()->asInt());
        $this->assertSame(6, TestStatus::error()->asInt());
    }

    public function testCanBeCreatedFromIntegerValue(): void
    {
        $this->assertInstanceOf(Unknown::class, TestStatus::from(-1));
        $this->assertInstanceOf(Success::class, TestStatus::from(0));
        $this->assertInstanceOf(Risky::class, TestStatus::from(1));
        $this->assertInstanceOf(Warning::class, TestStatus::from(2));
        $this->assertInstanceOf(Skipped::class, TestStatus::from(3));
        $this->assertInstanceOf(Incomplete::class, TestStatus::from(4));
        $this->assertInstanceOf(Failure::class, TestStatus::from(5));
        $this->assertInstanceOf(Error::class, TestStatus::from(6));
    }
}
