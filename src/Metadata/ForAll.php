<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use Closure;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * The generator that is specified for a parameter of a property with the
 * #[ForAll] or #[ForAllExternal] attribute of the parameter.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ForAll extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $parameterName;

    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var Closure(): ValueGenerator<mixed>|non-empty-string
     */
    private Closure|string $generator;

    /**
     * @param non-empty-string                                  $parameterName
     * @param class-string                                      $className
     * @param Closure(): ValueGenerator<mixed>|non-empty-string $generator
     */
    protected function __construct(Level $level, string $parameterName, string $className, Closure|string $generator)
    {
        parent::__construct($level);

        $this->parameterName = $parameterName;
        $this->className     = $className;
        $this->generator     = $generator;
    }

    public function isForAll(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function parameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * The class on which the method that returns the generator is called:
     * the test case class, unless the generator is specified with
     * #[ForAllExternal].
     *
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * The name of a public static method of the class that is returned by
     * className() that returns the generator, or a static closure that
     * returns it.
     *
     * @return Closure(): ValueGenerator<mixed>|non-empty-string
     */
    public function generator(): Closure|string
    {
        return $this->generator;
    }
}
