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

/**
 * Generates the same value without drawing a choice.
 *
 * @template T
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ConstantGenerator extends AbstractGenerator
{
    /**
     * @var T
     */
    private mixed $value;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param T $value
     */
    public function __construct(mixed $value)
    {
        $this->label = self::labelOf('constant');

        $this->value = $value;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        return $this->value;
    }
}
