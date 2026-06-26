<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Phpt extends Test
{
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
     * @param non-empty-string $file
     * @param positive-int     $repetition
     * @param positive-int     $totalRepetitions
     * @param positive-int     $attempt
     * @param positive-int     $maxAttempts
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $file, int $repetition = 1, int $totalRepetitions = 1, int $attempt = 1, int $maxAttempts = 1)
    {
        parent::__construct($file);

        $this->repetition       = $repetition;
        $this->totalRepetitions = $totalRepetitions;
        $this->attempt          = $attempt;
        $this->maxAttempts      = $maxAttempts;
    }

    public function isPhpt(): true
    {
        return true;
    }

    /**
     * @return positive-int
     */
    public function repetition(): int
    {
        return $this->repetition;
    }

    /**
     * @return positive-int
     */
    public function totalRepetitions(): int
    {
        return $this->totalRepetitions;
    }

    public function isRepeated(): bool
    {
        return $this->totalRepetitions > 1;
    }

    /**
     * @return positive-int
     */
    public function attempt(): int
    {
        return $this->attempt;
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function isRetried(): bool
    {
        return $this->maxAttempts > 1;
    }

    /**
     * @return non-empty-string
     */
    public function id(): string
    {
        return $this->annotate($this->file());
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->annotate($this->file());
    }

    /**
     * @param non-empty-string $buffer
     *
     * @return non-empty-string
     */
    private function annotate(string $buffer): string
    {
        if ($this->totalRepetitions > 1) {
            $buffer .= sprintf(
                ' (repetition %d of %d)',
                $this->repetition,
                $this->totalRepetitions,
            );
        }

        if ($this->attempt > 1) {
            $buffer .= sprintf(
                ' (attempt %d of %d)',
                $this->attempt,
                $this->maxAttempts,
            );
        }

        return $buffer;
    }
}
