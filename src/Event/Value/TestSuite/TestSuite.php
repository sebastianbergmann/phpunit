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

use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use PHPUnit\Runner\PhptTestCase;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuite
{
    private int $count;

    private string $name;

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

        return new self(
            $testSuite->count(),
            $testSuite->getName(),
            $groups,
            $testSuite->provides(),
            $testSuite->requires(),
            $testSuite->sortId(),
            TestCollection::fromArray($tests),
            $testSuite->warnings()
        );
    }

    public function __construct(int $size, string $name, array $groups, array $provides, array $requires, string $sortId, TestCollection $tests, array $warnings)
    {
        $this->count    = $size;
        $this->name     = $name;
        $this->groups   = $groups;
        $this->provides = $provides;
        $this->requires = $requires;
        $this->sortId   = $sortId;
        $this->tests    = $tests;
        $this->warnings = $warnings;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function name(): string
    {
        return $this->name;
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
}
