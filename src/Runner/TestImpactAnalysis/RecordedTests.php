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
 * The tests that are recorded as having executed one source file.
 *
 * The tests that executed the file as it is now and the tests that executed an
 * earlier version of it are kept apart because they say different things: what
 * was recorded for the former describes the code that is there, while what was
 * recorded for the latter describes code that has changed since.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RecordedTests
{
    /**
     * @var list<non-empty-string>
     */
    private array $thatDependOnTheFileAsItIsNow;

    /**
     * @var list<non-empty-string>
     */
    private array $thatDependOnAnEarlierVersionOfTheFile;
    private Provenance $provenance;

    /**
     * @param list<non-empty-string> $thatDependOnTheFileAsItIsNow
     * @param list<non-empty-string> $thatDependOnAnEarlierVersionOfTheFile
     */
    public static function from(array $thatDependOnTheFileAsItIsNow, array $thatDependOnAnEarlierVersionOfTheFile, Provenance $provenance): self
    {
        return new self($thatDependOnTheFileAsItIsNow, $thatDependOnAnEarlierVersionOfTheFile, $provenance);
    }

    /**
     * @param list<non-empty-string> $thatDependOnTheFileAsItIsNow
     * @param list<non-empty-string> $thatDependOnAnEarlierVersionOfTheFile
     */
    private function __construct(array $thatDependOnTheFileAsItIsNow, array $thatDependOnAnEarlierVersionOfTheFile, Provenance $provenance)
    {
        $this->thatDependOnTheFileAsItIsNow          = $thatDependOnTheFileAsItIsNow;
        $this->thatDependOnAnEarlierVersionOfTheFile = $thatDependOnAnEarlierVersionOfTheFile;
        $this->provenance                            = $provenance;
    }

    /**
     * Whether these tests were derived from the code coverage targets the
     * tests declare instead of from what the tests were observed to execute.
     */
    public function wereDerivedFromCoverageTargets(): bool
    {
        return $this->provenance === Provenance::CoverageTargets;
    }

    /**
     * @return list<non-empty-string>
     */
    public function thatDependOnTheFileAsItIsNow(): array
    {
        return $this->thatDependOnTheFileAsItIsNow;
    }

    /**
     * @return list<non-empty-string>
     */
    public function thatDependOnAnEarlierVersionOfTheFile(): array
    {
        return $this->thatDependOnAnEarlierVersionOfTheFile;
    }

    public function isEmpty(): bool
    {
        return $this->thatDependOnTheFileAsItIsNow === [] && $this->thatDependOnAnEarlierVersionOfTheFile === [];
    }
}
