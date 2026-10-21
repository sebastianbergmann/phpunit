<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function is_int;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyChecked implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Code\Test $test;

    /**
     * @var non-negative-int
     */
    private int $seed;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesChecked;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesRejected;

    /**
     * @var list<array{label: non-empty-string, numberOfExamples: positive-int}>
     */
    private array $classifications;

    /**
     * @var list<array{label: non-empty-string, numberOfValues: positive-int, minimum: float|int, median: float|int, mean: float, percentile90: float|int, maximum: float|int}>
     */
    private array $observations;

    /**
     * @var list<array{label: non-empty-string, highestScore: float|int}>
     */
    private array $targets;

    /**
     * @param non-negative-int                                                                                                                                                    $seed
     * @param non-negative-int                                                                                                                                                    $numberOfExamplesChecked
     * @param non-negative-int                                                                                                                                                    $numberOfExamplesRejected
     * @param list<array{label: non-empty-string, numberOfExamples: positive-int}>                                                                                                $classifications
     * @param list<array{label: non-empty-string, numberOfValues: positive-int, minimum: float|int, median: float|int, mean: float, percentile90: float|int, maximum: float|int}> $observations
     * @param list<array{label: non-empty-string, highestScore: float|int}>                                                                                                       $targets
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, int $seed, int $numberOfExamplesChecked, int $numberOfExamplesRejected, array $classifications, array $observations, array $targets)
    {
        $this->telemetryInfo            = $telemetryInfo;
        $this->test                     = $test;
        $this->seed                     = $seed;
        $this->numberOfExamplesChecked  = $numberOfExamplesChecked;
        $this->numberOfExamplesRejected = $numberOfExamplesRejected;
        $this->classifications          = $classifications;
        $this->observations             = $observations;
        $this->targets                  = $targets;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): Code\Test
    {
        return $this->test;
    }

    /**
     * The seed of the random number generator of the property, which is
     * derived from the seed of the test run and the ID of the test.
     *
     * @return non-negative-int
     */
    public function seed(): int
    {
        return $this->seed;
    }

    /**
     * The number of examples for which the property was checked, including
     * the example that falsified it, if any.
     *
     * @return non-negative-int
     */
    public function numberOfExamplesChecked(): int
    {
        return $this->numberOfExamplesChecked;
    }

    /**
     * The number of examples that were discarded, either because assume() was
     * called with false or because no arguments could be generated for them.
     * They are not included in the number of examples checked.
     *
     * @return non-negative-int
     */
    public function numberOfExamplesRejected(): int
    {
        return $this->numberOfExamplesRejected;
    }

    /**
     * The labels with which TestCase::classify() classified the examples for
     * which the property was checked, in the order in which they were first
     * used, each with the number of examples that were classified with it.
     * An example can be classified with several labels, and explicit
     * examples are not classified.
     *
     * @return list<array{label: non-empty-string, numberOfExamples: positive-int}>
     */
    public function classifications(): array
    {
        return $this->classifications;
    }

    /**
     * The labels with which TestCase::observe() observed numeric values of
     * the examples for which the property was checked, in the order in which
     * they were first used, each with the number of values that were
     * observed with it and their minimum, median, mean, 90th percentile, and
     * maximum. The median and the 90th percentile are values that were
     * observed: the smallest ones that at least 50 and 90 percent of the
     * values are not greater than. Explicit examples do not observe values.
     *
     * @return list<array{label: non-empty-string, numberOfValues: positive-int, minimum: float|int, median: float|int, mean: float, percentile90: float|int, maximum: float|int}>
     */
    public function observations(): array
    {
        return $this->observations;
    }

    /**
     * The labels with which TestCase::target() targeted scores for the
     * examples for which the property was checked, in the order in which
     * they were first used, each with the highest score that an example
     * reached. Explicit examples do not target scores.
     *
     * @return list<array{label: non-empty-string, highestScore: float|int}>
     */
    public function targets(): array
    {
        return $this->targets;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $buffer = sprintf(
            'Test Property Checked (%s, seed: %d, examples: %d, rejected: %d)',
            $this->test->id(),
            $this->seed,
            $this->numberOfExamplesChecked,
            $this->numberOfExamplesRejected,
        );

        foreach ($this->classifications as $classification) {
            $buffer .= sprintf(
                PHP_EOL . '%s: %d (%.1f%%)',
                $classification['label'],
                $classification['numberOfExamples'],
                100 * $classification['numberOfExamples'] / $this->numberOfExamplesChecked,
            );
        }

        foreach ($this->observations as $observation) {
            $buffer .= sprintf(
                PHP_EOL . '%s: count %d, min %s, median %s, mean %s, p90 %s, max %s',
                $observation['label'],
                $observation['numberOfValues'],
                self::number($observation['minimum']),
                self::number($observation['median']),
                self::number($observation['mean']),
                self::number($observation['percentile90']),
                self::number($observation['maximum']),
            );
        }

        foreach ($this->targets as $target) {
            $buffer .= sprintf(
                PHP_EOL . '%s: highest targeted score %s',
                $target['label'],
                self::number($target['highestScore']),
            );
        }

        return $buffer;
    }

    /**
     * Formats an integer as it is, and a float with at most six significant
     * digits.
     */
    private static function number(float|int $number): string
    {
        if (is_int($number)) {
            return (string) $number;
        }

        return sprintf('%.6g', $number);
    }
}
