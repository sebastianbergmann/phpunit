<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Result
{
    private int $count;
    private FailureCollection $errors;
    private FailureCollection $failures;
    private FailureCollection $notImplemented;
    private FailureCollection $risky;
    private FailureCollection $skipped;
    private FailureCollection $warnings;
    private array $passed;

    /**
     * @psalm-var list<class-string>
     *
     * @var array<int, string>
     */
    private array $passedClasses;

    /**
     * @psalm-param list<class-string> $passedClasses
     *
     * @param array<int, string> $passedClasses
     */
    public function __construct(
        int $count,
        FailureCollection $errors,
        FailureCollection $failures,
        FailureCollection $notImplemented,
        FailureCollection $risky,
        FailureCollection $skipped,
        FailureCollection $warnings,
        array $passed,
        array $passedClasses
    ) {
        $this->count          = $count;
        $this->errors         = $errors;
        $this->failures       = $failures;
        $this->notImplemented = $notImplemented;
        $this->risky          = $risky;
        $this->skipped        = $skipped;
        $this->warnings       = $warnings;
        $this->passed         = $passed;
        $this->passedClasses  = $passedClasses;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function errors(): FailureCollection
    {
        return $this->errors;
    }

    public function failures(): FailureCollection
    {
        return $this->failures;
    }

    public function notImplemented(): FailureCollection
    {
        return $this->notImplemented;
    }

    public function risky(): FailureCollection
    {
        return $this->risky;
    }

    public function skipped(): FailureCollection
    {
        return $this->skipped;
    }

    public function warnings(): FailureCollection
    {
        return $this->warnings;
    }

    public function passed(): array
    {
        return $this->passed;
    }

    /**
     * @psalm-return list<class-string>
     *
     * @return array<int, string>
     */
    public function passedClasses(): array
    {
        return $this->passedClasses;
    }
}
