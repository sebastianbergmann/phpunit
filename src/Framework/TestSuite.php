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
use function array_unique;
use function call_user_func;
use function class_exists;
use function count;
use function implode;
use function is_callable;
use function is_file;
use function is_string;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use Iterator;
use IteratorAggregate;
use PHPUnit\Event;
use PHPUnit\Event\TestResultMapper;
use PHPUnit\Metadata\Api\Dependencies;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\TestSuiteLoader;
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
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     */
    protected ?bool $backupGlobals = null;

    /**
     * Enable or disable the backup and restoration of static attributes.
     */
    protected ?bool $backupStaticProperties  = null;
    protected bool $runTestInSeparateProcess = false;

    /**
     * The name of the test suite.
     */
    protected string $name = '';

    /**
     * The test groups of the test suite.
     *
     * @psalm-var array<string,list<Test>>
     */
    protected array $groups = [];

    /**
     * The number of tests in the test suite.
     */
    protected int $numTests         = -1;
    protected ?array $requiredTests = null;

    /**
     * @psalm-var list<Test>
     */
    private array $tests                             = [];
    private ?array $providedTests                    = null;
    private ?bool $beStrictAboutChangesToGlobalState = null;
    private ?Factory $iteratorFilter                 = null;

    /**
     * @psalm-var array<int,string>
     */
    private array $warnings = [];

    /**
     * Constructs a new TestSuite.
     *
     *   - PHPUnit\Framework\TestSuite() constructs an empty TestSuite.
     *
     *   - PHPUnit\Framework\TestSuite(ReflectionClass) constructs a
     *     TestSuite from the given class.
     *
     *   - PHPUnit\Framework\TestSuite(ReflectionClass, String)
     *     constructs a TestSuite from the given class with the given
     *     name.
     *
     *   - PHPUnit\Framework\TestSuite(String) either constructs a
     *     TestSuite from the given class (if the passed string is the
     *     name of an existing class) or constructs an empty TestSuite
     *     with the given name.
     *
     * @throws Exception
     */
    public function __construct(ReflectionClass|string $theClass = '', string $name = '')
    {
        if (!$theClass instanceof ReflectionClass) {
            if (class_exists($theClass, true)) {
                if ($name === '') {
                    $name = $theClass;
                }

                try {
                    $theClass = new ReflectionClass($theClass);
                } catch (ReflectionException $e) {
                    throw new Exception(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd
            } else {
                $this->setName($theClass);

                return;
            }
        }

        if (!$theClass->isSubclassOf(TestCase::class)) {
            $this->setName((string) $theClass);

            return;
        }

        if ($name !== '') {
            $this->setName($name);
        } else {
            $this->setName($theClass->getName());
        }

        $constructor = $theClass->getConstructor();

        if ($constructor !== null &&
            !$constructor->isPublic()) {
            $this->addTest(
                new WarningTestCase(
                    $theClass->getName(),
                    '',
                    sprintf(
                        'Class "%s" has no public constructor.',
                        $theClass->getName()
                    )
                )
            );

            return;
        }

        foreach ($theClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() === Assert::class) {
                continue;
            }

            if ($method->getDeclaringClass()->getName() === TestCase::class) {
                continue;
            }

            if (!TestUtil::isTestMethod($method)) {
                continue;
            }

            $this->addTestMethod($theClass, $method);
        }

        if (empty($this->tests)) {
            $this->addTest(
                new WarningTestCase(
                    $theClass->getName(),
                    '',
                    sprintf(
                        'No tests found in class "%s".',
                        $theClass->getName()
                    )
                )
            );
        }
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
        try {
            $class = new ReflectionClass($test);
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

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
     * @psalm-param object|class-string $testClass
     *
     * @throws Exception
     */
    public function addTestSuite(object|string $testClass): void
    {
        if (is_string($testClass) && !class_exists($testClass)) {
            throw InvalidArgumentException::create(
                1,
                'class name or object'
            );
        }

        if (is_string($testClass)) {
            try {
                $testClass = new ReflectionClass($testClass);
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd
        }

        if ($testClass instanceof self) {
            $this->addTest($testClass);
        } elseif ($testClass instanceof ReflectionClass) {
            if (!$testClass->isAbstract() && $testClass->isSubclassOf(TestCase::class)) {
                $this->addTest(new self($testClass));
            }
        } else {
            throw new Exception;
        }
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
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
     *
     * @todo refactor usage of numTests in DefaultResultPrinter
     */
    public function count(): int
    {
        $this->numTests = 0;

        foreach ($this as $test) {
            $this->numTests += count($test);
        }

        return $this->numTests;
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
            static function ($key): string
            {
                return (string) $key;
            },
            array_keys($this->groups)
        );
    }

    public function getGroupDetails(): array
    {
        return $this->groups;
    }

    /**
     * Set tests groups of the test case.
     */
    public function setGroupDetails(array $groups): void
    {
        $this->groups = $groups;
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

        $result->startTestSuite($this);

        $testSuiteValueObjectForEvents = Event\TestSuite\TestSuite::fromTestSuite($this);

        Event\Facade::emitter()->testSuiteStarted($testSuiteValueObjectForEvents);

        $test = null;

        $methodsCalledBeforeFirstTest = [];

        if (class_exists($this->name, false)) {
            try {
                foreach ($hookMethods['beforeClass'] as $beforeClassMethod) {
                    if ($this->methodDoesNotExistOrIsDeclaredInTestCase($beforeClassMethod)) {
                        continue;
                    }

                    if ($missingRequirements = (new Requirements)->requirementsNotSatisfiedFor($this->name, $beforeClassMethod)) {
                        $this->markTestSuiteSkipped(implode(PHP_EOL, $missingRequirements));
                    }

                    call_user_func([$this->name, $beforeClassMethod]);

                    $methodCalledBeforeFirstTest = new Event\Code\ClassMethod(
                        $this->name,
                        $beforeClassMethod
                    );

                    Event\Facade::emitter()->testBeforeFirstTestMethodCalled(
                        $this->name,
                        $methodCalledBeforeFirstTest
                    );

                    $methodsCalledBeforeFirstTest[] = $methodCalledBeforeFirstTest;
                }
            } catch (SkippedTestSuiteError $error) {
                foreach ($this->tests() as $test) {
                    $result->startTest($test);
                    $result->addFailure($test, $error, 0);
                    $result->endTest($test, 0);
                }

                $result->endTestSuite($this);

                return;
            } catch (Throwable $t) {
                $errorAdded = false;

                foreach ($this->tests() as $test) {
                    if ($result->shouldStop()) {
                        break;
                    }

                    $result->startTest($test);

                    if (!$errorAdded) {
                        $result->addError($test, $t, 0);

                        $errorAdded = true;
                    } else {
                        $result->addFailure(
                            $test,
                            new SkippedDueToErrorInHookMethodException,
                            0
                        );
                    }

                    $result->endTest($test, 0);
                }

                $result->endTestSuite($this);

                return;
            }
        }

        if (!empty($methodsCalledBeforeFirstTest)) {
            Event\Facade::emitter()->testBeforeFirstTestMethodFinished(
                $this->name,
                ...$methodsCalledBeforeFirstTest
            );
        }

        foreach ($this as $test) {
            if ($result->shouldStop()) {
                break;
            }

            if ($test instanceof TestCase || $test instanceof self) {
                if ($this->backupGlobals !== null) {
                    $test->setBackupGlobals($this->backupGlobals);
                }

                if ($this->backupStaticProperties !== null) {
                    $test->setBackupStaticProperties($this->backupStaticProperties);
                }

                if ($this->beStrictAboutChangesToGlobalState !== null) {
                    $test->setBeStrictAboutChangesToGlobalState($this->beStrictAboutChangesToGlobalState);
                }

                $test->setRunTestInSeparateProcess($this->runTestInSeparateProcess);
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

                    Event\Facade::emitter()->testAfterLastTestMethodCalled(
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
                    $result->addFailure($placeholderTest, $error, 0);
                    $result->endTest($placeholderTest, 0);
                }
            }
        }

        if (!empty($methodsCalledAfterLastTest)) {
            Event\Facade::emitter()->testAfterLastTestMethodFinished(
                $this->name,
                ...$methodsCalledAfterLastTest
            );
        }

        $result->endTestSuite($this);

        Event\Facade::emitter()->testSuiteFinished(
            $testSuiteValueObjectForEvents,
            (new TestResultMapper)->map($result)
        );
    }

    public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
    {
        $this->runTestInSeparateProcess = $runTestInSeparateProcess;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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
     *
     * @psalm-return never-return
     */
    public function markTestSuiteSkipped(string $message = ''): void
    {
        throw new SkippedTestSuiteError($message);
    }

    public function setBeStrictAboutChangesToGlobalState(bool $beStrictAboutChangesToGlobalState): void
    {
        if (null === $this->beStrictAboutChangesToGlobalState) {
            $this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
        }
    }

    public function setBackupGlobals(bool $backupGlobals): void
    {
        if (null === $this->backupGlobals) {
            $this->backupGlobals = $backupGlobals;
        }
    }

    public function setBackupStaticProperties(bool $backupStaticProperties): void
    {
        if (null === $this->backupStaticProperties) {
            $this->backupStaticProperties = $backupStaticProperties;
        }
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
     * @psalm-return array<int,string>
     */
    public function warnings(): array
    {
        return array_unique($this->warnings);
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
        $this->numTests      = -1;
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
}
