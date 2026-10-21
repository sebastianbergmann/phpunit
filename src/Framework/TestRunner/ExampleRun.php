<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

/**
 * What a property is decided on, for one of its examples, besides the outcome
 * of the example: whether the property is risky for the assertions that its
 * examples performed or for their contribution to code coverage.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExampleRun
{
    /**
     * @var non-negative-int
     */
    private int $numberOfAssertions;
    private bool $doesNotPerformAssertions;
    private ?bool $contributedToCodeCoverage;

    /**
     * @param non-negative-int $numberOfAssertions
     */
    public function __construct(int $numberOfAssertions, bool $doesNotPerformAssertions, ?bool $contributedToCodeCoverage)
    {
        $this->numberOfAssertions        = $numberOfAssertions;
        $this->doesNotPerformAssertions  = $doesNotPerformAssertions;
        $this->contributedToCodeCoverage = $contributedToCodeCoverage;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    public function doesNotPerformAssertions(): bool
    {
        return $this->doesNotPerformAssertions;
    }

    /**
     * Whether the example executed code for which code coverage is collected,
     * null when this was not checked because contribution to code coverage is
     * not required or no code coverage was collected for the example.
     */
    public function contributedToCodeCoverage(): ?bool
    {
        return $this->contributedToCodeCoverage;
    }
}
