<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

/**
 * A type written as ?T. T|null is parsed as UnionType.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class NullableType extends Type
{
    private Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function asString(): string
    {
        if ($this->type instanceof UnionType) {
            return '?(' . $this->type->asString() . ')';
        }

        return '?' . $this->type->asString();
    }
}
