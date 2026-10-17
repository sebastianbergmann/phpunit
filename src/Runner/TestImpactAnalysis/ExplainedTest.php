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

/**
 * One test that is run, and why it is run.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExplainedTest
{
    /**
     * @var non-empty-string
     */
    private string $test;
    private SelectionReason $reason;

    /**
     * @var ?non-empty-string
     */
    private ?string $file;

    /**
     * @param non-empty-string  $test
     * @param ?non-empty-string $file the file that changed, where that is why the test is run
     */
    public static function from(string $test, SelectionReason $reason, ?string $file = null): self
    {
        return new self($test, $reason, $file);
    }

    /**
     * @param non-empty-string  $test
     * @param ?non-empty-string $file
     */
    private function __construct(string $test, SelectionReason $reason, ?string $file)
    {
        $this->test   = $test;
        $this->reason = $reason;
        $this->file   = $file;
    }

    /**
     * @return non-empty-string
     */
    public function test(): string
    {
        return $this->test;
    }

    public function reason(): SelectionReason
    {
        return $this->reason;
    }

    /**
     * @phpstan-assert-if-true !null $this->file
     */
    public function hasFile(): bool
    {
        return $this->file !== null;
    }

    /**
     * @return ?non-empty-string
     */
    public function file(): ?string
    {
        return $this->file;
    }
}
