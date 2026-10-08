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
 * An array shape such as array{name: string, age?: int} or list{int, string}.
 * A shape that ends with ... is unsealed: it may have keys that are not
 * listed.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ArrayShapeType extends Type
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var list<ArrayShapeItem>
     */
    private array $items;
    private bool $sealed;

    /**
     * @param non-empty-string     $name  the keyword in front of the shape, for instance array or list
     * @param list<ArrayShapeItem> $items
     */
    public function __construct(string $name, array $items, bool $sealed)
    {
        $this->name   = $name;
        $this->items  = $items;
        $this->sealed = $sealed;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<ArrayShapeItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function isSealed(): bool
    {
        return $this->sealed;
    }

    public function asString(): string
    {
        $items = array_map(static fn (ArrayShapeItem $item): string => $item->asString(), $this->items);

        if (!$this->sealed) {
            $items[] = '...';
        }

        return $this->name . '{' . implode(', ', $items) . '}';
    }
}
