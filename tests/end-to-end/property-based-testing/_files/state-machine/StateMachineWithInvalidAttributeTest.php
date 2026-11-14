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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/autoload.php';

final class StateMachineWithInvalidAttributeTest extends TestCase
{
    #[Property]
    public function testInvariantHasAnAttributeThatCannotBeInstantiated(): void
    {
        $this->runStateMachine(new MachineWithInvalidAttributeOnInvariant);

        $this->assertTrue(true);
    }

    #[Property]
    public function testParameterOfRuleHasAnAttributeThatCannotBeInstantiated(): void
    {
        $this->runStateMachine(new MachineWithInvalidAttributeOnParameter);

        $this->assertTrue(true);
    }
}
