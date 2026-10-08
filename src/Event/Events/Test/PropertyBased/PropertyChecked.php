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
     * @param non-negative-int $seed
     * @param non-negative-int $numberOfExamplesChecked
     * @param non-negative-int $numberOfExamplesRejected
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, int $seed, int $numberOfExamplesChecked, int $numberOfExamplesRejected)
    {
        $this->telemetryInfo            = $telemetryInfo;
        $this->test                     = $test;
        $this->seed                     = $seed;
        $this->numberOfExamplesChecked  = $numberOfExamplesChecked;
        $this->numberOfExamplesRejected = $numberOfExamplesRejected;
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
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Test Property Checked (%s, seed: %d, examples: %d, rejected: %d)',
            $this->test->id(),
            $this->seed,
            $this->numberOfExamplesChecked,
            $this->numberOfExamplesRejected,
        );
    }
}
