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
use function implode;
use function min;
use function strlen;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\BoundaryDistribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates domain names of letters, digits, and hyphens (RFC 1123) in
 * lowercase, without a trailing dot.
 *
 * A domain name has at least two labels, each with 1 to 63 characters,
 * neither starting nor ending with a hyphen, and without hyphens as their
 * third and fourth character, which are reserved for internationalized
 * domain names (RFC 5891). The top-level domain has at least two letters
 * and no other characters.
 *
 * The labels other than the top-level domain are drawn first, from left to
 * right, as long as they leave room for the shortest top-level domain, and
 * the top-level domain is drawn last, from the room they leave. A
 * counterexample that depends on the length of a domain name rather than on
 * its labels is therefore shrunk to a label of one letter and a top-level
 * domain of the other letters, a.aaaaaaaaa for 11 characters, for instance,
 * by moving the length of the other labels to the top-level domain. The
 * lengths of the labels are biased towards 1 and the longest length that
 * fits, so that labels of 63 characters, domain names of the maximum length,
 * and domain names of many labels are generated often. Some labels consist
 * of digits only.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DomainNameGenerator extends AbstractGenerator
{
    public const int SHORTEST_LENGTH            = 4;
    public const int LONGEST_LENGTH             = 253;
    private const int LONGEST_LABEL             = 63;
    private const int SHORTEST_TOP_LEVEL_DOMAIN = 2;
    private const string LETTERS                = 'abcdefghijklmnopqrstuvwxyz';
    private const string DIGITS                 = '0123456789';

    /**
     * The probability that another label is added after the labels that have
     * been drawn, while it leaves room for the top-level domain, and the
     * probability that a label other than the top-level domain consists of
     * digits only.
     */
    private const float PROBABILITY_OF_ANOTHER_LABEL = 0.75;

    private const float PROBABILITY_OF_NUMERIC_LABEL = 0.1;

    /**
     * @var int<4, 253>
     */
    private int $maximumLength;
    private BernoulliDistribution $anotherLabel;
    private BernoulliDistribution $numericLabel;
    private BoundaryDistribution $length;
    private UniformDistribution $character;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @var non-empty-string
     */
    private string $labelOfLabels;

    /**
     * @var non-empty-string
     */
    private string $labelOfCharacters;

    /**
     * @param int<4, 253> $maximumLength
     */
    public function __construct(int $maximumLength)
    {
        $this->label             = self::labelOf('domainNames', (string) $maximumLength);
        $this->labelOfLabels     = self::labelOf('label', $this->label);
        $this->labelOfCharacters = self::labelOf('character', $this->label);

        $this->maximumLength = $maximumLength;
        $this->anotherLabel  = new BernoulliDistribution(self::PROBABILITY_OF_ANOTHER_LABEL);
        $this->numericLabel  = new BernoulliDistribution(self::PROBABILITY_OF_NUMERIC_LABEL);
        $this->length        = new BoundaryDistribution;
        $this->character     = new UniformDistribution;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        // each label other than the top-level domain takes one character
        // more than its length, for the dot that follows it, and leaves room
        // for the shortest top-level domain
        $remaining = $this->maximumLength;
        $labels    = [];

        while ($remaining >= 2 + self::SHORTEST_TOP_LEVEL_DOMAIN) {
            $source->startSpan($this->labelOfLabels);

            if ($labels !== [] && $source->draw(1, $this->anotherLabel) === 0) {
                $source->endSpan();

                break;
            }

            $label = $this->otherLabel($source, min(self::LONGEST_LABEL, $remaining - 1 - self::SHORTEST_TOP_LEVEL_DOMAIN));

            $source->endSpan();

            $labels[] = $label;
            $remaining -= strlen($label) + 1;
        }

        // the labels leave room for the shortest top-level domain
        assert($remaining >= self::SHORTEST_TOP_LEVEL_DOMAIN);

        return implode('.', $labels) . '.' . $this->topLevelDomain($source, $remaining);
    }

    /**
     * @param int<2, max> $room the number of characters that the other labels leave for the top-level domain
     *
     * @return non-empty-string
     */
    private function topLevelDomain(ChoiceSource $source, int $room): string
    {
        $length = self::SHORTEST_TOP_LEVEL_DOMAIN + $source->draw(min(self::LONGEST_LABEL, $room) - self::SHORTEST_TOP_LEVEL_DOMAIN, $this->length);

        $topLevelDomain = $this->character($source, self::LETTERS);

        for ($index = 1; $index < $length; $index++) {
            $topLevelDomain .= $this->character($source, self::LETTERS);
        }

        return $topLevelDomain;
    }

    /**
     * @param positive-int $maximumLength
     *
     * @return non-empty-string
     */
    private function otherLabel(ChoiceSource $source, int $maximumLength): string
    {
        $alphabet = self::LETTERS . self::DIGITS;
        $numeric  = $source->draw(1, $this->numericLabel) === 1;

        if ($numeric) {
            $alphabet = self::DIGITS;
        }

        $length = 1 + $source->draw($maximumLength - 1, $this->length);
        $label  = $this->character($source, $alphabet);

        for ($index = 1; $index < $length; $index++) {
            // a hyphen is neither the last character nor, after a hyphen,
            // the fourth one
            if (!$numeric && $index < $length - 1 && !($index === 3 && $label[2] === '-')) {
                $label .= $this->character($source, $alphabet . '-');

                continue;
            }

            $label .= $this->character($source, $alphabet);
        }

        return $label;
    }

    /**
     * Each character is a span, so that a label is shortened by lowering its
     * length while its last characters are deleted.
     *
     * @param non-empty-string $alphabet
     *
     * @return non-empty-string
     */
    private function character(ChoiceSource $source, string $alphabet): string
    {
        $source->startSpan($this->labelOfCharacters);

        $character = $alphabet[$source->draw(strlen($alphabet) - 1, $this->character)];

        $source->endSpan();

        return $character;
    }
}
