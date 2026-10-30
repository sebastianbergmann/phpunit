<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function assert;
use function count;
use function mb_chr;
use function var_export;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\Distribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;
use SebastianBergmann\ChoiceSequence\WeightedRangesDistribution;

/**
 * Generates valid UTF-8 strings. Their length is the number of characters
 * (code points), not the number of bytes.
 *
 * Each character is one choice: the index of the character in the alphabet
 * or, without an alphabet, the index of the code point in the order that
 * CodePoints defines.
 *
 * @template-extends AbstractGenerator<string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class StringGenerator extends AbstractGenerator
{
    /**
     * @var ?non-empty-list<non-empty-string>
     */
    private ?array $alphabet;
    private ?CodePointRanges $codePointRanges;
    private Repetition $repetition;
    private Distribution $character;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param ?non-empty-list<non-empty-string> $alphabet        the characters to choose from, or null for all Unicode scalar values or the code point ranges
     * @param non-negative-int                  $minimumLength
     * @param ?non-negative-int                 $maximumLength
     * @param ?CodePointRanges                  $codePointRanges the code points of the characters to choose from when there is no alphabet, or null for all Unicode scalar values
     */
    public function __construct(?array $alphabet, int $minimumLength, ?int $maximumLength, ?CodePointRanges $codePointRanges = null)
    {
        assert($alphabet === null || $codePointRanges === null);

        $alphabetSize = 'unicode';

        if ($alphabet !== null) {
            $alphabetSize = (string) count($alphabet);
        }

        if ($codePointRanges !== null) {
            $alphabetSize = $codePointRanges->asString();
        }

        $this->label = self::labelOf('strings', $alphabetSize, (string) $minimumLength, var_export($maximumLength, true));

        $this->alphabet        = $alphabet;
        $this->codePointRanges = $codePointRanges;
        $this->repetition      = new Repetition(self::labelOf('element', $this->label), $minimumLength, $maximumLength);

        if ($alphabet !== null) {
            $this->character = new UniformDistribution;

            return;
        }

        // the characters of each range are chosen equally often, so that a
        // small range is not drowned out by a large one
        if ($codePointRanges !== null) {
            $ranges = [];

            foreach ($codePointRanges->indexRanges() as $indexRange) {
                $ranges[] = ['weight' => 1.0, 'minimum' => $indexRange['minimum'], 'maximum' => $indexRange['maximum']];
            }

            $this->character = new WeightedRangesDistribution($ranges);

            return;
        }

        $this->character = new WeightedRangesDistribution(
            [
                // printable ASCII characters
                ['weight' => 0.5, 'minimum' => 0, 'maximum' => 94],
                // ASCII control characters
                ['weight' => 0.03, 'minimum' => 95, 'maximum' => 127],
                // characters that are encoded with two bytes
                ['weight' => 0.12, 'minimum' => CodePoints::index(0x80), 'maximum' => CodePoints::index(0x7FF)],
                // combining diacritical marks
                ['weight' => 0.1, 'minimum' => CodePoints::index(0x300), 'maximum' => CodePoints::index(0x36F)],
                // characters that are encoded with three bytes
                ['weight' => 0.1, 'minimum' => CodePoints::index(0x800), 'maximum' => CodePoints::index(0xFFFF)],
                // emoji, which are encoded with four bytes
                ['weight' => 0.1, 'minimum' => CodePoints::index(0x1F300), 'maximum' => CodePoints::index(0x1FAFF)],
                // any character
                ['weight' => 0.05, 'minimum' => 0, 'maximum' => CodePoints::LARGEST_INDEX],
            ],
        );
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        $string = '';

        $this->repetition->repeat(
            $source,
            function () use ($source, &$string): bool
            {
                $string .= $this->character($source);

                return true;
            },
        );

        return $string;
    }

    /**
     * @return non-empty-string
     */
    private function character(ChoiceSource $source): string
    {
        if ($this->alphabet !== null) {
            return $source->choose($this->alphabet, $this->character);
        }

        if ($this->codePointRanges !== null) {
            $character = mb_chr($this->codePointRanges->codePoint($source->draw($this->codePointRanges->numberOfCodePoints() - 1, $this->character)), 'UTF-8');

            assert($character !== false && $character !== '');

            return $character;
        }

        $index = $source->draw(CodePoints::LARGEST_INDEX, $this->character);

        assert($index <= CodePoints::LARGEST_INDEX);

        $character = mb_chr(CodePoints::codePoint($index), 'UTF-8');

        assert($character !== false && $character !== '');

        return $character;
    }
}
