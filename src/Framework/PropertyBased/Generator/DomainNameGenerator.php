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

use function implode;
use function min;
use function strlen;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\BoundaryDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

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
 * The top-level domain is drawn first, then the other labels from left to
 * right, as long as they fit into the maximum length. The lengths of the
 * labels are biased towards 1 and the longest length that fits, so that
 * labels of 63 characters, domain names of the maximum length, and domain
 * names of many labels are generated often. Some labels consist of digits
 * only.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DomainNameGenerator extends AbstractGenerator
{
    public const int SHORTEST_LENGTH = 4;
    public const int LONGEST_LENGTH  = 253;
    private const int LONGEST_LABEL  = 63;
    private const string LETTERS     = 'abcdefghijklmnopqrstuvwxyz';
    private const string DIGITS      = '0123456789';

    /**
     * The probability that another label is added after the labels that have
     * been drawn, while one fits into the maximum length, and the
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
     * @param int<4, 253> $maximumLength
     */
    public function __construct(int $maximumLength)
    {
        $this->label         = self::labelOf('domainNames', (string) $maximumLength);
        $this->labelOfLabels = self::labelOf('label', $this->label);

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
        $topLevelDomain = $this->topLevelDomain($source);

        // each label other than the top-level domain takes one character
        // more than its length, for the dot that follows it
        $remaining = $this->maximumLength - strlen($topLevelDomain);
        $labels    = [];

        while ($remaining >= 2) {
            $source->startSpan($this->labelOfLabels);

            if ($labels !== [] && $source->draw(1, $this->anotherLabel) === 0) {
                $source->endSpan();

                break;
            }

            $label = $this->otherLabel($source, min(self::LONGEST_LABEL, $remaining - 1));

            $source->endSpan();

            $labels[] = $label;
            $remaining -= strlen($label) + 1;
        }

        return implode('.', $labels) . '.' . $topLevelDomain;
    }

    /**
     * @return non-empty-string
     */
    private function topLevelDomain(ChoiceSource $source): string
    {
        // there is room for a label of one character in front of it
        $length = 2 + $source->draw(min(self::LONGEST_LABEL, $this->maximumLength - 2) - 2, $this->length);

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
     * @param non-empty-string $alphabet
     *
     * @return non-empty-string
     */
    private function character(ChoiceSource $source, string $alphabet): string
    {
        return $alphabet[$source->draw(strlen($alphabet) - 1, $this->character)];
    }
}
