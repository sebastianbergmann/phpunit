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

use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;

/**
 * The descriptor of the suite that aggregates the tests of a data provider
 * method. Its members are described recursively so that the suite travels to
 * the worker as a suite and emits, inside the worker, the event envelope that
 * nests its tests in the logger output.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataProviderSuiteDescriptor extends TestDescriptor
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var list<TestDescriptor>
     */
    private array $members;

    /**
     * @param class-string $className
     *
     * @throws WorkerException
     */
    public static function fromTestSuite(DataProviderTestSuite $suite, string $className): self
    {
        $members = [];

        // The suite is iterated, not asked for its tests: iterating applies
        // the filter that test selection (--filter, --group, …) injected into
        // it, so that only the tests a sequential run would have run travel to
        // the worker.
        foreach ($suite as $member) {
            $members[] = TestDescriptor::from($member, $className);
        }

        return new self($suite->name(), $members);
    }

    /**
     * @param non-empty-string     $name
     * @param list<TestDescriptor> $members
     */
    private function __construct(string $name, array $members)
    {
        $this->name    = $name;
        $this->members = $members;
    }

    /**
     * @param class-string<TestCase> $className
     */
    public function test(string $className): DataProviderTestSuite
    {
        $suite = DataProviderTestSuite::empty($this->name);

        foreach ($this->members as $member) {
            $suite->addTest($member->test($className));
        }

        return $suite;
    }
}
