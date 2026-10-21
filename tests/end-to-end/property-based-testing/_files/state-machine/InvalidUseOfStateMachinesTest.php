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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/autoload.php';

final class InvalidUseOfStateMachinesTest extends TestCase
{
    public function testStateMachineIsRunOutsideOfAProperty(): void
    {
        $this->runStateMachine(new MachineWithoutRules);
    }

    #[Property]
    #[TestWith([0])]
    public function testStateMachineIsRunForAnExplicitExample(int $value): void
    {
        $this->runStateMachine(new MachineWithoutRules);
    }

    #[Property]
    public function testStateMachineIsRunTwice(): void
    {
        $store = new EventStore;

        $this->runStateMachine(new BankMachine(new Bank($store, false), $store, new BalanceProjection(false)), 1);
        $this->runStateMachine(new BankMachine(new Bank($store, false), $store, new BalanceProjection(false)), 1);
    }

    #[Property]
    public function testStateMachineIsRunWithoutSteps(): void
    {
        $this->runStateMachine(new MachineWithoutRules, 0);
    }

    #[Property]
    public function testStateMachineIsInvalid(): void
    {
        $this->runStateMachine(new MachineWithoutRules);
    }

    #[Property]
    public function testStepsOfStateMachineCannotBeGenerated(): void
    {
        $this->runStateMachine(new MachineWithUnsatisfiableFilter);
    }
}
