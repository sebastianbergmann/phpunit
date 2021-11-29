<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use function class_exists;
use function explode;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class TestSuite
{
    private string $name;
    private int $count;

    /**
     * @psalm-var array<string, list<class-string>>
     */
    private array $groups;

    /**
     * @psalm-var list<ExecutionOrderDependency>
     */
    private array $provides;

    /**
     * @psalm-var list<ExecutionOrderDependency>
     */
    private array $requires;
    private string $sortId;
    private TestCollection $tests;

    /**
     * @psalm-var list<string>
     */
    private array $warnings;

    public static function fromTestSuite(FrameworkTestSuite $testSuite): self
    {
        $groups = [];

        foreach ($testSuite->getGroupDetails() as $groupName => $tests) {
            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [];
            }

            foreach ($tests as $test) {
                $groups[$groupName][] = get_class($test);
            }
        }

        $tests = [];

        foreach ($testSuite->tests() as $test) {
            if ($test instanceof TestCase || $test instanceof PhptTestCase) {
                $tests[] = $test->valueObjectForEvents();
            }
        }

        if ($testSuite instanceof DataProviderTestSuite) {
            [$className, $methodName] = explode('::', $testSuite->getName());

            try {
                $reflector = new ReflectionMethod($className, $methodName);

                return new TestSuiteForTestMethodWithDataProvider(
                    $testSuite->getName(),
                    $testSuite->count(),
                    $groups,
                    $testSuite->provides(),
                    $testSuite->requires(),
                    $testSuite->sortId(),
                    TestCollection::fromArray($tests),
                    $testSuite->warnings(),
                    $className,
                    $methodName,
                    $reflector->getFileName(),
                    $reflector->getStartLine(),
                );
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

        if (class_exists($testSuite->getName())) {
            try {
                $reflector = new ReflectionClass($testSuite->getName());

                return new TestSuiteForTestClass(
                    $testSuite->getName(),
                    $testSuite->count(),
                    $groups,
                    $testSuite->provides(),
                    $testSuite->requires(),
                    $testSuite->sortId(),
                    TestCollection::fromArray($tests),
                    $testSuite->warnings(),
                    $reflector->getFileName(),
                    $reflector->getStartLine(),
                );
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

        return new TestSuiteWithName(
            $testSuite->getName(),
            $testSuite->count(),
            $groups,
            $testSuite->provides(),
            $testSuite->requires(),
            $testSuite->sortId(),
            TestCollection::fromArray($tests),
            $testSuite->warnings(),
        );
    }

    public function __construct(string $name, int $size, array $groups, array $provides, array $requires, string $sortId, TestCollection $tests, array $warnings)
    {
        $this->name     = $name;
        $this->count    = $size;
        $this->groups   = $groups;
        $this->provides = $provides;
        $this->requires = $requires;
        $this->sortId   = $sortId;
        $this->tests    = $tests;
        $this->warnings = $warnings;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @psalm-return array<string, list<class-string>>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        return $this->provides;
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        return $this->requires;
    }

    public function sortId(): string
    {
        return $this->sortId;
    }

    public function tests(): TestCollection
    {
        return $this->tests;
    }

    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @psalm-assert-if-true TestSuiteWithName $this
     */
    public function isWithName(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestSuiteForTestClass $this
     */
    public function isForTestClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestSuiteForTestMethodWithDataProvider $this
     */
    public function isForTestMethodWithDataProvider(): bool
    {
        return false;
    }
}
