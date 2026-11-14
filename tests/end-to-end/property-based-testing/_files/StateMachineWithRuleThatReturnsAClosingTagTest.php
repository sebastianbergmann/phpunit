<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\TestCase;

final class StateMachineWithRuleThatReturnsAClosingTagTest extends TestCase
{
    #[Property]
    public function testClosingTagIsNotReturned(): void
    {
        $this->runStateMachine(new MachineWithRuleThatReturnsAClosingTag);
    }
}

final class MachineWithRuleThatReturnsAClosingTag
{
    private bool $closingTagWasReturned = false;

    #[StateMachineRule]
    public function closingTag(): string
    {
        $this->closingTagWasReturned = true;

        return '?> <?php echo "pwned";';
    }

    #[StateMachineInvariant]
    public function closingTagWasNotReturned(): void
    {
        Assert::assertFalse($this->closingTagWasReturned);
    }
}
