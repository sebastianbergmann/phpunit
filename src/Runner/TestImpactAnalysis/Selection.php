<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function count;

/**
 * Which tests are to be run, and how many are not.
 *
 * Selecting fewer tests is only worth anything if what it leaves out is said
 * plainly: a test that was not run did not pass, and a run that does not say
 * how many tests it left out, and why, invites being read as a run of the
 * whole test suite.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Selection
{
    /**
     * @var ?list<non-empty-string>
     */
    private ?array $tests;

    /**
     * @var non-empty-string
     */
    private string $reason;
    private int $numberOfTestsThatWereConsidered;
    private ?RecordingTime $recordedAt;

    /**
     * @param non-empty-string $reason
     */
    public static function everything(string $reason, ?RecordingTime $recordedAt): self
    {
        return new self(null, $reason, 0, $recordedAt);
    }

    /**
     * @param list<non-empty-string> $tests
     * @param non-empty-string       $reason
     */
    public static function of(array $tests, string $reason, int $numberOfTestsThatWereConsidered, RecordingTime $recordedAt): self
    {
        return new self($tests, $reason, $numberOfTestsThatWereConsidered, $recordedAt);
    }

    /**
     * @param ?list<non-empty-string> $tests
     * @param non-empty-string        $reason
     */
    private function __construct(?array $tests, string $reason, int $numberOfTestsThatWereConsidered, ?RecordingTime $recordedAt)
    {
        $this->tests                           = $tests;
        $this->reason                          = $reason;
        $this->numberOfTestsThatWereConsidered = $numberOfTestsThatWereConsidered;
        $this->recordedAt                      = $recordedAt;
    }

    /**
     * @phpstan-assert-if-false !null $this->tests
     */
    public function isEverything(): bool
    {
        return $this->tests === null;
    }

    /**
     * Whether no test can be affected by what changed.
     *
     * There is nothing to run, which is not the same as there being no test to
     * run: the tests are there, and what changed cannot affect them.
     */
    public function isNothing(): bool
    {
        return !$this->isEverything() && $this->tests === [];
    }

    /**
     * @return list<non-empty-string>
     */
    public function tests(): array
    {
        if ($this->isEverything()) {
            return [];
        }

        return $this->tests;
    }

    public function numberOfTestsThatAreNotRun(): int
    {
        if ($this->isEverything()) {
            return 0;
        }

        return $this->numberOfTestsThatWereConsidered - count($this->tests);
    }

    /**
     * @return non-empty-string
     */
    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * When what this was made from was recorded, or null when nothing was
     * recorded.
     */
    public function recordedAt(): ?RecordingTime
    {
        return $this->recordedAt;
    }
}
