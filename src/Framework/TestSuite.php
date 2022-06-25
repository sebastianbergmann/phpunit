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

use const PHP_EOL;
use function array_keys;
use function array_map;
use function assert;
use function call_user_func;
use function class_exists;
use function count;
use function implode;
use function is_callable;
use function is_file;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use Iterator;
use IteratorAggregate;
use PHPUnit\Event;
use PHPUnit\Metadata\Api\Dependencies;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TestRunner\TestResult\Facade;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\Util\Reflection;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class TestSuite implements IteratorAggregate, Reorderable, SelfDescribing, Test
{
    protected string $name = '';

    /**
     * @psalm-var array<string,list<Test>>
     */
    protected array $groups         = [];
    protected ?array $requiredTests = null;

    /**
     * @psalm-var list<Test>
     */
    private array $tests             = [];
    private ?array $providedTests    = null;
    private ?Factory $iteratorFilter = null;
    private bool $stopOnError;
    private bool $stopOnFailure;
    private bool $stopOnWarning;
    private bool $stopOnRisky;
    private bool $stopOnIncomplete;
    private bool $stopOnSkipped;
    private bool $stopOnDefect;

    public static function empty(string $name = null): static
    {
        if ($name === null) {
            $name = '';
        }

        return new static($name);
    }

    /**
     * @psalm-param class-string $className
     */
    public static function fromClassName(string $className): static
    {
        try {
            $class = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        return static::fromClassReflector($class);
    }

    public static function fromClassReflector(ReflectionClass $class): static
    {
        $testSuite = new static($class->getName());

        $constructor = $class->getConstructor();

        if ($constructor !== null && !$constructor->isPublic()) {
            $message = sprintf(
                'Class "%s" has no public constructor.',
                $class->getName()
            );

            Event\Facade::emitter()->testRunnerTriggeredWarning($message);

            $testSuite->addTest(
                new WarningTestCase(
                    $class->getName(),
                    '',
                    $message
                )
            );

            return $testSuite;
        }

        foreach ((new Reflection)->publicMethodsInTestClass($class) as $method) {
            if ($method->getDeclaringClass()->getName() === Assert::class) {
                continue;
            }

            if ($method->getDeclaringClass()->getName() === TestCase::class) {
                continue;
            }

            if (!TestUtil::isTestMethod($method)) {
                continue;
            }

            $testSuite->addTestMethod($class, $method);
        }

        if (count($testSuite) === 0) {
            $message = sprintf(
                'No tests found in class "%s".',
                $class->getName()
            );

            Event\Facade::emitter()->testRunnerTriggeredWarning($message);

            $testSuite->addTest(
                new WarningTestCase(
                    $class->getName(),
                    '',
                    $message
                )
            );
        }

        return $testSuite;
    }

    private function __construct(string $name)
    {
        $this->name = $name;

        $configuration = Registry::get();

        $this->stopOnError      = $configuration->stopOnError();
        $this->stopOnFailure    = $configuration->stopOnFailure();
        $this->stopOnWarning    = $configuration->stopOnWarning();
        $this->stopOnRisky      = $configuration->stopOnRisky();
        $this->stopOnIncomplete = $configuration->stopOnIncomplete();
        $this->stopOnSkipped    = $configuration->stopOnSkipped();
        $this->stopOnDefect     = $configuration->stopOnDefect();
    }

    /**
     * Returns a string representation of the test suite.
     */
    public function toString(): string
    {
        return $this->getName();
    }

    /**
     * Adds a test to the suite.
     */
    public function addTest(Test $test, array $groups = []): void
    {
        $class = new ReflectionClass($test);

        if (!$class->isAbstract()) {
            $this->tests[] = $test;
            $this->clearCaches();

            if ($test instanceof self && empty($groups)) {
                $groups = $test->getGroups();
            }

            if ($this->containsOnlyVirtualGroups($groups)) {
                $groups[] = 'default';
            }

            foreach ($groups as $group) {
                if (!isset($this->groups[$group])) {
                    $this->groups[$group] = [$test];
                } else {
                    $this->groups[$group][] = $test;
                }
            }

            if ($test instanceof TestCase) {
                $test->setGroups($groups);
            }
        }
    }

    /**
     * Adds the tests from the given class to the suite.
     *
     * @throws Exception
     */
    public function addTestSuite(ReflectionClass $testClass): void
    {
        if ($testClass->isAbstract()) {
            throw new Exception(
                sprintf(
                    'Class %s is abstract',
                    $testClass->getName()
                )
            );
        }

        if (!$testClass->isSubclassOf(TestCase::class)) {
            throw new Exception(
                sprintf(
                    'Class %s is not a subclass of %s',
                    $testClass->getName(),
                    TestCase::class
                )
            );
        }

        $this->addTest(self::fromClassReflector($testClass));
    }

    /**
     * Wraps both <code>addTest()</code> and <code>addTestSuite</code>
     * as well as the separate import statements for the user's convenience.
     *
     * If the named file cannot be read or there are no new tests that can be
     * added, a <code>PHPUnit\Framework\WarningTestCase</code> will be created instead,
     * leaving the current test run untouched.
     *
     * @throws \PHPUnit\Runner\Exception
     * @throws Exception
     */
    public function addTestFile(string $filename): void
    {
        if (is_file($filename) && str_ends_with($filename, '.phpt')) {
            $this->addTest(new PhptTestCase($filename));

            return;
        }

        $this->addTestSuite(
            (new TestSuiteLoader)->load($filename)
        );
    }

    /**
     * Wrapper for addTestFile() that adds multiple test files.
     *
     * @throws Exception
     */
    public function addTestFiles(iterable $fileNames): void
    {
        foreach ($fileNames as $filename) {
            $this->addTestFile((string) $filename);
        }
    }

    /**
     * Counts the number of test cases that will be run by this test.
     */
    public function count(): int
    {
        $numTests = 0;

        foreach ($this as $test) {
            $numTests += count($test);
        }

        return $numTests;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Returns the name of the suite.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the test groups of the suite.
     *
     * @psalm-return list<string>
     */
    public function getGroups(): array
    {
        return array_map(
            'strval',
            array_keys($this->groups)
        );
    }

    public function getGroupDetails(): array
    {
        return $this->groups;
    }

    /**
     * Runs the tests and collects their result in a TestResult.
     *
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException
     * @throws CodeCoverageException
     * @throws Warning
     */
    public function run(TestResult $result): void
    {
        if (count($this) === 0) {
            return;
        }

        /** @psalm-var class-string $className */
        $className   = $this->name;
        $hookMethods = (new HookMethods)->hookMethods($className);

        $emitter                       = Event\Facade::emitter();
        $testSuiteValueObjectForEvents = Event\TestSuite\TestSuite::fromTestSuite($this);

        $emitter->testSuiteStarted($testSuiteValueObjectForEvents);

        $methodsCalledBeforeFirstTest = [];
        $test                         = null;

        if (class_exists($this->name, false)) {
            try {
                foreach ($hookMethods['beforeClass'] as $beforeClassMethod) {
                    if ($this->methodDoesNotExistOrIsDeclaredInTestCase($beforeClassMethod)) {
                        continue;
                    }

                    if ($missingRequirements = (new Requirements)->requirementsNotSatisfiedFor($this->name, $beforeClassMethod)) {
                        $this->markTestSuiteSkipped(implode(PHP_EOL, $missingRequirements));
                    }

                    $methodCalledBeforeFirstTest = new Event\Code\ClassMethod(
                        $this->name,
                        $beforeClassMethod
                    );

                    $emitter->testBeforeFirstTestMethodCalled(
                        $this->name,
                        $methodCalledBeforeFirstTest
                    );

                    $methodsCalledBeforeFirstTest[] = $methodCalledBeforeFirstTest;

                    call_user_func([$this->name, $beforeClassMethod]);
                }
            } catch (SkippedTestSuiteError $error) {
                foreach ($this->tests() as $test) {
                    $result->startTest($test);
                    $result->addFailure($test, $error);
                }

                return;
            } catch (Throwable $t) {
                assert(isset($methodCalledBeforeFirstTest));

                $emitter->testBeforeFirstTestMethodErrored(
                    $this->name,
                    $methodCalledBeforeFirstTest,
                    Event\Code\Throwable::from($t)
                );

                if (!empty($methodsCalledBeforeFirstTest)) {
                    $emitter->testBeforeFirstTestMethodFinished(
                        $this->name,
                        ...$methodsCalledBeforeFirstTest
                    );
                }

                $errorAdded = false;

                foreach ($this->tests() as $test) {
                    $result->startTest($test);

                    if (!$errorAdded) {
                        $result->addError($test, $t);

                        $errorAdded = true;
                    } else {
                        $result->addFailure(
                            $test,
                            new SkippedDueToErrorInHookMethodException,
                        );
                    }
                }

                return;
            }
        }

        if (!empty($methodsCalledBeforeFirstTest)) {
            $emitter->testBeforeFirstTestMethodFinished(
                $this->name,
                ...$methodsCalledBeforeFirstTest
            );
        }

        foreach ($this as $test) {
            if ($this->shouldStop()) {
                break;
            }

            $test->run($result);
        }

        $methodsCalledAfterLastTest = [];

        if (class_exists($this->name, false)) {
            foreach ($hookMethods['afterClass'] as $afterClassMethod) {
                if ($this->methodDoesNotExistOrIsDeclaredInTestCase($afterClassMethod)) {
                    continue;
                }

                try {
                    call_user_func([$this->name, $afterClassMethod]);

                    $methodCalledAfterLastTest = new Event\Code\ClassMethod(
                        $this->name,
                        $afterClassMethod
                    );

                    $emitter->testAfterLastTestMethodCalled(
                        $this->name,
                        $methodCalledAfterLastTest
                    );

                    $methodsCalledAfterLastTest[] = $methodCalledAfterLastTest;
                } catch (Throwable $t) {
                    $message = "Exception in {$this->name}::{$afterClassMethod}" . PHP_EOL . $t->getMessage();
                    $error   = new SyntheticError($message, 0, $t->getFile(), $t->getLine(), $t->getTrace());

                    $placeholderTest = clone $test;
                    $placeholderTest->setName($afterClassMethod);

                    $result->startTest($placeholderTest);
                    $result->addFailure($placeholderTest, $error);
                }
            }
        }

        if (!empty($methodsCalledAfterLastTest)) {
            $emitter->testAfterLastTestMethodFinished(
                $this->name,
                ...$methodsCalledAfterLastTest
            );
        }

        $emitter->testSuiteFinished($testSuiteValueObjectForEvents);
    }

    /**
     * Returns the tests as an enumeration.
     *
     * @psalm-return list<Test>
     */
    public function tests(): array
    {
        return $this->tests;
    }

    /**
     * Set tests of the test suite.
     *
     * @psalm-param list<Test> $tests
     */
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }

    /**
     * Mark the test suite as skipped.
     *
     * @throws SkippedTestSuiteError
     */
    public function markTestSuiteSkipped(string $message = ''): never
    {
        throw new SkippedTestSuiteError($message);
    }

    /**
     * Returns an iterator for this test suite.
     */
    public function getIterator(): Iterator
    {
        $iterator = new TestSuiteIterator($this);

        if ($this->iteratorFilter !== null) {
            $iterator = $this->iteratorFilter->factory($iterator, $this);
        }

        return $iterator;
    }

    public function injectFilter(Factory $filter): void
    {
        $this->iteratorFilter = $filter;

        foreach ($this as $test) {
            if ($test instanceof self) {
                $test->injectFilter($filter);
            }
        }
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        if ($this->providedTests === null) {
            $this->providedTests = [];

            if (is_callable($this->sortId(), true)) {
                $this->providedTests[] = new ExecutionOrderDependency($this->sortId());
            }

            foreach ($this->tests as $test) {
                if (!($test instanceof Reorderable)) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $this->providedTests = ExecutionOrderDependency::mergeUnique($this->providedTests, $test->provides());
            }
        }

        return $this->providedTests;
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        if ($this->requiredTests === null) {
            $this->requiredTests = [];

            foreach ($this->tests as $test) {
                if (!($test instanceof Reorderable)) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $this->requiredTests = ExecutionOrderDependency::mergeUnique(
                    ExecutionOrderDependency::filterInvalid($this->requiredTests),
                    $test->requires()
                );
            }

            $this->requiredTests = ExecutionOrderDependency::diff($this->requiredTests, $this->provides());
        }

        return $this->requiredTests;
    }

    public function sortId(): string
    {
        return $this->getName() . '::class';
    }

    /**
     * @throws Exception
     */
    protected function addTestMethod(ReflectionClass $class, ReflectionMethod $method): void
    {
        $methodName = $method->getName();

        $test = (new TestBuilder)->build($class, $methodName);

        if ($test instanceof TestCase || $test instanceof DataProviderTestSuite) {
            $test->setDependencies(
                Dependencies::dependencies($class->getName(), $methodName)
            );
        }

        $this->addTest(
            $test,
            (new Groups)->groups($class->getName(), $methodName)
        );
    }

    private function clearCaches(): void
    {
        $this->providedTests = null;
        $this->requiredTests = null;
    }

    private function containsOnlyVirtualGroups(array $groups): bool
    {
        foreach ($groups as $group) {
            if (!str_starts_with($group, '__phpunit_')) {
                return false;
            }
        }

        return true;
    }

    private function methodDoesNotExistOrIsDeclaredInTestCase(string $methodName): bool
    {
        $reflector = new ReflectionClass($this->name);

        return !$reflector->hasMethod($methodName) ||
               $reflector->getMethod($methodName)->getDeclaringClass()->getName() === TestCase::class;
    }

    private function shouldStop(): bool
    {
        if (($this->stopOnDefect || $this->stopOnError) && Facade::hasTestErroredEvents()) {
            return true;
        }

        if (($this->stopOnDefect || $this->stopOnFailure) && Facade::hasTestFailedEvents()) {
            return true;
        }

        if (($this->stopOnDefect || $this->stopOnWarning) && Facade::hasTestPassedWithWarningEvents()) {
            return true;
        }

        if (($this->stopOnDefect || $this->stopOnRisky) && Facade::hasTestConsideredRiskyEvents()) {
            return true;
        }

        if ($this->stopOnSkipped && Facade::hasTestSkippedEvents()) {
            return true;
        }

        if ($this->stopOnIncomplete && Facade::hasTestMarkedIncompleteEvents()) {
            return true;
        }

        return false;
    }
}
