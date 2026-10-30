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
use function sprintf;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates UUIDs (RFC 9562) in their canonical form.
 *
 * A UUID is drawn as the integers that its version lays out in its 128 bits,
 * next to the version and the variant bits, which are fixed: versions 1 and 6
 * draw a 60-bit timestamp, a 14-bit clock sequence, and a 48-bit node, which
 * they lay out differently; all other versions draw 48, 12, and 62 bits,
 * of which the first are the Unix timestamp in milliseconds for version 7.
 * The integers are biased towards their boundaries, so that fields of only
 * zeros or ones are generated often.
 *
 * @template-extends AbstractGenerator<non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class UuidGenerator extends AbstractGenerator
{
    private const string NIL = '00000000-0000-0000-0000-000000000000';
    private const string MAX = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    /**
     * The probability that a UUID of one of the versions is generated rather
     * than the nil or the max UUID, when these are generated.
     */
    private const float PROBABILITY_OF_VERSION = 0.95;

    /**
     * @var non-empty-list<int<1, 8>>
     */
    private array $versions;
    private bool $nilAndMax;
    private BernoulliDistribution $version;
    private UniformDistribution $uniform;
    private IntegerGenerator $timestamps;
    private IntegerGenerator $clockSequences;
    private IntegerGenerator $nodes;
    private IntegerGenerator $first48Bits;
    private IntegerGenerator $next12Bits;
    private IntegerGenerator $last62Bits;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-empty-list<int<1, 8>> $versions  the versions to choose from, the first one simplest
     * @param bool                      $nilAndMax whether the nil and the max UUID are generated as well, the nil UUID simplest
     */
    public function __construct(array $versions, bool $nilAndMax)
    {
        $this->label = self::labelOf('uuids', implode(',', $versions), (string) (int) $nilAndMax);

        $this->versions       = $versions;
        $this->nilAndMax      = $nilAndMax;
        $this->version        = new BernoulliDistribution(self::PROBABILITY_OF_VERSION);
        $this->uniform        = new UniformDistribution;
        $this->timestamps     = new IntegerGenerator(0, 0xFFFFFFFFFFFFFFF);
        $this->clockSequences = new IntegerGenerator(0, 0x3FFF);
        $this->nodes          = new IntegerGenerator(0, 0xFFFFFFFFFFFF);
        $this->first48Bits    = new IntegerGenerator(0, 0xFFFFFFFFFFFF);
        $this->next12Bits     = new IntegerGenerator(0, 0xFFF);
        $this->last62Bits     = new IntegerGenerator(0, 0x3FFFFFFFFFFFFFFF);
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        if ($this->nilAndMax && $source->draw(1, $this->version) === 0) {
            return $source->choose([self::NIL, self::MAX], $this->uniform);
        }

        $version = $source->choose($this->versions, $this->uniform);

        if ($version === 1 || $version === 6) {
            $timestamp     = $this->timestamps->generate($source);
            $clockSequence = $this->clockSequences->generate($source);
            $node          = $this->nodes->generate($source);

            if ($version === 1) {
                return self::format(
                    $timestamp & 0xFFFFFFFF,
                    ($timestamp >> 32) & 0xFFFF,
                    ($version << 12) | ($timestamp >> 48),
                    0x8000 | $clockSequence,
                    $node,
                );
            }

            return self::format(
                $timestamp >> 28,
                ($timestamp >> 12) & 0xFFFF,
                ($version << 12) | ($timestamp & 0xFFF),
                0x8000 | $clockSequence,
                $node,
            );
        }

        $first  = $this->first48Bits->generate($source);
        $second = $this->next12Bits->generate($source);
        $third  = $this->last62Bits->generate($source);

        return self::format(
            $first >> 16,
            $first & 0xFFFF,
            ($version << 12) | $second,
            0x8000 | ($third >> 48),
            $third & 0xFFFFFFFFFFFF,
        );
    }

    /**
     * @return non-empty-string
     */
    private static function format(int $timeLow, int $timeMid, int $timeHighAndVersion, int $variantAndClockSequence, int $node): string
    {
        return sprintf('%08x-%04x-%04x-%04x-%012x', $timeLow, $timeMid, $timeHighAndVersion, $variantAndClockSequence, $node);
    }
}
