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

abstract class ParentStateMachine
{
    #[InitializeStateMachine]
    public function initializeParent(): void
    {
    }

    #[InitializeStateMachine]
    public function initializeParentFurther(): void
    {
    }

    #[StateMachineRule]
    public function ruleOfParent(): void
    {
    }

    #[StateMachineRule]
    public function ruleThatIsOverridden(): void
    {
    }

    #[StateMachineInvariant]
    public function invariantOfParent(): void
    {
    }

    #[StateMachineInvariant(atEnd: true)]
    public function invariantAtEndOfParent(): void
    {
    }
}
