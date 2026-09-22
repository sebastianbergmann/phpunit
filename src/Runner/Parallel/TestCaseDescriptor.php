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
use function is_array;
use function serialize;
use function sprintf;
use function unserialize;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestBuilder;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The descriptor of a single test case: the test method to run, together with
 * everything that distinguishes one invocation of that method from another.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestCaseDescriptor extends TestDescriptor
{
    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * Whether the test case runs with data from a data provider, and the name
     * of its data set.
     *
     * The data itself does not travel with the descriptor: it may be anything
     * a test author can construct, and no transport would carry all of it
     * faithfully. The worker process invokes the data provider again and
     * selects the data set by this name (see WorkerDataProvider). A test case
     * that does not use a data provider still carries a name — the empty
     * string that TestCase gives such a test case, or the name of a data set
     * that was provided as an empty array — so that the rebuilt test case
     * reports itself the way the described one did.
     */
    private bool $usesDataProvider;
    private int|string $dataName;

    /**
     * The input provided to the test method by the tests it depends on,
     * serialized.
     *
     * The input is not known until the depended-upon tests have run, so at
     * the time a unit is described it is empty; it travels all the same, so
     * that a described test case is rebuilt exactly. Keeping it serialized
     * while the descriptor travels is what allows the command that carries
     * the descriptor to be decoded with only the descriptor classes allowed
     * (see CommandStream): whatever classes the input consists of are
     * unserialized here, one test case at a time, and not while the command
     * is being decoded.
     */
    private string $dependencyInput;

    /**
     * @var positive-int
     */
    private int $repetition;

    /**
     * @var positive-int
     */
    private int $totalRepetitions;

    /**
     * @var positive-int
     */
    private int $attempt;

    /**
     * @var positive-int
     */
    private int $maxAttempts;

    /**
     * @param class-string $className
     *
     * @throws WorkerException
     */
    public static function fromTestCase(TestCase $test, string $className): self
    {
        try {
            $dependencyInput = serialize($test->dependencyInput());
        } catch (Throwable $t) {
            throw new WorkerException(
                sprintf(
                    'The tests of class %s cannot be run in parallel because their dependency input cannot be serialized: %s',
                    $className,
                    $t->getMessage(),
                ),
            );
        }

        return new self(
            $test->name(),
            $test->usesDataProvider(),
            $test->dataName(),
            $dependencyInput,
            $test->repetition(),
            $test->totalRepetitions(),
            $test->attempt(),
            $test->maxAttempts(),
        );
    }

    /**
     * @param non-empty-string $methodName
     * @param positive-int     $repetition
     * @param positive-int     $totalRepetitions
     * @param positive-int     $attempt
     * @param positive-int     $maxAttempts
     */
    private function __construct(string $methodName, bool $usesDataProvider, int|string $dataName, string $dependencyInput, int $repetition, int $totalRepetitions, int $attempt, int $maxAttempts)
    {
        $this->methodName       = $methodName;
        $this->usesDataProvider = $usesDataProvider;
        $this->dataName         = $dataName;
        $this->dependencyInput  = $dependencyInput;
        $this->repetition       = $repetition;
        $this->totalRepetitions = $totalRepetitions;
        $this->attempt          = $attempt;
        $this->maxAttempts      = $maxAttempts;
    }

    /**
     * @param class-string<TestCase> $className
     *
     * @throws WorkerException
     */
    public function test(string $className, WorkerDataProvider $dataProvider): TestCase
    {
        $test = new $className($this->methodName);

        $providedData = [];

        if ($this->usesDataProvider) {
            $providedData = $dataProvider->dataSet($className, $this->methodName, $this->dataName);
        }

        $dependencyInput = unserialize($this->dependencyInput);

        assert(is_array($dependencyInput));

        /** @var array<string, mixed> $dependencyInput */
        $test->setData($this->dataName, $providedData);
        $test->setDependencyInput($dependencyInput);
        $test->setRepetition($this->repetition, $this->totalRepetitions);
        $test->setAttempt($this->attempt, $this->maxAttempts);

        // The settings that TestBuilder derives from metadata and configuration
        // do not travel with the descriptor: they are derived again here, from
        // the same sources, which are available in the worker process as well.
        new TestBuilder(EventFacade::emitter())->configure($test, $className, $this->methodName);

        return $test;
    }
}
