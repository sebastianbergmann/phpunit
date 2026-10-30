<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\RegularExpression;

use function assert;
use function preg_match;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use SebastianBergmann\ChoiceSequence\Distribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;
use SebastianBergmann\ChoiceSequence\WeightedRangesDistribution;

/**
 * One character of a set, for a character class, for an escape sequence such
 * as \d, or for the dot. ASCII characters are generated more often than
 * others, and the simplest character of the set comes first.
 *
 * The set of a character class can contain characters that the class does
 * not match, for instance because of the i modifier, or because the u
 * modifier makes \w match letters other than ASCII ones. Each character is
 * therefore checked with a regular expression that consists of the class: a
 * character that the class does not match is replaced with the next one of
 * the set that it does match.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CharacterSetNode implements Node
{
    private const int MAXIMUM_NUMBER_OF_CHECKED_CHARACTERS = 64;
    private CharacterSet $set;

    /**
     * @var non-negative-int
     */
    private int $largestIndex;

    /**
     * @var ?non-empty-string
     */
    private ?string $check;
    private Distribution $distribution;

    /**
     * @param ?non-empty-string $check a regular expression that matches exactly the characters that are to be generated, null when all characters of the set are
     */
    public function __construct(CharacterSet $set, ?string $check = null)
    {
        $largestIndex = $set->size() - 1;

        assert($largestIndex >= 0);

        $this->set          = $set;
        $this->largestIndex = $largestIndex;
        $this->check        = $check;

        $numberOfAsciiMembers = $set->numberOfAsciiMembers();

        if ($numberOfAsciiMembers === 0 || $numberOfAsciiMembers === $set->size()) {
            $this->distribution = new UniformDistribution;

            return;
        }

        $this->distribution = new WeightedRangesDistribution(
            [
                ['weight' => 0.8, 'minimum' => 0, 'maximum' => $numberOfAsciiMembers - 1],
                ['weight' => 0.2, 'minimum' => 0, 'maximum' => $largestIndex],
            ],
        );
    }

    public function generate(ChoiceSource $source): string
    {
        $size  = $this->largestIndex + 1;
        $index = $source->draw($this->largestIndex, $this->distribution);

        for ($offset = 0; $offset < self::MAXIMUM_NUMBER_OF_CHECKED_CHARACTERS && $offset < $size; $offset++) {
            $character = $this->set->member(($index + $offset) % $size);

            if ($this->check === null || preg_match($this->check, $character) === 1) {
                return $character;
            }
        }

        throw ExampleRejectedException::noCharacterOfClass(self::MAXIMUM_NUMBER_OF_CHECKED_CHARACTERS);
    }
}
