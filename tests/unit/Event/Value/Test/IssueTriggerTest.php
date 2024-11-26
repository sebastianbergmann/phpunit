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

#[CoversClass(TestTrigger::class)]
#[CoversClass(SelfTrigger::class)]
#[CoversClass(DirectTrigger::class)]
#[CoversClass(IndirectTrigger::class)]
#[CoversClass(UnknownTrigger::class)]
#[Small]
final class IssueTriggerTest extends TestCase
{
    public function testCanBeTest(): void
    {
        $trigger = IssueTrigger::test();

        $this->assertTrue($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by test code', $trigger->asString());
    }

    public function testCanBeSelf(): void
    {
        $trigger = IssueTrigger::self();

        $this->assertTrue($trigger->isSelf());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by first-party code calling into first-party code', $trigger->asString());
    }

    public function testCanBeDirect(): void
    {
        $trigger = IssueTrigger::direct();

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by first-party code calling into third-party code', $trigger->asString());
    }

    public function testCanBeIndirect(): void
    {
        $trigger = IssueTrigger::indirect();

        $this->assertTrue($trigger->isIndirect());
        $this->assertFalse($trigger->isTest());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isUnknown());
        $this->assertSame('issue triggered by third-party code', $trigger->asString());
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
