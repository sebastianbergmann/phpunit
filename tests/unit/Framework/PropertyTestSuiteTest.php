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
