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
    private array $thatExecutedTheFileAsItIsNow;

    /**
     * @var list<non-empty-string>
     */
    private array $thatExecutedAnEarlierVersionOfTheFile;

    /**
     * @param list<non-empty-string> $thatExecutedTheFileAsItIsNow
     * @param list<non-empty-string> $thatExecutedAnEarlierVersionOfTheFile
     */
    public static function from(array $thatExecutedTheFileAsItIsNow, array $thatExecutedAnEarlierVersionOfTheFile): self
    {
        return new self($thatExecutedTheFileAsItIsNow, $thatExecutedAnEarlierVersionOfTheFile);
    }

    /**
     * @param list<non-empty-string> $thatExecutedTheFileAsItIsNow
     * @param list<non-empty-string> $thatExecutedAnEarlierVersionOfTheFile
     */
    private function __construct(array $thatExecutedTheFileAsItIsNow, array $thatExecutedAnEarlierVersionOfTheFile)
    {
        $this->thatExecutedTheFileAsItIsNow          = $thatExecutedTheFileAsItIsNow;
        $this->thatExecutedAnEarlierVersionOfTheFile = $thatExecutedAnEarlierVersionOfTheFile;
    }

    /**
     * @return list<non-empty-string>
     */
    public function thatExecutedTheFileAsItIsNow(): array
    {
        return $this->thatExecutedTheFileAsItIsNow;
    }

    /**
     * @return list<non-empty-string>
     */
    public function thatExecutedAnEarlierVersionOfTheFile(): array
    {
        return $this->thatExecutedAnEarlierVersionOfTheFile;
    }

    public function isEmpty(): bool
    {
        return $this->thatExecutedTheFileAsItIsNow === [] && $this->thatExecutedAnEarlierVersionOfTheFile === [];
    }
}
