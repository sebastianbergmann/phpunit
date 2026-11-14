<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\StateMachine;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class MachineWithInvalidAttributeOnInvariant
{
    #[StateMachineRule]
    public function step(): void
    {
    }

    #[StateMachineInvariant(atTheEnd: true)]
    public function invariant(): void
    {
        Assert::fail('The invariant does not hold');
    }
}
