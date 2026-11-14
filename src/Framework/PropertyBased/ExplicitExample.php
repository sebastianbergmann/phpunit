<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function is_int;
use function sprintf;

/**
 * An example of a property whose arguments are specified with #[TestWith] or
 * #[TestWithJson] instead of being generated.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExplicitExample
{
    /**
     * @var int|non-empty-string
     */
    private int|string $name;

    /**
     * @var array<non-empty-string, mixed>
     */
    private array $arguments;

    /**
     * @param int|non-empty-string $name
     *
     * @return non-empty-string
     */
    public static function labelFor(int|string $name): string
    {
        if (is_int($name)) {
            return '#' . $name;
        }

        return sprintf('"%s"', $name);
    }

    /**
     * @param int|non-empty-string           $name      the name that is specified for the example, or its number among the examples for which no name is specified
     * @param array<non-empty-string, mixed> $arguments by parameter name, in the order of the parameters
     */
    public function __construct(int|string $name, array $arguments)
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * @return int|non-empty-string
     */
    public function name(): int|string
    {
        return $this->name;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * The name of the example as it is reported: #0 for a number and "name"
     * for a name, like the name of a data set.
     *
     * @return non-empty-string
     */
    public function label(): string
    {
        return self::labelFor($this->name);
    }
}
