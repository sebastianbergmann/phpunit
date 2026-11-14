<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\TestCase;

final class StateMachineOfPropertyWithParametersTest extends TestCase
{
    /**
     * @param int<3, 5> $machine
     * @param int<7, 9> $items1
     */
    #[Property]
    public function testTakesValuesFromAPool(int $machine, int $items1): void
    {
        $this->runStateMachine(new MachineWithPoolOfPropertyWithParameters);

        $this->assertTrue(true);
    }
}

final class MachineWithPoolOfPropertyWithParameters
{
    #[StateMachineRule(produces: 'items')]
    public function item(): int
    {
        return 1;
    }

    #[StateMachineRule]
    public function take(#[FromPool('items', consume: true)] int $item): void
    {
    }
}
