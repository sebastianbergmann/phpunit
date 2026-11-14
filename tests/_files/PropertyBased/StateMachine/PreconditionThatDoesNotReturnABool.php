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

use PHPUnit\Framework\Attributes\StateMachineRule;

final class PreconditionThatDoesNotReturnABool
{
    #[StateMachineRule(precondition: 'canStep')]
    public function step(): void
    {
    }

    public function canStep(): int
    {
        return 1;
    }
}
