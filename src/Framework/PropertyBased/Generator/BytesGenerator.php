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

use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\Bytes;
use SebastianBergmann\ChoiceSequence\SpanKind;

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
    private Bytes $bytes;

    /**
     * @param non-negative-int  $minimumLength
     * @param ?non-negative-int $maximumLength
     */
    public function __construct(int $minimumLength, ?int $maximumLength)
    {
        $this->bytes = new Bytes($minimumLength, $maximumLength);
    }

    public function label(): string
    {
        return $this->bytes->label();
    }

    /**
     * The bytes that the minimum length of the string requires consist of
     * their value only, as SpanKind::Bytes permits.
     */
    protected function spanKind(): SpanKind
    {
        return SpanKind::Bytes;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        return $source->bytes($this->bytes);
    }
}
