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

#[CoversClass(HtmlRenderer::class)]
#[Group('testdox')]
#[Small]
final class HtmlRendererTest extends TestCase
{
    public function test_Empty_input_renders_a_complete_HTML_document_with_no_test_results(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/html/empty.html',
            (new HtmlRenderer)->render([]),
        );
    }

    public function test_Successful_test_method_is_marked_as_success(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::success()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/html/successful_test.html',
            (new HtmlRenderer)->render($tests),
        );
    }

    public function test_Failed_test_method_is_marked_as_defect(): void
    {
        $tests = [
            'FooTest' => TestResultCollection::fromArray([
                $this->testResult('Foo', 'testBar', TestStatus::failure()),
            ]),
        ];

        $this->assertStringEqualsFile(
            __DIR__ . '/expectations/html/failed_test.html',
            (new HtmlRenderer)->render($tests),
        );
    }

    public function test_Renders_one_h2_section_per_test_class(): void
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
            __DIR__ . '/expectations/html/multiple_test_classes.html',
            (new HtmlRenderer)->render($tests),
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
            __DIR__ . '/expectations/html/success_demoted_to_failure.html',
            (new HtmlRenderer)->render($tests),
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
            __DIR__ . '/expectations/html/success_demoted_to_failure.html',
            (new HtmlRenderer)->render($tests),
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
