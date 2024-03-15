<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\DeprecationTrigger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelfTrigger::class)]
#[CoversClass(DirectTrigger::class)]
#[CoversClass(IndirectTrigger::class)]
#[Small]
final class IssueTriggerTest extends TestCase
{
    public function testCanBeSelf(): void
    {
        $trigger = IssueTrigger::self();

        $this->assertTrue($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
        $this->assertFalse($trigger->isIndirect());
    }

    public function testCanBeDirect(): void
    {
        $trigger = IssueTrigger::direct();

        $this->assertTrue($trigger->isDirect());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isIndirect());
    }

    public function testCanBeIndirect(): void
    {
        $trigger = IssueTrigger::indirect();

        $this->assertTrue($trigger->isIndirect());
        $this->assertFalse($trigger->isSelf());
        $this->assertFalse($trigger->isDirect());
    }
}
