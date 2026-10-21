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

/**
 * A public static method that returns a generator: a method that is
 * registered with #[GeneratorFor], or one that is specified with #[ForAll] or
 * #[ForAllExternal].
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GeneratorMethod
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @param class-string     $className  the class on which the method is called
     * @param non-empty-string $methodName
     */
    public function __construct(string $className, string $methodName)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return $this->className . '::' . $this->methodName . '()';
    }
}
