<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessBudget::class)]
#[Small]
final class ProcessBudgetTest extends TestCase
{
    public function testProvidesAsManySlotsAsItsCapacity(): void
    {
        $budget = new ProcessBudget(2);

        $this->assertTrue($budget->acquire());
        $this->assertTrue($budget->acquire());
        $this->assertFalse($budget->acquire());
    }

    public function testAReleasedSlotCanBeAcquiredAgain(): void
    {
        $budget = new ProcessBudget(1);

        $this->assertTrue($budget->acquire());
        $this->assertFalse($budget->acquire());

        $budget->release();

        $this->assertTrue($budget->acquire());
    }
}
