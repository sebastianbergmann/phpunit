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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Deprecation::class)]
#[CoversClass(Error::class)]
#[CoversClass(Failure::class)]
#[CoversClass(Incomplete::class)]
#[CoversClass(Known::class)]
#[CoversClass(Notice::class)]
#[CoversClass(Risky::class)]
#[CoversClass(Skipped::class)]
#[CoversClass(Success::class)]
#[CoversClass(TestStatus::class)]
#[CoversClass(Unknown::class)]
#[CoversClass(Warning::class)]
#[Small]
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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

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
        $this->assertFalse($status->isDeprecation());
        $this->assertFalse($status->isNotice());

        $this->assertSame('skipped', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeDeprecation(): void
    {
        $status = TestStatus::deprecation('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());
        $this->assertTrue($status->isDeprecation());
        $this->assertFalse($status->isNotice());

        $this->assertSame('deprecation', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeNotice(): void
    {
        $status = TestStatus::notice('message');

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSuccess());
        $this->assertFalse($status->isFailure());
        $this->assertFalse($status->isError());
        $this->assertFalse($status->isWarning());
        $this->assertFalse($status->isRisky());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isSkipped());
        $this->assertFalse($status->isDeprecation());
        $this->assertTrue($status->isNotice());

        $this->assertSame('notice', $status->asString());
        $this->assertSame('message', $status->message());
    }

    public function testCanBeRepresentedAsIntegerValue(): void
    {
        $this->assertSame(-1, TestStatus::unknown()->asInt());
        $this->assertSame(0, TestStatus::success()->asInt());
        $this->assertSame(1, TestStatus::skipped()->asInt());
        $this->assertSame(2, TestStatus::incomplete()->asInt());
        $this->assertSame(3, TestStatus::notice()->asInt());
        $this->assertSame(4, TestStatus::deprecation()->asInt());
        $this->assertSame(5, TestStatus::risky()->asInt());
        $this->assertSame(6, TestStatus::warning()->asInt());
        $this->assertSame(7, TestStatus::failure()->asInt());
        $this->assertSame(8, TestStatus::error()->asInt());
    }

    public function testCanBeCreatedFromIntegerValue(): void
    {
        $this->assertInstanceOf(Unknown::class, TestStatus::from(-1));
        $this->assertInstanceOf(Success::class, TestStatus::from(0));
        $this->assertInstanceOf(Skipped::class, TestStatus::from(1));
        $this->assertInstanceOf(Incomplete::class, TestStatus::from(2));
        $this->assertInstanceOf(Notice::class, TestStatus::from(3));
        $this->assertInstanceOf(Deprecation::class, TestStatus::from(4));
        $this->assertInstanceOf(Risky::class, TestStatus::from(5));
        $this->assertInstanceOf(Warning::class, TestStatus::from(6));
        $this->assertInstanceOf(Failure::class, TestStatus::from(7));
        $this->assertInstanceOf(Error::class, TestStatus::from(8));
    }

    public function testSuccessIsMoreImportantThanUnknown(): void
    {
        $this->assertTrue(TestStatus::success()->isMoreImportantThan(TestStatus::unknown()));
        $this->assertFalse(TestStatus::unknown()->isMoreImportantThan(TestStatus::success()));
    }

    public function testSkippedIsMoreImportantThanSuccess(): void
    {
        $this->assertTrue(TestStatus::skipped()->isMoreImportantThan(TestStatus::success()));
        $this->assertFalse(TestStatus::success()->isMoreImportantThan(TestStatus::skipped()));
    }

    public function testIncompleteIsMoreImportantThanSkipped(): void
    {
        $this->assertTrue(TestStatus::incomplete()->isMoreImportantThan(TestStatus::skipped()));
        $this->assertFalse(TestStatus::skipped()->isMoreImportantThan(TestStatus::incomplete()));
    }

    public function testRiskyIsMoreImportantThanIncomplete(): void
    {
        $this->assertTrue(TestStatus::risky()->isMoreImportantThan(TestStatus::incomplete()));
        $this->assertFalse(TestStatus::incomplete()->isMoreImportantThan(TestStatus::risky()));
    }

    public function testWarningIsMoreImportantThanRisky(): void
    {
        $this->assertTrue(TestStatus::warning()->isMoreImportantThan(TestStatus::risky()));
        $this->assertFalse(TestStatus::risky()->isMoreImportantThan(TestStatus::warning()));
    }

    public function testFailureIsMoreImportantThanWarning(): void
    {
        $this->assertTrue(TestStatus::failure()->isMoreImportantThan(TestStatus::warning()));
        $this->assertFalse(TestStatus::warning()->isMoreImportantThan(TestStatus::failure()));
    }

    public function testErrorIsMoreImportantThanFailure(): void
    {
        $this->assertTrue(TestStatus::error()->isMoreImportantThan(TestStatus::failure()));
        $this->assertFalse(TestStatus::failure()->isMoreImportantThan(TestStatus::error()));
    }
}
