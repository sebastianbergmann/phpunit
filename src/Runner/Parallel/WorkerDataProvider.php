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

use function array_key_exists;
use function is_int;
use function sprintf;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\DataProvider;
use PHPUnit\Metadata\Api\ProvidedData;
use PHPUnit\Runner\ErrorHandler;
use Throwable;

/**
 * Provides, inside a worker process, the data of the data-provided tests of
 * the unit the worker is running.
 *
 * The data a data provider provides does not travel from the parent process
 * to the worker: it may be anything a test author can construct — a closure,
 * a resource, an anonymous class, an object whose __serialize() keeps only
 * part of its state — and no transport would carry all of it faithfully.
 * Instead, the worker invokes the data providers again and picks, by the
 * name the parent process selected, the data set each test is to run with.
 * This is the data the tests would have received in a sequential run, in
 * the process they run in.
 *
 * A data provider is invoked once per test method and unit, however many of
 * the method's data sets the unit contains, and it is invoked the way the
 * parent process invoked it — through the same DataProvider API, inside the
 * error handler's test case context — so that the deprecations it triggers
 * are deferred to the tests it provides data for, as they are in a
 * sequential run. The events the invocation emits are discarded by the
 * worker: the parent process emitted them when it built the suite.
 *
 * What this asks of a data provider is that it provides the same data set
 * names in every process: a provider that keys its data sets by something
 * that differs between invocations — a random value, an object id, the time
 * — provides, in the worker, no data set of the name the parent selected.
 * Such a unit is reported as failed with a message that names the data set.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class WorkerDataProvider
{
    private readonly Emitter $emitter;

    /**
     * The data sets each data provider provided, keyed by test method.
     *
     * @var array<non-empty-string, array<int|string, ProvidedData>>
     */
    private array $providedData = [];

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * The data set of the given name that the data provider of the given test
     * method provides.
     *
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws WorkerException
     *
     * @return array<mixed>
     */
    public function dataSet(string $className, string $methodName, int|string $dataName): array
    {
        $key = $className . '::' . $methodName;

        if (!array_key_exists($key, $this->providedData)) {
            $this->providedData[$key] = $this->provide($className, $methodName);
        }

        if (!array_key_exists($dataName, $this->providedData[$key])) {
            throw new WorkerException(
                sprintf(
                    'The data provider for %s did not provide data set %s when the worker process invoked it; a data provider must provide the same data sets, under the same names, in every process',
                    $key,
                    $this->formatDataName($dataName),
                ),
            );
        }

        return $this->providedData[$key][$dataName]->value();
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws WorkerException
     *
     * @return array<int|string, ProvidedData>
     */
    private function provide(string $className, string $methodName): array
    {
        try {
            ErrorHandler::instance()->enterTestCaseContext($className, $methodName);

            $providedData = new DataProvider($this->emitter)->providedData($className, $methodName);
        } catch (Throwable $t) {
            throw new WorkerException(
                sprintf(
                    'The data provider for %s::%s failed when the worker process invoked it: %s',
                    $className,
                    $methodName,
                    $t->getMessage(),
                ),
            );
        } finally {
            ErrorHandler::instance()->leaveTestCaseContext();
        }

        if ($providedData === null) {
            throw new WorkerException(
                sprintf(
                    '%s::%s does not declare a data provider, yet the main process selected a data set for it',
                    $className,
                    $methodName,
                ),
            );
        }

        return $providedData;
    }

    private function formatDataName(int|string $dataName): string
    {
        if (is_int($dataName)) {
            return sprintf('#%d', $dataName);
        }

        return sprintf('"%s"', $dataName);
    }
}
