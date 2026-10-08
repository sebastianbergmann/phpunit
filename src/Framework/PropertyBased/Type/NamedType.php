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
 * A keyword such as int or non-empty-string, or a class name, optionally with
 * generic arguments, for instance list<int> or int<0, max>. T[] is parsed as
 * array<T>.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class NamedType extends Type
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var list<Type>
     */
    private array $arguments;

    /**
     * @param non-empty-string $name      the name as it is written, a class name possibly with a leading backslash
     * @param list<Type>       $arguments
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<Type>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function asString(): string
    {
        if ($this->arguments === []) {
            return $this->name;
        }

        return $this->name . '<' . implode(', ', array_map(static fn (Type $type): string => $type->asString(), $this->arguments)) . '>';
    }
}
