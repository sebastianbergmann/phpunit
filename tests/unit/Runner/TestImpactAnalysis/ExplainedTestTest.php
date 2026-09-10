<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExplainedTest::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ExplainedTestTest extends TestCase
{
    public function testKnowsWhyATestIsRun(): void
    {
        $test = ExplainedTest::from('FooTest::testOne', SelectionReason::NothingIsKnownAboutIt);

        $this->assertSame('FooTest::testOne', $test->test());
        $this->assertSame(SelectionReason::NothingIsKnownAboutIt, $test->reason());
        $this->assertFalse($test->hasFile());
        $this->assertNull($test->file());
    }

    public function testKnowsWhichFileMakesATestRun(): void
    {
        $test = ExplainedTest::from(
            'FooTest::testOne',
            SelectionReason::DependsOnSomethingThatChanged,
            '/src/Foo.php',
        );

        $this->assertSame('FooTest::testOne', $test->test());
        $this->assertSame(SelectionReason::DependsOnSomethingThatChanged, $test->reason());
        $this->assertTrue($test->hasFile());
        $this->assertSame('/src/Foo.php', $test->file());
    }
}
