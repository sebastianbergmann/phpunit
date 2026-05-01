<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(PlainTextRenderer::class)]
#[Group('testdox')]
#[Small]
final class PlainTextRendererTest extends TestCase
{
    public function test_Empty_input_renders_to_an_empty_string(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/empty.txt',
            (new PlainTextRenderer)->render([]),
        );
    }

    public function test_Successful_test_method_is_marked_with_x(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::success()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/successful_test.txt',
            (new PlainTextRenderer)->render($tests),
        );
    }

    public function test_Errored_failed_incomplete_or_skipped_test_method_is_marked_with_a_blank(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testErrored', TestStatus::error()),
                $this->testResult('Foo', 'testFailed', TestStatus::failure()),
                $this->testResult('Foo', 'testIncomplete', TestStatus::incomplete()),
                $this->testResult('Foo', 'testSkipped', TestStatus::skipped()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/non_successful_outcomes.txt',
            (new PlainTextRenderer)->render($tests),
        );
    }

    public function test_Renders_each_test_class_with_its_methods_and_a_trailing_blank_line(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::success()),
            ]),
            'BazTest' => TestResultCollection::fromArray([
                $this->testResult('Baz', 'testQux', TestStatus::success()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/multiple_test_classes.txt',
            (new PlainTextRenderer)->render($tests),
        );
    }

    public function test_A_subsequent_failure_demotes_the_outcome_of_a_previously_successful_test_method(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::success()),
                $this->testResult('Foo', 'testBar', TestStatus::failure()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/success_demoted_to_failure.txt',
            (new PlainTextRenderer)->render($tests),
        );
    }

    public function test_A_subsequent_success_does_not_promote_the_outcome_of_a_previously_failed_test_method(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::failure()),
                $this->testResult('Foo', 'testBar', TestStatus::success()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/plain-text/success_demoted_to_failure.txt',
            (new PlainTextRenderer)->render($tests),
        );
    }

    private function testResult(string $prettifiedClassName, string $methodName, TestStatus $status): TestResult
    {
        return new TestResult(
            new TestMethod(
                $prettifiedClassName . 'Test',
                $methodName,
                $prettifiedClassName . 'Test.php',
                1,
                TestDoxBuilder::fromClassNameAndMethodName($prettifiedClassName, $methodName),
                MetadataCollection::fromArray([]),
                TestDataCollection::fromArray([]),
            ),
            $status,
            null,
        );
    }
}
