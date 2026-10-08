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

use function array_map;
use function implode;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class UnionType extends Type
{
    /**
     * @var non-empty-list<Type>
     */
    private array $types;

    /**
     * @param non-empty-list<Type> $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return non-empty-list<Type>
     */
    public function types(): array
    {
        return $this->types;
    }

    public function asString(): string
    {
        return implode('|', array_map(static fn (Type $type): string => $type->asString(), $this->types));
    }
}
