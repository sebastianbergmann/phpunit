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

use function chr;
use function var_export;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\WeightedRangesDistribution;

/**
 * Generates arbitrary binary strings. Each byte is one choice, its value.
 *
 * @template-extends AbstractGenerator<string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BytesGenerator extends AbstractGenerator
{
    private Repetition $repetition;
    private WeightedRangesDistribution $byte;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-negative-int  $minimumLength
     * @param ?non-negative-int $maximumLength
     */
    public function __construct(int $minimumLength, ?int $maximumLength)
    {
        $this->label = self::labelOf('bytes', (string) $minimumLength, var_export($maximumLength, true));

        $this->repetition = new Repetition(self::labelOf('element', $this->label), $minimumLength, $maximumLength);

        $this->byte = new WeightedRangesDistribution(
            [
                ['weight' => 0.5, 'minimum' => 0x20, 'maximum' => 0x7E],
                ['weight' => 0.1, 'minimum' => 0x00, 'maximum' => 0x00],
                ['weight' => 0.1, 'minimum' => 0xFF, 'maximum' => 0xFF],
                ['weight' => 0.3, 'minimum' => 0x00, 'maximum' => 0xFF],
            ],
        );
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        $bytes = '';

        $this->repetition->repeat(
            $source,
            function () use ($source, &$bytes): bool
            {
                $bytes .= chr($source->draw(0xFF, $this->byte));

                return true;
            },
        );

        return $bytes;
    }
}
