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

use Closure;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class ObservedMachine
{
    /**
     * @var ?Closure(): void
     */
    public ?Closure $whileStepIsTaken = null;

    /**
     * @var ?Closure(): void
     */
    public ?Closure $whileInvariantIsChecked = null;

    /**
     * @param int<1, 3> $amount
     */
    #[StateMachineRule]
    public function step(int $amount): void
    {
        if ($this->whileStepIsTaken !== null) {
            ($this->whileStepIsTaken)();
        }
    }

    #[StateMachineInvariant]
    public function invariant(): void
    {
        if ($this->whileInvariantIsChecked !== null) {
            ($this->whileInvariantIsChecked)();
        }
    }
}
