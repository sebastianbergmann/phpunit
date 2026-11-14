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

use function preg_quote;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\PropertyBased\Properties\InvalidPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyWithIneligibleAttributesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use ReflectionClass;
use ReflectionProperty;

#[CoversClass(PropertySuiteDescriptor::class)]
#[UsesClass(WorkerDataProvider::class)]
#[Small]
final class PropertySuiteDescriptorTest extends TestCase
{
    public function testNamesTheTestMethodOfTheProperty(): void
    {
        $this->assertSame('testWithParameters', PropertySuiteDescriptor::fromTestSuite($this->suite())->methodName());
    }

    public function testRebuildsTheSuiteUnderTheNameItWasBuiltWith(): void
    {
        $suite = $this->suite();

        $this->assertSame($suite->name(), $this->rebuild($suite, ValidPropertiesTest::class)->name());
    }

    public function testRebuildsTheNumberOfExamplesThePropertyIsCheckedFor(): void
    {
        $suite = $this->suite();

        $this->assertSame($suite->numberOfExamples(), $this->rebuild($suite, ValidPropertiesTest::class)->numberOfExamples());
    }

    public function testRebuildsTheTestOfTheProperty(): void
    {
        $tests = $this->rebuild($this->suite(), ValidPropertiesTest::class)->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(ValidPropertiesTest::class, $tests[0]);
        $this->assertSame('testWithParameters', $tests[0]->name());
    }

    public function testCarriesTheCounterexamplesThatAreRememberedInTheTestRunHistory(): void
    {
        // Only the main process has the test run history, so what it
        // remembers for the property travels with the description.
        $history = $this->createStub(TestRunHistory::class);

        $history->method('counterexamples')->willReturn([[0, 5], [1, 2, 3]])->seal();

        $suite = $this->suite();

        $suite->setTestRunHistory($history);

        $this->assertSame([[0, 5], [1, 2, 3]], $this->rebuild($suite, ValidPropertiesTest::class)->rememberedCounterexamples());
    }

    public function testRebuildsASuiteThatRemembersNoCounterexampleWhenNoneIsRemembered(): void
    {
        $this->assertSame([], $this->rebuild($this->suite(), ValidPropertiesTest::class)->rememberedCounterexamples());
    }

    public function testDoesNotConfigureTheTestOfTheRebuiltSuiteForProcessIsolation(): void
    {
        // Process isolation does not apply to a property. A test case that
        // is rebuilt from its description would be configured for it when
        // its test method asks for it, which is why the suite of a property
        // is not rebuilt from a described test case.
        $suite = $this->suiteFor(PropertyWithIneligibleAttributesTest::class, 'testOne');

        // The warnings that are emitted while the suite is built again must
        // not become warnings of the test run that runs this test.
        $facade = new Facade;

        $facade->seal();

        $property = new ReflectionProperty(Facade::class, 'instance');
        $instance = $property->getValue();

        $property->setValue(null, $facade);

        try {
            $rebuilt = $this->rebuild($suite, PropertyWithIneligibleAttributesTest::class);
        } finally {
            $property->setValue(null, $instance);
        }

        $this->assertNull(new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($rebuilt->tests()[0]));
    }

    public function testDescribesAPropertyThatTestSelectionSelected(): void
    {
        // A suite the selection emptied is not described at all: it is
        // skipped where the members of a unit are collected.
        $suite = $this->suite();

        $factory = new Factory;

        $factory->addIncludeNameFilter('testWithParameters');

        $suite->injectFilter($factory);

        $this->assertCount(1, $this->rebuild($suite, ValidPropertiesTest::class)->tests());
    }

    public function testReportsAPropertyThatCannotBeCheckedInTheWorker(): void
    {
        // The main process does not describe a property that cannot be
        // checked, as it cannot build its suite. The class of the property
        // can differ in the worker, though, when it is loaded from a file
        // that changed since the main process loaded it.
        $suite = $this->suiteOf(new InvalidPropertiesTest('testWithUnsupportedType'));

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessageMatches('/^The property ' . preg_quote(InvalidPropertiesTest::class, '/') . '::testWithUnsupportedType cannot be checked in a worker process: /');

        $this->rebuild($suite, InvalidPropertiesTest::class);
    }

    public function testReportsATestMethodThatIsNotAPropertyInTheWorker(): void
    {
        $suite = $this->suiteOf(new WorkerFirstTest('testStartsTheProcessLocalCounter'));

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessageIsOrContains(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter is a property in the main process, but not in a worker process');

        $this->rebuild($suite, WorkerFirstTest::class);
    }

    /**
     * The suite of a property, as the main process builds it.
     */
    private function suite(): PropertyTestSuite
    {
        return $this->suiteFor(ValidPropertiesTest::class, 'testWithParameters');
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function suiteFor(string $className, string $methodName): PropertyTestSuite
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass($className),
            $methodName,
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);

        return $suite;
    }

    /**
     * A suite for a test method that the test builder does not build the
     * suite of a property for.
     */
    private function suiteOf(TestCase $test): PropertyTestSuite
    {
        return PropertyTestSuite::fromTestCase(
            $test::class . '::' . $test->name(),
            $this->createStub(Emitter::class),
            $test,
            null,
            100,
            static fn (): TestCase => $test,
        );
    }

    /**
     * Describe the suite as the main process does and rebuild it as the
     * worker process does.
     *
     * @param class-string<TestCase> $className
     */
    private function rebuild(PropertyTestSuite $suite, string $className): PropertyTestSuite
    {
        return PropertySuiteDescriptor::fromTestSuite($suite)->test(
            $className,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );
    }
}
