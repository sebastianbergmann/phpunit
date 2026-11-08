<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Event\Code\Counterexample;
use PHPUnit\Event\Code\OtherFalsification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(FalsificationReport::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FalsificationReportTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $counterexample     = new Counterexample([0, 2], ['value' => '2'], null, [], []);
        $otherFalsification = new OtherFalsification('RuntimeException at tests/FooTest.php:12', new Counterexample([3], ['value' => '3'], null, [], []));
        $report             = new FalsificationReport(1234, 2, 40, 17, true, false, $counterexample, [$otherFalsification], 4, 3);

        $this->assertSame(1234, $report->seed());
        $this->assertSame(2, $report->numberOfExamplesChecked());
        $this->assertSame(40, $report->numberOfShrinkingAttempts());
        $this->assertSame(17, $report->numberOfShrinkingSteps());
        $this->assertTrue($report->shrinkingBudgetExhausted());
        $this->assertFalse($report->shrinkingInterrupted());
        $this->assertSame($counterexample, $report->counterexample());
        $this->assertSame([$otherFalsification], $report->otherFalsifications());
        $this->assertSame(4, $report->numberOfReplays());
        $this->assertSame(3, $report->numberOfReproductions());
    }
}
