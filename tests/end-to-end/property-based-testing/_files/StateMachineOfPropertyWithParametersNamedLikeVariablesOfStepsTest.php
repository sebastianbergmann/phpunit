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
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\TestCase;

final class StateMachineOfPropertyWithParametersNamedLikeVariablesOfStepsTest extends TestCase
{
    /**
     * @param int<3, 5> $machine
     * @param int<7, 9> $counters1
     */
    #[Property]
    public function testCountersAreNotIncremented(int $machine, int $counters1): void
    {
        $this->runStateMachine(new MachineWithCountersThatAreNotToBeIncremented);
    }
}

final class MachineWithCountersThatAreNotToBeIncremented
{
    private bool $counterWasIncremented = false;

    #[StateMachineRule(produces: 'counters')]
    public function counter(): int
    {
        return 0;
    }

    #[StateMachineRule]
    public function increment(#[FromPool('counters')] int $counter): void
    {
        $this->counterWasIncremented = true;
    }

    #[StateMachineInvariant]
    public function noCounterWasIncremented(): void
    {
        Assert::assertFalse($this->counterWasIncremented);
    }
}
