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

use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestSuite;

final class Info
{
    private int $count;

    private string $name;

    /**
     * @psalm-var array<string, list<class-string>>
     *
     * @var array<string, array<int, string>>
     */
    private array $groups;

    /**
     * @psalm-var list<ExecutionOrderDependency>
     *
     * @var array<int, ExecutionOrderDependency>
     */
    private array $provides;

    /**
     * @psalm-var list<ExecutionOrderDependency>
     *
     * @var array<int, ExecutionOrderDependency>
     */
    private array $requires;

    private string $sortId;

    /**
     * @psalm-var list<class-string>
     *
     * @var array<int, string>
     */
    private array $tests;

    /**
     * @psalm-var list<string>
     *
     * @var array<int, string>
     */
    private array $warnings;

    public static function fromTestSuite(TestSuite $testSuite): self
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
            $tests[] = get_class($test);
        }

        return new self(
            $testSuite->count(),
            $testSuite->getName(),
            $groups,
            $testSuite->provides(),
            $testSuite->requires(),
            $testSuite->sortId(),
            $tests,
            $testSuite->warnings()
        );
    }

    public function __construct(
        int $size,
        string $name,
        array $groups,
        array $provides,
        array $requires,
        string $sortId,
        array $tests,
        array $warnings
    ) {
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
     *
     * @return array<string, array<int, string>>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     *
     * @return array<int, ExecutionOrderDependency>
     */
    public function provides(): array
    {
        return $this->provides;
    }

    /**
     * @psalm-return list<ExecutionOrderDependency>
     *
     * @return array<int, ExecutionOrderDependency>
     */
    public function requires(): array
    {
        return $this->requires;
    }

    public function sortId(): string
    {
        return $this->sortId;
    }

    /**
     * @psalm-return list<class-string>
     *
     * @return array<int, string>
     */
    public function tests(): array
    {
        return $this->tests;
    }

    public function warnings(): array
    {
        return $this->warnings;
    }
}
