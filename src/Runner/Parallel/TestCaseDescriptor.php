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
     * The data provided to the test method by its data provider, and the input
     * provided to it by the tests it depends on, each serialized on its own.
     *
     * These are the only parts of a work unit that are under the control of
     * the tests themselves, and the only ones that can therefore contain
     * objects of arbitrary classes. Keeping them serialized while the
     * descriptor travels is what allows the command that carries it to be
     * decoded with only the descriptor classes allowed (see CommandStream):
     * the classes of the data are unserialized here, one test case at a time,
     * and not while the command is being decoded.
     */
    private string $data;
    private string $dependencyInput;
    private int|string $dataName;

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
            $data            = serialize($test->providedData());
            $dependencyInput = serialize($test->dependencyInput());
        } catch (Throwable $t) {
            throw new WorkerException(
                sprintf(
                    'The tests of class %s cannot be run in parallel because their data cannot be serialized: %s',
                    $className,
                    $t->getMessage(),
                ),
            );
        }

        return new self(
            $test->name(),
            $data,
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
    private function __construct(string $methodName, string $data, int|string $dataName, string $dependencyInput, int $repetition, int $totalRepetitions, int $attempt, int $maxAttempts)
    {
        $this->methodName       = $methodName;
        $this->data             = $data;
        $this->dataName         = $dataName;
        $this->dependencyInput  = $dependencyInput;
        $this->repetition       = $repetition;
        $this->totalRepetitions = $totalRepetitions;
        $this->attempt          = $attempt;
        $this->maxAttempts      = $maxAttempts;
    }

    /**
     * @param class-string<TestCase> $className
     */
    public function test(string $className): TestCase
    {
        $test = new $className($this->methodName);

        $providedData    = unserialize($this->data);
        $dependencyInput = unserialize($this->dependencyInput);

        assert(is_array($providedData));
        assert(is_array($dependencyInput));

        /** @var array<string, mixed> $dependencyInput */
        $test->setData($this->dataName, $providedData);
        $test->setDependencyInput($dependencyInput);
        $test->setRepetition($this->repetition, $this->totalRepetitions);
        $test->setAttempt($this->attempt, $this->maxAttempts);

        return $test;
    }
}
