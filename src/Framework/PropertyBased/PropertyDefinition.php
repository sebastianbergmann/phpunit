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

use Closure;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;

/**
 * A property after its metadata has been validated: the generator for each
 * of its parameters that is derived from its type or where it comes from
 * otherwise, and the methods of its test case class that are registered with
 * #[GeneratorFor].
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyDefinition
{
    /**
     * @var class-string<TestCase>
     */
    private string $className;

    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|non-empty-string>
     */
    private array $parameters;

    /**
     * @var array<class-string, non-empty-string>
     */
    private array $registeredGenerators;

    /**
     * @param class-string<TestCase>                                                                              $className
     * @param non-empty-string                                                                                    $methodName
     * @param array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|non-empty-string> $parameters           by parameter name, in the order of the parameters, the generators that are derived from types and the closures and names of the methods that are specified with #[ForAll]
     * @param array<class-string, non-empty-string>                                                               $registeredGenerators the names of the methods that are registered with #[GeneratorFor] by class name
     */
    public function __construct(string $className, string $methodName, array $parameters, array $registeredGenerators)
    {
        $this->className            = $className;
        $this->methodName           = $methodName;
        $this->parameters           = $parameters;
        $this->registeredGenerators = $registeredGenerators;
    }

    /**
     * @return class-string<TestCase>
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
     * @return array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|non-empty-string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<class-string, non-empty-string>
     */
    public function registeredGenerators(): array
    {
        return $this->registeredGenerators;
    }
}
