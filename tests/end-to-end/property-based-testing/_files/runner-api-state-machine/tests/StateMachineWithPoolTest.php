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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/MachineWithPool.php';

final class StateMachineWithPoolTest extends TestCase
{
    #[Property]
    public function testTakesValuesFromAPool(): void
    {
        $this->runStateMachine(new MachineWithPool);

        $this->assertTrue(true);
    }
}
