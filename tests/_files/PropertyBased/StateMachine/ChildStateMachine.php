<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\StateMachine;

use PHPUnit\Framework\Attributes\InitializeStateMachine;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class ChildStateMachine extends ParentStateMachine
{
    #[InitializeStateMachine]
    public function initializeChild(): void
    {
    }

    #[StateMachineRule]
    public function ruleOfChild(): void
    {
    }

    #[StateMachineRule]
    public function ruleThatIsOverridden(): void
    {
    }

    #[StateMachineInvariant]
    public function invariantOfChild(): void
    {
    }

    #[StateMachineInvariant(atEnd: true)]
    public function invariantAtEndOfChild(): void
    {
    }
}
