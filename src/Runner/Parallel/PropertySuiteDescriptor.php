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
use function iterator_to_array;
use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The descriptor of the suite that checks a property. The suite travels to the
 * worker as a suite so that the property is checked inside the worker: its
 * examples are generated, run, and shrunk there, and only the events that the
 * property is reported with travel back.
 *
 * What a property is checked with cannot travel: its generators are objects
 * and closures. The worker derives them again, from the metadata of the test
 * method, by building the suite the way the parent process built it. The
 * examples are the same as in the parent process, as they are generated from
 * the random seed of the test run, which is part of the configuration that
 * the worker shares with the parent process.
 *
 * The counterexamples that were remembered when the property was last
 * falsified do travel: they are taken from the test run history, which only
 * the parent process has.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertySuiteDescriptor extends TestDescriptor
{
    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var list<list<non-negative-int>>
     */
    private array $rememberedCounterexamples;

    public static function fromTestSuite(PropertyTestSuite $suite): self
    {
        // The suite is iterated, not asked for its tests: iterating applies
        // the filter that test selection (--filter, --group, …) injected into
        // it, so that a test the selection excluded does not travel to the
        // worker.
        $aggregated = iterator_to_array($suite, false);

        assert(count($aggregated) === 1 && $aggregated[0] instanceof TestCase);

        return new self(
            $aggregated[0]->name(),
            $suite->rememberedCounterexamples(),
        );
    }

    /**
     * @param non-empty-string             $methodName
     * @param list<list<non-negative-int>> $rememberedCounterexamples
     */
    private function __construct(string $methodName, array $rememberedCounterexamples)
    {
        $this->methodName                = $methodName;
        $this->rememberedCounterexamples = $rememberedCounterexamples;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @param class-string<TestCase> $className
     *
     * @throws WorkerException
     */
    public function test(string $className, WorkerDataProvider $dataProvider): PropertyTestSuite
    {
        // The suite is not rebuilt from a described test case, as the suites
        // of retried and repeated test methods are: a test case that is
        // rebuilt from its description is configured for process isolation
        // when its test method asks for it, which does not apply to a
        // property. The test builder builds the examples of a property
        // without it.
        try {
            $suite = new TestBuilder(EventFacade::emitter())->build(
                new ReflectionClass($className),
                $this->methodName,
            );
        } catch (InvalidPropertyException $e) {
            throw new WorkerException(
                sprintf(
                    'The property %s::%s cannot be checked in a worker process: %s',
                    $className,
                    $this->methodName,
                    $e->getMessage(),
                ),
                $e->getCode(),
                $e,
            );
        }

        if (!$suite instanceof PropertyTestSuite) {
            throw new WorkerException(
                sprintf(
                    '%s::%s is a property in the main process, but not in a worker process',
                    $className,
                    $this->methodName,
                ),
            );
        }

        $suite->setRememberedCounterexamples($this->rememberedCounterexamples);

        return $suite;
    }
}
