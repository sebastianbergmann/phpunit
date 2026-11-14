<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\TestFixture\Success;

#[CoversClass(PropertyTestSuite::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class PropertyTestSuiteTest extends TestCase
{
    public function testHoldsOneTestRegardlessOfTheNumberOfExamples(): void
    {
        $suite = $this->suite(new Success('testOne'));

        $this->assertCount(1, $suite);
        $this->assertSame(100, $suite->numberOfExamples());
    }

    public function testHasTheNameItIsCreatedWith(): void
    {
        $this->assertSame('PHPUnit\TestFixture\Success::testOne', $this->suite(new Success('testOne'))->name());
    }

    public function testSortIdDelegatesToTheTestItHolds(): void
    {
        $test = new Success('testOne');

        $this->assertSame($test->sortId(), $this->suite($test)->sortId());
    }

    public function testRemembersNoCounterexampleWithoutATestRunHistory(): void
    {
        $this->assertSame([], $this->suite(new Success('testOne'))->rememberedCounterexamples());
    }

    public function testTakesTheRememberedCounterexamplesFromTheTestRunHistory(): void
    {
        $test    = new Success('testOne');
        $history = $this->createMock(TestRunHistory::class);

        $history
            ->expects($this->once())
            ->method('counterexamples')
            ->with(TestRunHistoryId::fromReorderable($test))
            ->willReturn([[0, 5]])
            ->seal();

        $suite = $this->suite($test);

        $suite->setTestRunHistory($history);

        $this->assertSame([[0, 5]], $suite->rememberedCounterexamples());
    }

    public function testRemembersTheCounterexamplesItIsGivenRatherThanTheOnesOfTheTestRunHistory(): void
    {
        // A worker process of a parallel test run has no test run history:
        // the counterexamples travel to it with the description of the
        // property.
        $history = $this->createMock(TestRunHistory::class);

        $history->expects($this->never())->method('counterexamples')->seal();

        $suite = $this->suite(new Success('testOne'));

        $suite->setTestRunHistory($history);
        $suite->setRememberedCounterexamples([[1, 2, 3]]);

        $this->assertSame([[1, 2, 3]], $suite->rememberedCounterexamples());
    }

    private function suite(TestCase $test): PropertyTestSuite
    {
        return PropertyTestSuite::fromTestCase(
            'PHPUnit\TestFixture\Success::testOne',
            $this->createStub(Emitter::class),
            $test,
            null,
            100,
            static fn (): TestCase => new Success('testOne'),
        );
    }
}
