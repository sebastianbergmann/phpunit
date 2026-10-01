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

use function assert;
use function count;
use function explode;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

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
            // The filter accepts every suite and applies the selection to the
            // tests inside it, so a member suite the selection has emptied —
            // the attempts of a retried data set, the repetitions of a
            // repeated one — is still yielded here. It runs nothing in a
            // sequential run and must therefore not travel to the worker.
            if ($member instanceof TestSuite && $member->isEmpty()) {
                continue;
            }

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
     * The name of a data provider test suite is the name of its test class
     * and the name of its test method, separated by "::" (see TestBuilder).
     *
     * @return non-empty-string
     */
    public function methodName(): string
    {
        assert(count(explode('::', $this->name)) === 2);

        [, $methodName] = explode('::', $this->name);

        assert($methodName !== '');

        return $methodName;
    }

    /**
     * @param class-string<TestCase> $className
     *
     * @throws WorkerException
     */
    public function test(string $className, WorkerDataProvider $dataProvider): DataProviderTestSuite
    {
        $suite = DataProviderTestSuite::empty($this->name, EventFacade::emitter());

        foreach ($this->members as $member) {
            $suite->addTest($member->test($className, $dataProvider));
        }

        return $suite;
    }
}
