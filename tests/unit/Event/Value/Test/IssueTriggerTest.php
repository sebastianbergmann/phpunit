<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\IssueTrigger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueTrigger::class)]
#[Small]
final class IssueTriggerTest extends TestCase
{
    public function testCanBeTestCode(): void
    {
        $trigger = IssueTrigger::test();

        $this->assertTrue($trigger->isTest());
        $this->assertTrue($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by test code', $trigger->asString());
    }

    public function testCanBeSelf(): void
    {
        $trigger = IssueTrigger::from(Code::FirstParty, Code::FirstParty);

        $this->assertTrue($trigger->isSelf());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by first-party code calling into first-party code', $trigger->asString());
    }

    public function testCanBeSelf2(): void
    {
        $trigger = IssueTrigger::from(Code::FirstParty, Code::ThirdParty);

        $this->assertTrue($trigger->isSelf());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by third-party code calling into first-party code', $trigger->asString());
    }

    public function testCanBeDirect(): void
    {
        $trigger = IssueTrigger::from(Code::ThirdParty, Code::FirstParty);

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by first-party code calling into third-party code', $trigger->asString());
    }

    public function testCanBeIndirect(): void
    {
        $trigger = IssueTrigger::from(Code::ThirdParty, Code::ThirdParty);

        $this->assertTrue($trigger->isIndirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by third-party code calling into third-party code', $trigger->asString());
    }

    public function testTestCallingThirdPartyIsDirect(): void
    {
        $trigger = IssueTrigger::from(Code::ThirdParty, Code::Test);

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by test code calling into third-party code', $trigger->asString());
    }

    public function testFirstPartyCallingPhpIsDirect(): void
    {
        $trigger = IssueTrigger::from(Code::PHP, Code::FirstParty);

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by first-party code calling into PHP runtime', $trigger->asString());
    }

    public function testTestCallingPhpIsDirect(): void
    {
        $trigger = IssueTrigger::from(Code::PHP, Code::Test);

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by test code calling into PHP runtime', $trigger->asString());
    }

    public function testThirdPartyCallingPhpIsIndirect(): void
    {
        $trigger = IssueTrigger::from(Code::PHP, Code::ThirdParty);

        $this->assertTrue($trigger->isIndirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by third-party code calling into PHP runtime', $trigger->asString());
    }

    public function testCanBeUnknown(): void
    {
        $trigger = IssueTrigger::unknown();

        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertTrue($trigger->isUnknown());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $trigger->asString());
    }
}
